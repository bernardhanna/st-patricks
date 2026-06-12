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

if (! function_exists('matrix_process_external_links_in_html')) {
    function matrix_process_external_links_in_html(string $html): string
    {
        if ($html === '' || ! str_contains($html, '<a')) {
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
