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

if (! function_exists('matrix_is_pdf_url')) {
    function matrix_is_pdf_url(string $url): bool
    {
        $url = trim($url);

        if ($url === '') {
            return false;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $candidate = is_string($path) && $path !== '' ? $path : $url;
        $candidate = explode('?', $candidate, 2)[0];
        $candidate = explode('#', $candidate, 2)[0];

        return str_ends_with(strtolower($candidate), '.pdf');
    }
}

if (! function_exists('matrix_normalize_link_target')) {
    function matrix_normalize_link_target(string $url, string $target = ''): string
    {
        $target = trim($target);

        if ($target === '_blank' || matrix_is_external_url($url) || matrix_is_pdf_url($url)) {
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

if (! function_exists('matrix_new_tab_announcement_html')) {
    /**
     * Visible-to-AT note Silktide expects on links that open a new tab.
     */
    function matrix_new_tab_announcement_html(string $target = ''): string
    {
        if (trim($target) !== '_blank') {
            return '';
        }

        return '<span class="sr-only"> (opens in a new tab)</span>';
    }
}

if (! function_exists('matrix_is_meaningful_outbound_url')) {
    /**
     * True when a URL is worth rendering as a social/outbound control.
     * Hides empty, hash-only, and same-site homepage placeholders that create
     * adjacent duplicate links in accessibility audits.
     */
    function matrix_is_meaningful_outbound_url(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || $url === '#' || stripos($url, 'javascript:') === 0) {
            return false;
        }

        $normalized = untrailingslashit(strtolower(esc_url_raw($url)));
        $home = untrailingslashit(strtolower(home_url('/')));

        if ($normalized === '' || $normalized === $home) {
            return false;
        }

        return true;
    }
}

if (! function_exists('matrix_is_vague_link_text')) {
    /**
     * Link text that fails WCAG 2.4.4 / Silktide "links explain their purpose".
     */
    function matrix_is_vague_link_text(string $text): bool
    {
        $normalized = strtolower(trim(preg_replace('/\s+/u', ' ', $text) ?? $text));

        return in_array($normalized, [
            '',
            'here',
            'click here',
            'learn more',
            'read more',
            'find out more',
            'view more',
            'more',
            'read',
            'link',
        ], true);
    }
}

if (! function_exists('matrix_link_label_from_url')) {
    /**
     * Build a readable label from a URL path slug (last segment).
     */
    function matrix_link_label_from_url(string $url): string
    {
        $url = trim($url);

        if ($url === '' || $url === '#' || str_starts_with($url, '#')) {
            return '';
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $path = trim($path, '/');

        if ($path === '') {
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            $host = preg_replace('/^www\./', '', (string) $host) ?: '';

            return $host !== '' ? $host : '';
        }

        $segments = array_values(array_filter(explode('/', $path), static fn ($s) => $s !== ''));
        $slug = (string) end($segments);
        $slug = preg_replace('/\.(html?|php|aspx?)$/i', '', $slug) ?? $slug;

        if ($slug === '' || preg_match('/^\d+$/', $slug)) {
            return '';
        }

        $label = str_replace(['-', '_'], ' ', $slug);
        $label = preg_replace('/\s+/u', ' ', $label) ?? $label;

        return ucwords(strtolower(trim($label)));
    }
}

if (! function_exists('matrix_resolve_link_accessible_name')) {
    /**
     * Prefer explicit titles; replace vague defaults with context or URL-derived labels.
     */
    function matrix_resolve_link_accessible_name(string $title, string $url = '', string $context = ''): string
    {
        $title = trim($title);
        $context = function_exists('wp_strip_all_tags')
            ? trim(wp_strip_all_tags($context))
            : trim(strip_tags($context));

        if (! matrix_is_vague_link_text($title)) {
            return $title;
        }

        if ($context !== '') {
            return $context;
        }

        $from_url = matrix_link_label_from_url($url);

        if ($from_url !== '') {
            return $from_url;
        }

        $fallback = function_exists('__') ? __('Learn more', 'matrix-starter') : 'Learn more';

        return $title !== '' ? $title : $fallback;
    }
}

if (! function_exists('matrix_is_orphan_word_anchor_href')) {
    /**
     * Word/Office leftover fragment anchors that create empty links in audits.
     */
    function matrix_is_orphan_word_anchor_href(string $href): bool
    {
        $href = trim($href);

        return (bool) preg_match('/^#_(?:msocom|ednref|ftnref|edn|ftn)/i', $href);
    }
}

if (! function_exists('matrix_link_newsletter_subtext_click_here')) {
    function matrix_link_newsletter_subtext_click_here(string $html): string
    {
        if ($html === '' || stripos($html, 'click here') === false || stripos($html, '<a') !== false) {
            return $html;
        }

        $href = esc_url(home_url('/campaigns/subscribe-to-our-gp-enewsletter/'));
        $linked = preg_replace(
            '/\bclick here\b/i',
            '<a href="' . $href . '">subscribe to our GP e-newsletter</a>',
            $html,
            1
        );

        return is_string($linked) ? $linked : $html;
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
            'issuu' => 'Issuu publication',
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

if (! function_exists('matrix_allowed_embed_iframe_hosts')) {
    /**
     * Hosts allowed for client/content embeds (iframe src).
     *
     * @return list<string>
     */
    function matrix_allowed_embed_iframe_hosts(): array
    {
        return [
            'www.youtube.com',
            'youtube.com',
            'www.youtube-nocookie.com',
            'youtube-nocookie.com',
            'player.vimeo.com',
            'vimeo.com',
            'e.issuu.com',
            'issuu.com',
            'www.issuu.com',
            'www.google.com',
            'maps.google.com',
            'www.facebook.com',
            'facebook.com',
            'www.instagram.com',
            'instagram.com',
            'open.spotify.com',
            'w.soundcloud.com',
            'player.podbean.com',
            'www.buzzsprout.com',
        ];
    }
}

if (! function_exists('matrix_is_allowed_embed_iframe_src')) {
    function matrix_is_allowed_embed_iframe_src(string $src): bool
    {
        $src = trim($src);

        if ($src === '' || ! preg_match('#^https?://#i', $src)) {
            return false;
        }

        $host = strtolower((string) parse_url($src, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host) ?: $host;

        foreach (matrix_allowed_embed_iframe_hosts() as $allowed) {
            $allowed = strtolower(preg_replace('/^www\./', '', $allowed) ?: $allowed);

            if ($host === $allowed || str_ends_with($host, '.' . $allowed)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('matrix_kses_allowed_html_with_embeds')) {
    /**
     * Post HTML allowlist plus iframe embeds from trusted hosts.
     *
     * @return array<string, array<string, bool|array>>
     */
    function matrix_kses_allowed_html_with_embeds(): array
    {
        $allowed = function_exists('wp_kses_allowed_html')
            ? wp_kses_allowed_html('post')
            : [];

        if (! is_array($allowed)) {
            $allowed = [];
        }

        $allowed['iframe'] = [
            'src' => true,
            'width' => true,
            'height' => true,
            'frameborder' => true,
            'allowfullscreen' => true,
            'allow' => true,
            'sandbox' => true,
            'style' => true,
            'title' => true,
            'loading' => true,
            'referrerpolicy' => true,
            'name' => true,
            'id' => true,
            'class' => true,
            'scrolling' => true,
        ];

        return $allowed;
    }
}

if (! function_exists('matrix_kses_post_with_embeds')) {
    /**
     * Like wp_kses_post, but keeps trusted iframe embeds (YouTube, Issuu, etc.).
     */
    function matrix_kses_post_with_embeds(string $html): string
    {
        if ($html === '') {
            return '';
        }

        if (! function_exists('wp_kses')) {
            return $html;
        }

        $cleaned = wp_kses($html, matrix_kses_allowed_html_with_embeds());

        // Drop iframes whose src is not on the allowlist.
        if (! str_contains($cleaned, '<iframe')) {
            return $cleaned;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="matrix-kses-root">' . $cleaned . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOWARNING | LIBXML_NOERROR
        );
        libxml_clear_errors();

        if (! $loaded) {
            return $cleaned;
        }

        $wrapper = $dom->getElementById('matrix-kses-root');

        if (! $wrapper instanceof DOMElement) {
            return $cleaned;
        }

        foreach (iterator_to_array($dom->getElementsByTagName('iframe')) as $iframe) {
            if (! $iframe instanceof DOMElement) {
                continue;
            }

            $src = trim($iframe->getAttribute('src'));

            if (! matrix_is_allowed_embed_iframe_src($src)) {
                $iframe->parentNode?->removeChild($iframe);
            }
        }

        $out = '';

        foreach ($wrapper->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }
}

if (! function_exists('matrix_normalize_absolute_embeds_in_html')) {
    /**
     * Wrap absolute-positioned iframes (e.g. Issuu) so they cannot cover sibling content.
     */
    function matrix_normalize_absolute_embeds_in_html(string $html): string
    {
        if ($html === '' || ! str_contains($html, '<iframe')) {
            return $html;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="matrix-embed-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOWARNING | LIBXML_NOERROR
        );
        libxml_clear_errors();

        if (! $loaded) {
            return $html;
        }

        $wrapper = $dom->getElementById('matrix-embed-root');

        if (! $wrapper instanceof DOMElement) {
            return $html;
        }

        $changed = false;

        foreach (iterator_to_array($dom->getElementsByTagName('iframe')) as $iframe) {
            if (! $iframe instanceof DOMElement || ! $iframe->parentNode) {
                continue;
            }

            $parent = $iframe->parentNode;

            if ($parent instanceof DOMElement
                && $parent->hasAttribute('class')
                && str_contains($parent->getAttribute('class'), 'matrix-embed')
            ) {
                continue;
            }

            $style = strtolower($iframe->getAttribute('style'));
            $is_absolute = str_contains($style, 'position:absolute') || str_contains($style, 'position: absolute');
            $src = trim($iframe->getAttribute('src'));
            $is_issuu = str_contains(strtolower($src), 'issuu.com');

            if (! $is_absolute && ! $is_issuu) {
                // Standard sized embeds (YouTube etc.): ensure they stay within the column.
                $class = trim($iframe->getAttribute('class'));

                if (! str_contains($class, 'matrix-embed__frame')) {
                    $iframe->setAttribute('class', trim($class . ' matrix-embed__frame'));
                    $changed = true;
                }

                continue;
            }

            $container = $dom->createElement('div');
            $container->setAttribute(
                'class',
                $is_issuu ? 'matrix-embed matrix-embed--issuu' : 'matrix-embed matrix-embed--absolute'
            );

            // Prefer wrapping the iframe in place; if it's alone in a <p>, replace the <p>.
            if ($parent instanceof DOMElement
                && strtolower($parent->tagName) === 'p'
                && $parent->childNodes->length === 1
                && $parent->parentNode
            ) {
                $parent->parentNode->insertBefore($container, $parent);
                $container->appendChild($iframe);
                $parent->parentNode->removeChild($parent);
            } else {
                $parent->insertBefore($container, $iframe);
                $container->appendChild($iframe);
            }

            $iframe->setAttribute(
                'style',
                'position:absolute;border:none;width:100%;height:100%;left:0;right:0;top:0;bottom:0;'
            );
            $class = trim($iframe->getAttribute('class'));

            if (! str_contains($class, 'matrix-embed__frame')) {
                $iframe->setAttribute('class', trim($class . ' matrix-embed__frame'));
            }

            $changed = true;
        }

        if (! $changed) {
            return $html;
        }

        $out = '';

        foreach ($wrapper->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
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

        // Remove Word/Office leftover fragment anchors (empty / comment links).
        foreach (iterator_to_array($dom->getElementsByTagName('a')) as $anchor) {
            if (! $anchor instanceof DOMElement || ! $anchor->parentNode) {
                continue;
            }

            $href = trim($anchor->getAttribute('href'));
            $visible = trim(preg_replace('/\s+/u', ' ', $anchor->textContent) ?? '');
            $is_orphan_href = matrix_is_orphan_word_anchor_href($href)
                || ($href === '' && $visible === '' && trim($anchor->getAttribute('aria-label')) === '');

            if (! $is_orphan_href) {
                continue;
            }

            while ($anchor->firstChild) {
                $anchor->parentNode->insertBefore($anchor->firstChild, $anchor);
            }

            $anchor->parentNode->removeChild($anchor);
        }

        foreach ($dom->getElementsByTagName('a') as $anchor) {
            if (! $anchor instanceof DOMElement) {
                continue;
            }

            $href = trim($anchor->getAttribute('href'));

            if ($href === '' || (! matrix_is_external_url($href) && ! matrix_is_pdf_url($href))) {
                continue;
            }

            $anchor->setAttribute('target', '_blank');

            $rel = trim($anchor->getAttribute('rel'));
            $rel_parts = $rel !== '' ? preg_split('/\s+/', $rel) ?: [] : [];
            $rel_parts = array_values(array_unique(array_merge($rel_parts, ['noopener', 'noreferrer'])));

            $anchor->setAttribute('rel', implode(' ', $rel_parts));

            // Announce new tab to assistive tech (WCAG 3.2.5 / Silktide new-tab check).
            $already_announced = (bool) preg_match(
                '/opens in (a )?new (tab|window)/i',
                $anchor->textContent . ' ' . $anchor->getAttribute('aria-label')
            );

            if (! $already_announced) {
                $existing_label = trim($anchor->getAttribute('aria-label'));
                if ($existing_label !== '') {
                    $anchor->setAttribute('aria-label', $existing_label . ' (opens in a new tab)');
                } else {
                    $sr = $dom->createElement('span', ' (opens in a new tab)');
                    $sr->setAttribute('class', 'sr-only');
                    $anchor->appendChild($sr);
                }
            }
        }

        // Replace vague link text with a destination-derived label (WCAG 2.4.4).
        foreach ($dom->getElementsByTagName('a') as $anchor) {
            if (! $anchor instanceof DOMElement) {
                continue;
            }

            $href = trim($anchor->getAttribute('href'));
            $visible = trim(preg_replace('/\s+/u', ' ', $anchor->textContent) ?? '');

            // Ignore sr-only "opens in a new tab" when judging vagueness.
            $visible_for_check = trim(preg_replace('/\s*\(opens in (a )?new (tab|window)\)\s*/i', '', $visible) ?? '');

            if (! matrix_is_vague_link_text($visible_for_check)) {
                continue;
            }

            $label = matrix_resolve_link_accessible_name($visible_for_check, $href);

            if ($label === '' || strcasecmp($label, $visible_for_check) === 0) {
                continue;
            }

            while ($anchor->firstChild) {
                $anchor->removeChild($anchor->firstChild);
            }

            $anchor->appendChild($dom->createTextNode($label));

            if (matrix_is_external_url($href) || matrix_is_pdf_url($href)) {
                $sr = $dom->createElement('span', ' (opens in a new tab)');
                $sr->setAttribute('class', 'sr-only');
                $anchor->appendChild($sr);
            }
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

        $cleaned = function_exists('matrix_kses_post_with_embeds')
            ? matrix_kses_post_with_embeds($html)
            : wp_kses_post($html);

        return matrix_process_external_links_in_html($cleaned);
    }
}

if (! function_exists('matrix_filter_external_links_in_content')) {
    function matrix_filter_external_links_in_content(string $content): string
    {
        if ($content === '' || is_admin()) {
            return $content;
        }

        $content = matrix_normalize_absolute_embeds_in_html($content);

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
            'title' => matrix_resolve_link_accessible_name((string) ($link['title'] ?? ''), $url),
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
     * Theme-level 301 redirects for legacy / deleted paths.
     *
     * Base map covers hierarchy moves and legacy slugs. Workbook "Delete"
     * destinations are merged from inc/data/path-redirect-map.json when present.
     *
     * @return array<string, string>
     */
    function matrix_get_theme_path_redirect_map(): array
    {
        $map = [
            // Legacy make-a-referral paths
            'make-a-referral/refer-an-adult-for-inpatient-care' => '/healthcare-professionals/refer-an-adult-for-inpatient-care/',
            'make-a-referral/refer-an-adolescent-for-inpatient-care' => '/healthcare-professionals/refer-an-adolescent-for-inpatient-care/',
            'make-a-referral/refer-to-the-st-patricks-at-home-service' => '/healthcare-professionals/refer-to-the-st-patricks-at-home-service/',
            'make-a-referral/refer-for-outpatient-care' => '/healthcare-professionals/refer-for-outpatient-care/',
            'make-a-referral/refer-to-a-day-programme' => '/healthcare-professionals/refer-to-a-day-programme/',
            'make-a-referral' => '/healthcare-professionals/',

            // Day programmes
            'service-users-and-visitors/attending-our-day-programmes' => '/what-we-offer/day-programmes/',
            'attending-our-day-programmes' => '/what-we-offer/day-programmes/',

            // Renamed pages
            'service-users-and-visitors/schizophrenia-and-psychosis' => '/service-users-and-visitors/schizophrenia/',

            // Hierarchy fixes - pages moved to correct parents per Slickplan sitemap
            'national-centre' => '/about-us/our-present-and-future/national-centre/',
            'new-hospital' => '/about-us/our-present-and-future/new-hospital/',
            'advocacy-centre' => '/about-us/our-present-and-future/advocacy-centre/',
            'academic-institute' => '/about-us/our-present-and-future/academic-institute/',
            'traning-centre' => '/about-us/our-present-and-future/traning-centre/',
            'extending-and-enhancing-our-services' => '/about-us/our-present-and-future/extending-and-enhancing-our-services/',
            'about-us/extending-our-services' => '/about-us/our-present-and-future/extending-and-enhancing-our-services/',
            'extending-our-services' => '/about-us/our-present-and-future/extending-and-enhancing-our-services/',
            'about-us/partnering-with-service-users' => '/about-us/our-present-and-future/partnering-with-service-users/',
            'about-us/psychiatrists' => '/about-us/our-team/psychiatrists/',
            'about-us/social-workers' => '/about-us/our-team/social-workers/',
            'about-us/nurses' => '/about-us/our-team/nurses/',
            'about-us/occupational-therapists' => '/about-us/our-team/occupational-therapists/',
            'about-us/psychologists' => '/about-us/our-team/psychologists/',
            'about-us/pharmacists' => '/about-us/our-team/pharmacists/',
            'careers' => '/about-us/careers/',
            'careers/attending-an-interview' => '/about-us/careers/attending-an-interview/',
            'recruitment-and-useful-information' => '/about-us/careers/recruitment-and-useful-information/',
            'recruitment-and-useful-information/staff-wellbeing' => '/about-us/careers/recruitment-and-useful-information/staff-wellbeing/',
            'recruitment-and-useful-information/how-to-get-work-experience' => '/about-us/careers/recruitment-and-useful-information/how-to-get-work-experience/',
            'recruitment-and-useful-information/how-to-apply-for-a-role' => '/about-us/careers/recruitment-and-useful-information/how-to-apply-for-a-role/',
            'training-centre' => '/healthcare-professionals/training-centre/',
            'directions-and-parking' => '/service-users-and-visitors/directions-and-parking/',
            'service-user-it-support' => '/service-users-and-visitors/service-user-it-support/',
            'about-your-portal' => '/your-portal/about-your-portal/',
            'register-for-your-portal' => '/your-portal/register-for-your-portal/',
        ];

        $theme_dir = function_exists('get_template_directory')
            ? get_template_directory()
            : dirname(__DIR__);

        // Prefer the tracked theme data file so redirects deploy with the theme.
        // Keep the legacy gitignored path as a local fallback during migration.
        $candidates = [
            $theme_dir . '/inc/data/path-redirect-map.json',
            $theme_dir . '/old/content/delete-redirect-map.json',
        ];

        foreach ($candidates as $delete_map_file) {
            if (! is_readable($delete_map_file)) {
                continue;
            }

            $decoded = json_decode((string) file_get_contents($delete_map_file), true);

            if (! is_array($decoded)) {
                continue;
            }

            foreach ($decoded as $from => $to) {
                if (! is_string($from) || ! is_string($to) || $from === '' || $to === '') {
                    continue;
                }

                $map[trim($from, '/')] = $to;
            }

            break;
        }

        return $map;
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
    // Run before Password Protected (priority -10) so legacy paths redirect even when gated.
    add_action('template_redirect', 'matrix_maybe_redirect_theme_paths', -20);
}
