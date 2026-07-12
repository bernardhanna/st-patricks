<?php

/**
 * Shared link helpers — external URLs open in a new tab.
 */

if (! function_exists('matrix_is_external_url')) {
    function matrix_is_external_url(string $url): bool
    {
        $url = trim($url);

        if ($url === '' || $url === '#') {
            return false;
        }

        if (str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
            return false;
        }

        if (! preg_match('#^https?://#i', $url)) {
            return false;
        }

        $link_host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($link_host === '') {
            return false;
        }

        $site_host = strtolower((string) parse_url(home_url('/'), PHP_URL_HOST));
        $internal_hosts = array_values(array_filter(array_unique([
            $site_host,
            'www.stpatricks.ie',
            'stpatricks.ie',
            'localhost',
        ])));

        return ! in_array($link_host, $internal_hosts, true);
    }
}

if (! function_exists('matrix_normalize_link_target')) {
    function matrix_normalize_link_target(string $url, string $target = ''): string
    {
        $target = trim($target);

        if ($target === '_blank' || matrix_is_external_url($url)) {
            return '_blank';
        }

        return '_self';
    }
}

if (! function_exists('matrix_external_link_rel')) {
    function matrix_external_link_rel(string $target = ''): string
    {
        return $target === '_blank' ? 'noopener noreferrer' : '';
    }
}

if (! function_exists('matrix_iframe_title_for_src')) {
    /**
     * Derive an accessible title for an embedded iframe based on its source.
     */
    function matrix_iframe_title_for_src(string $src): string
    {
        $host = strtolower((string) parse_url($src, PHP_URL_HOST));

        if ($host === '') {
            return 'Embedded content';
        }

        $map = [
            'youtube' => 'YouTube video',
            'youtu.be' => 'YouTube video',
            'vimeo' => 'Vimeo video',
            'facebook' => 'Facebook post',
            'instagram' => 'Instagram post',
            'twitter' => 'X (Twitter) post',
            'x.com' => 'X (Twitter) post',
            'spotify' => 'Spotify player',
            'soundcloud' => 'SoundCloud player',
            'mixcloud' => 'Mixcloud player',
            'audioboom' => 'Audioboom player',
            'anchor.fm' => 'Podcast player',
            'google.com/maps' => 'Google Map',
            'maps.google' => 'Google Map',
            'podbean' => 'Podcast player',
            'buzzsprout' => 'Podcast player',
        ];

        $needle = $host . (string) parse_url($src, PHP_URL_PATH);

        foreach ($map as $key => $label) {
            if (str_contains($host, $key) || str_contains($needle, $key)) {
                return $label;
            }
        }

        $host = preg_replace('/^www\./', '', $host);

        return 'Embedded content from ' . $host;
    }
}

if (! function_exists('matrix_process_external_links_in_html')) {
    function matrix_process_external_links_in_html(string $html): string
    {
        if (
            $html === ''
            || (! str_contains($html, '<a')
                && ! str_contains($html, '<iframe')
                && ! str_contains($html, '<ul')
                && ! str_contains($html, '<ol'))
        ) {
            return $html;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div>' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOWARNING | LIBXML_NOERROR
        );
        libxml_clear_errors();

        if (! $loaded) {
            return $html;
        }

        $wrapper = $dom->getElementsByTagName('div')->item(0);

        if (! $wrapper instanceof DOMElement) {
            return $html;
        }

        foreach (iterator_to_array($dom->getElementsByTagName('iframe')) as $iframe) {
            if (! $iframe instanceof DOMElement) {
                continue;
            }

            $existing_title = trim($iframe->getAttribute('title'));

            if ($existing_title !== '') {
                continue;
            }

            $iframe->setAttribute('title', matrix_iframe_title_for_src(trim($iframe->getAttribute('src'))));
        }

        foreach ($dom->getElementsByTagName('a') as $anchor) {
            if (! $anchor instanceof DOMElement) {
                continue;
            }

            $href = trim($anchor->getAttribute('href'));

            if ($href === '' || ! matrix_is_external_url($href)) {
                continue;
            }

            $anchor->setAttribute('target', '_blank');

            $rel = trim($anchor->getAttribute('rel'));
            $rel_parts = $rel !== '' ? preg_split('/\s+/', $rel) ?: [] : [];
            $rel_parts = array_values(array_unique(array_merge($rel_parts, ['noopener', 'noreferrer'])));

            $anchor->setAttribute('rel', implode(' ', $rel_parts));
        }

        // Ensure links have a discernible accessible name (WCAG link-name).
        foreach ($dom->getElementsByTagName('a') as $anchor) {
            if (! $anchor instanceof DOMElement) {
                continue;
            }

            $has_name = trim($anchor->textContent) !== ''
                || trim($anchor->getAttribute('aria-label')) !== ''
                || trim($anchor->getAttribute('title')) !== '';

            if ($has_name) {
                continue;
            }

            foreach ($anchor->getElementsByTagName('img') as $img) {
                if ($img instanceof DOMElement && trim($img->getAttribute('alt')) !== '') {
                    $has_name = true;
                    break;
                }
            }

            if ($has_name) {
                continue;
            }

            $href = trim($anchor->getAttribute('href'));
            $host = strtolower((string) parse_url($href, PHP_URL_HOST));
            $host = preg_replace('/^www\./', '', (string) $host);

            $anchor->setAttribute('aria-label', $host !== '' ? $host : 'Link');
        }

        // Normalise malformed lists so <ul>/<ol> only directly contain <li>
        // (WCAG "list"). Stray inline/block content gets wrapped in an <li>.
        $lists = array_merge(
            iterator_to_array($dom->getElementsByTagName('ul')),
            iterator_to_array($dom->getElementsByTagName('ol'))
        );

        foreach ($lists as $list) {
            if (! $list instanceof DOMElement) {
                continue;
            }

            $allowed = ['li', 'script', 'template'];
            $group = [];

            $flush = static function () use (&$group, $list, $dom): void {
                if ($group === []) {
                    return;
                }

                $li = $dom->createElement('li');
                $list->insertBefore($li, $group[0]);

                foreach ($group as $node) {
                    $li->appendChild($node);
                }

                $group = [];
            };

            foreach (iterator_to_array($list->childNodes) as $child) {
                if ($child instanceof DOMElement && in_array(strtolower($child->tagName), $allowed, true)) {
                    $flush();
                    continue;
                }

                if ($child instanceof DOMText && trim($child->wholeText) === '') {
                    continue;
                }

                $group[] = $child;
            }

            $flush();
        }

        $processed = '';

        foreach ($wrapper->childNodes as $child) {
            $processed .= $dom->saveHTML($child);
        }

        return $processed;
    }
}

if (! function_exists('matrix_kses_rich_text')) {
    function matrix_kses_rich_text(string $html): string
    {
        if ($html === '') {
            return '';
        }

        return matrix_process_external_links_in_html(wp_kses_post($html));
    }
}

if (! function_exists('matrix_format_newsletter_subtext')) {
    function matrix_format_newsletter_subtext(string $html): string
    {
        if ($html === '' || stripos($html, '<a') !== false || stripos($html, 'click here') === false) {
            return $html;
        }

        $newsletter_url = home_url('/subscribe-to-our-gp-enewsletter/');

        return preg_replace_callback(
            '/\bclick here\b/i',
            static function (array $matches) use ($newsletter_url): string {
                return '<a href="' . esc_url($newsletter_url) . '">' . esc_html($matches[0]) . '</a>';
            },
            $html,
            1
        ) ?? $html;
    }
}

if (! function_exists('matrix_filter_external_links_in_content')) {
    function matrix_filter_external_links_in_content(string $content): string
    {
        if ($content === '' || is_admin()) {
            return $content;
        }

        return matrix_process_external_links_in_html($content);
    }
}

if (! function_exists('matrix_normalize_acf_link')) {
    /**
     * @param mixed $link
     * @return array<string, string>|null
     */
    function matrix_normalize_acf_link($link): ?array
    {
        if (! is_array($link) || empty($link['url'])) {
            return null;
        }

        $url = (string) $link['url'];
        $target = matrix_normalize_link_target($url, (string) ($link['target'] ?? ''));

        return [
            'title' => (string) ($link['title'] ?? ''),
            'url' => $url,
            'target' => $target,
            'rel' => matrix_external_link_rel($target),
        ];
    }
}

if (! function_exists('matrix_filter_acf_link_target')) {
    function matrix_filter_acf_link_target($value)
    {
        if (! is_array($value) || empty($value['url'])) {
            return $value;
        }

        $value['target'] = matrix_normalize_link_target((string) $value['url'], (string) ($value['target'] ?? ''));

        return $value;
    }
}

if (function_exists('add_filter')) {
    add_filter('the_content', 'matrix_filter_external_links_in_content', 25);
    add_filter('acf/format_value/type=link', 'matrix_filter_acf_link_target', 20);
}

if (! function_exists('matrix_get_theme_path_redirect_map')) {
    /**
     * @return array<string, string>
     */
    function matrix_get_theme_path_redirect_map(): array
    {
        return [
            'make-a-referral/refer-an-adult-for-inpatient-care' => '/healthcare-professionals/refer-an-adult-for-inpatient-care/',
            'make-a-referral/refer-an-adolescent-for-inpatient-care' => '/healthcare-professionals/refer-an-adolescent-for-inpatient-care/',
            'make-a-referral/refer-to-the-st-patricks-at-home-service' => '/healthcare-professionals/refer-to-the-st-patricks-at-home-service/',
            'make-a-referral/refer-for-outpatient-care' => '/healthcare-professionals/refer-for-outpatient-care/',
            'make-a-referral/refer-to-a-day-programme' => '/healthcare-professionals/refer-to-a-day-programme/',
        ];
    }
}

if (! function_exists('matrix_maybe_redirect_theme_paths')) {
    function matrix_maybe_redirect_theme_paths(): void
    {
        $request_uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
        $path = trim((string) parse_url($request_uri, PHP_URL_PATH), '/');
        $home_path = trim((string) (function_exists('wp_parse_url') ? wp_parse_url(home_url('/'), PHP_URL_PATH) : parse_url(home_url('/'), PHP_URL_PATH)), '/');

        if ($home_path !== '' && str_starts_with($path, $home_path . '/')) {
            $path = trim(substr($path, strlen($home_path)), '/');
        }

        foreach (matrix_get_theme_path_redirect_map() as $old_path => $destination) {
            if ($path !== trim($old_path, '/')) {
                continue;
            }

            $target = str_starts_with($destination, 'http')
                ? $destination
                : home_url($destination);

            if (function_exists('wp_safe_redirect')) {
                wp_safe_redirect($target, 301);
                exit;
            }
        }
    }
}

if (function_exists('add_action')) {
    add_action('template_redirect', 'matrix_maybe_redirect_theme_paths', 1);
}
