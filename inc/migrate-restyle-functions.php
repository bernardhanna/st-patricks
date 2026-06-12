<?php

/**
 * Build designed flexi layouts from migrated Umbraco HTML.
 */

require_once __DIR__ . '/migrate-functions.php';

if (! function_exists('matrix_migrate_restyle_section_padding')) {
    function matrix_migrate_restyle_section_padding(): array
    {
        return [
            ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
            ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
        ];
    }
}

if (! function_exists('matrix_migrate_restyle_accordion_padding')) {
    function matrix_migrate_restyle_accordion_padding(): array
    {
        return [
            ['screen_size' => 'mob', 'padding_top' => '1', 'padding_bottom' => '3'],
            ['screen_size' => 'lg', 'padding_top' => '1', 'padding_bottom' => '6.25'],
        ];
    }
}

if (! function_exists('matrix_migrate_restyle_accordion_item')) {
    function matrix_migrate_restyle_accordion_item(string $title, string $content, bool $starts_open = false): array
    {
        return [
            'title' => $title,
            'starts_open' => $starts_open ? 1 : 0,
            'content_rows' => [
                [
                    'row_type' => 'text',
                    'icon_key' => '',
                    'icon' => '',
                    'content' => $content,
                ],
            ],
        ];
    }
}

if (! function_exists('matrix_migrate_attachment_id_from_url')) {
    function matrix_migrate_attachment_id_from_url(string $url): int
    {
        $url = trim($url);

        if ($url === '') {
            return 0;
        }

        if (function_exists('attachment_url_to_postid')) {
            $attachment_id = (int) attachment_url_to_postid($url);

            if ($attachment_id > 0) {
                return $attachment_id;
            }
        }

        $legacy_path = matrix_migrate_legacy_media_path_from_url($url);

        if ($legacy_path !== '') {
            return matrix_migrate_attachment_id_for_source_path($legacy_path);
        }

        return 0;
    }
}

if (! function_exists('matrix_migrate_extract_leading_image_from_html')) {
    /**
     * @return array{content: string, image_id: int}
     */
    function matrix_migrate_extract_leading_image_from_html(string $html): array
    {
        $html = trim($html);

        if ($html === '') {
            return ['content' => '', 'image_id' => 0];
        }

        $image_id = 0;
        $stripped = $html;

        if (preg_match('/<figure[^>]*>.*?<img[^>]+src=["\']([^"\']+)["\'][^>]*>.*?<\/figure>/is', $html, $matches, PREG_OFFSET_CAPTURE)) {
            $image_id = matrix_migrate_attachment_id_from_url($matches[1][0]);
            $stripped = substr($html, 0, $matches[0][1]) . substr($html, $matches[0][1] + strlen($matches[0][0]));
        } elseif (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches, PREG_OFFSET_CAPTURE)) {
            $image_id = matrix_migrate_attachment_id_from_url($matches[1][0]);
            $stripped = substr($html, 0, $matches[0][1]) . substr($html, $matches[0][1] + strlen($matches[0][0]));
        }

        return [
            'content' => trim($stripped),
            'image_id' => $image_id,
        ];
    }
}

if (! function_exists('matrix_migrate_derive_section_heading')) {
    function matrix_migrate_derive_section_heading(string $html, string $fallback = ''): string
    {
        if (preg_match('/<h2[^>]*>(.*?)<\/h2>/is', $html, $matches)) {
            return trim(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        if (preg_match('/<h3[^>]*>(.*?)<\/h3>/is', $html, $matches)) {
            return trim(html_entity_decode(strip_tags($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        return $fallback;
    }
}

if (! function_exists('matrix_migrate_remove_leading_heading_from_content')) {
    function matrix_migrate_remove_leading_heading_from_content(string $html, string $heading): string
    {
        if ($html === '' || $heading === '') {
            return $html;
        }

        $pattern = '/<h[23][^>]*>\s*' . preg_quote($heading, '/') . '\s*<\/h[23]>\s*/iu';

        return trim((string) preg_replace($pattern, '', $html, 1));
    }
}

if (! function_exists('matrix_migrate_collect_restyle_image_pool')) {
    /**
     * @param array<string, mixed> $structured
     * @return int[]
     */
    function matrix_migrate_collect_restyle_image_pool(array $structured, int $hero_image_id = 0): array
    {
        $pool = [];
        $blocks = is_array($structured['blocks'] ?? null) ? $structured['blocks'] : [];

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            if (($block['type'] ?? '') === 'gallery') {
                foreach ((array) ($block['images'] ?? []) as $path) {
                    $attachment_id = matrix_migrate_attachment_id_for_source_path((string) $path);

                    if ($attachment_id > 0) {
                        $pool[] = $attachment_id;
                    }
                }
            }
        }

        foreach ($blocks as $block) {
            if (! is_array($block) || ($block['type'] ?? '') !== 'content') {
                continue;
            }

            $html = trim((string) ($block['intro'] ?? '') . (string) ($block['content'] ?? ''));

            while ($html !== '' && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
                $attachment_id = matrix_migrate_attachment_id_from_url($matches[1]);

                if ($attachment_id > 0) {
                    $pool[] = $attachment_id;
                }

                $html = (string) preg_replace('/<img[^>]+>/i', '', $html, 1);
            }
        }

        $pool = array_values(array_unique(array_filter($pool)));

        if ($hero_image_id > 0 && count($pool) > 1) {
            $pool = array_values(array_filter($pool, static fn (int $id): bool => $id !== $hero_image_id));
        }

        if ($pool === [] && $hero_image_id > 0) {
            $pool[] = $hero_image_id;
        }

        shuffle($pool);

        return $pool;
    }
}

if (! function_exists('matrix_migrate_format_content_intro_html')) {
    function matrix_migrate_format_content_intro_html(string $intro): string
    {
        $intro = trim($intro);

        if ($intro === '') {
            return '';
        }

        if (str_contains($intro, '<')) {
            return $intro;
        }

        return '<p>' . esc_html($intro) . '</p>';
    }
}

if (! function_exists('matrix_migrate_make_content_flexi_row')) {
    function matrix_migrate_make_content_flexi_row(
        string $heading,
        string $intro_text,
        string $content,
        int $image_id,
        string $layout_style,
        string $background_color,
        array $padding_settings
    ): array {
        $has_image = $image_id > 0;

        return [
            'acf_fc_layout' => 'content',
            'heading' => $heading,
            'heading_tag' => 'h2',
            'accent_position' => 'below_heading',
            'intro_text' => $intro_text,
            'content' => $content,
            'image' => $has_image ? $image_id : '',
            'column_layout' => $has_image ? 'two_column' : 'one_column',
            'layout_style' => $has_image ? $layout_style : 'image_left',
            'text_width' => 'wide',
            'background_type' => 'color',
            'background_color' => $background_color,
            'padding_settings' => $padding_settings,
        ];
    }
}

if (! function_exists('matrix_migrate_prepare_content_section')) {
    /**
     * @param int[] $image_pool
     * @return array{heading: string, intro_text: string, content: string, image_id: int, layout_style: string}|null
     */
    function matrix_migrate_prepare_content_section(
        string $heading,
        string $block_intro,
        string $content,
        array $image_pool,
        int &$pool_cursor,
        int $section_index
    ): ?array {
        $content = trim($content);
        $block_intro = trim($block_intro);

        if ($heading === '' && $content === '' && $block_intro === '') {
            return null;
        }

        if ($heading === '') {
            $source_html = matrix_migrate_format_content_intro_html($block_intro) . $content;
            $heading = matrix_migrate_derive_section_heading($source_html, '');
            $content = matrix_migrate_remove_leading_heading_from_content($content, $heading);

            if ($heading === '') {
                $fallbacks = ['Overview', 'In more detail', 'Further information', 'Additional information'];
                $heading = $fallbacks[min($section_index, count($fallbacks) - 1)];
            }
        }

        $extracted = matrix_migrate_extract_leading_image_from_html($content);
        $content = $extracted['content'];
        $image_id = (int) $extracted['image_id'];

        if ($image_id === 0 && $image_pool !== []) {
            $image_id = $image_pool[$pool_cursor % count($image_pool)];
            $pool_cursor++;
        }

        return [
            'heading' => $heading,
            'intro_text' => matrix_migrate_format_content_intro_html($block_intro),
            'content' => $content,
            'image_id' => $image_id,
            'layout_style' => $section_index % 2 === 0 ? 'image_right' : 'image_left',
        ];
    }
}

if (! function_exists('matrix_migrate_html_file_for_old_path')) {
    function matrix_migrate_html_file_for_old_path(string $old_path): string
    {
        $old_path = trim($old_path, '/');

        foreach (matrix_migrate_list_html_files() as $item) {
            if ($item['path'] === $old_path) {
                return $item['file'];
            }
        }

        return '';
    }
}

if (! function_exists('matrix_migrate_clean_rte_html')) {
    function matrix_migrate_clean_rte_html(string $html): string
    {
        if ($html === '') {
            return '';
        }

        if (! function_exists('matrix_migrate_parse_html_document')) {
            return $html;
        }

        $dom = matrix_migrate_parse_html_document($html);

        if (! $dom instanceof DOMDocument) {
            return $html;
        }

        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//h3[contains(concat(" ", normalize-space(@class), " "), " hide-for-main ")]') as $node) {
            if ($node->parentNode instanceof DOMNode) {
                $node->parentNode->removeChild($node);
            }
        }

        foreach ($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " section-head ")]') as $wrapper) {
            if (! $wrapper->parentNode instanceof DOMNode) {
                continue;
            }

            $parent = $wrapper->parentNode;

            while ($wrapper->firstChild) {
                $parent->insertBefore($wrapper->firstChild, $wrapper);
            }

            $parent->removeChild($wrapper);
        }

        $body = $dom->getElementsByTagName('body')->item(0);

        if (! $body instanceof DOMNode) {
            return $html;
        }

        $cleaned = trim(matrix_migrate_dom_inner_html($body));

        return matrix_migrate_rewrite_html_urls($cleaned);
    }
}

if (! function_exists('matrix_migrate_extract_structured_page')) {
    /**
     * @return array<string, mixed>|null
     */
    function matrix_migrate_extract_structured_page(string $html, string $old_path): ?array
    {
        $base = matrix_migrate_extract_parsed_page($html, $old_path);

        if ($base === null) {
            return null;
        }

        $dom = matrix_migrate_parse_html_document($html);

        if (! $dom instanceof DOMDocument) {
            return null;
        }

        $xpath = new DOMXPath($dom);
        $blocks = [];
        $inner_nav = [];
        $breadcrumbs = [];
        $hero_button = null;

        $current_crumb_label = '';

        foreach ($xpath->query('//div[contains(@class,"breadcrumb-container")]//a') as $anchor) {
            if (! $anchor instanceof DOMElement) {
                continue;
            }

            $breadcrumbs[] = [
                'title' => trim($anchor->textContent),
                'url' => matrix_migrate_resolve_migrated_url($anchor->getAttribute('href')),
                'target' => '',
            ];
        }

        $last_crumb = $xpath->query('//div[contains(@class,"breadcrumb-container")]//li[last()]')->item(0);

        if ($last_crumb instanceof DOMNode) {
            $current_crumb_label = trim($last_crumb->textContent);
        }

        foreach ($xpath->query('//section[contains(@class,"inner-nav")]//a') as $anchor) {
            if (! $anchor instanceof DOMElement) {
                continue;
            }

            $title = trim($anchor->textContent);

            if ($title === '') {
                continue;
            }

            $inner_nav[] = [
                'link' => [
                    'title' => $title,
                    'url' => matrix_migrate_resolve_migrated_url($anchor->getAttribute('href')),
                    'target' => '',
                ],
            ];
        }

        $hero_anchor = $xpath->query('//section[contains(@class,"hero")]//a[contains(@class,"button")]')->item(0);

        if ($hero_anchor instanceof DOMElement) {
            $hero_href = matrix_migrate_resolve_migrated_url($hero_anchor->getAttribute('href'));

            if ($hero_href !== '' && $hero_href !== '#') {
                $hero_button = [
                    'title' => trim($hero_anchor->textContent) ?: 'Learn more',
                    'url' => $hero_href,
                    'target' => '',
                ];
            }
        }

        $main = $xpath->query('//div[contains(@class,"inner-content-main")]')->item(0);

        if ($main instanceof DOMNode) {
            foreach ($xpath->query('.//section[contains(@class,"pb-rte") or contains(@class,"pb-accordion") or contains(@class,"pb-gallery") or contains(@class,"pb-pods")]', $main) as $section) {
                if (! $section instanceof DOMElement) {
                    continue;
                }

                $class = $section->getAttribute('class');

                if (str_contains($class, 'pb-rte')) {
                    $rte = $xpath->query('.//div[contains(@class,"rte-styles")]', $section)->item(0);

                    if (! $rte instanceof DOMNode) {
                        continue;
                    }

                    $heading = matrix_migrate_dom_text($xpath->query('.//div[contains(@class,"section-head")]//h2', $section)->item(0));
                    $section_intro = matrix_migrate_dom_text($xpath->query('.//div[contains(@class,"section-head")]//p', $section)->item(0));
                    $content = matrix_migrate_clean_rte_html(matrix_migrate_dom_inner_html($rte));

                    if ($heading !== '') {
                        $content = (string) preg_replace('/<h2[^>]*>' . preg_quote($heading, '/') . '<\/h2>\s*/i', '', $content, 1);
                    }

                    if ($section_intro !== '') {
                        $content = (string) preg_replace('/<p[^>]*>' . preg_quote($section_intro, '/') . '<\/p>\s*/i', '', $content, 1);
                    }

                    $content = trim($content);

                    if ($heading === '' && $section_intro === '' && $content === '') {
                        continue;
                    }

                    if ($heading === '' && preg_match('/<h2[^>]*>(.*?)<\/h2>/i', $content, $matches)) {
                        $heading = trim(strip_tags($matches[1]));
                        $content = (string) preg_replace('/<h2[^>]*>.*?<\/h2>\s*/i', '', $content, 1);
                        $content = trim($content);
                    }

                    if ($heading === '' && $section_intro === '' && str_contains($content, 'intro')) {
                        if (preg_match('/<p[^>]*class="intro"[^>]*>(.*?)<\/p>/is', $content, $matches)) {
                            $section_intro = trim(strip_tags($matches[1]));
                            $content = (string) preg_replace('/<p[^>]*class="intro"[^>]*>.*?<\/p>\s*/is', '', $content, 1);
                            $content = trim($content);
                        }
                    }

                    if ($heading === '' && $section_intro === '' && $content === '') {
                        continue;
                    }

                    if ($heading === '' && $content === '' && $section_intro !== '') {
                        continue;
                    }

                    $blocks[] = [
                        'type' => 'content',
                        'heading' => $heading,
                        'intro' => $section_intro,
                        'content' => $content,
                    ];

                    continue;
                }

                if (str_contains($class, 'pb-accordion')) {
                    $heading = matrix_migrate_dom_text($xpath->query('.//div[contains(@class,"section-head")]//h2', $section)->item(0));
                    $intro_html = '';
                    $intro_node = $xpath->query('.//div[contains(@class,"section-head")]/following-sibling::p[1]', $section)->item(0);

                    if (! $intro_node instanceof DOMNode) {
                        $intro_node = $xpath->query('.//p', $section)->item(0);
                    }

                    if ($intro_node instanceof DOMNode) {
                        $intro_html = matrix_migrate_clean_rte_html(matrix_migrate_dom_inner_html($intro_node));
                    }

                    $items = [];

                    foreach ($xpath->query('.//li[contains(@class,"accordion-item")]', $section) as $item) {
                        if (! $item instanceof DOMNode) {
                            continue;
                        }

                        $item_heading = matrix_migrate_dom_text($xpath->query('.//a[contains(@class,"accordion-title")]//h3|.//a[contains(@class,"accordion-title")]', $item)->item(0));
                        $item_body = $xpath->query('.//div[contains(@class,"accordion-content")]', $item)->item(0);
                        $item_content = $item_body instanceof DOMNode
                            ? matrix_migrate_clean_rte_html(matrix_migrate_dom_inner_html($item_body))
                            : '';

                        if ($item_heading === '' && trim(strip_tags($item_content)) === '') {
                            continue;
                        }

                        $items[] = [
                            'title' => $item_heading,
                            'content' => $item_content,
                        ];
                    }

                    if ($items !== []) {
                        $blocks[] = [
                            'type' => 'accordion',
                            'heading' => $heading,
                            'intro' => $intro_html,
                            'items' => $items,
                        ];
                    }

                    continue;
                }

                if (str_contains($class, 'pb-gallery')) {
                    $images = [];

                    foreach ($xpath->query('.//a[contains(@class,"popup-gallery")]/@href', $section) as $href) {
                        $src = trim((string) $href->nodeValue);

                        if ($src !== '') {
                            $images[] = $src;
                        }
                    }

                    if ($images !== []) {
                        $blocks[] = [
                            'type' => 'gallery',
                            'images' => $images,
                        ];
                    }

                    continue;
                }

                if (str_contains($class, 'pb-pods')) {
                    $pods = [];
                    $pod_heading = matrix_migrate_dom_text($xpath->query('.//div[contains(@class,"section-head")]//h2', $section)->item(0));

                    foreach ($xpath->query('.//div[contains(concat(" ", normalize-space(@class), " "), " pod ")]', $section) as $pod) {
                        if (! $pod instanceof DOMElement) {
                            continue;
                        }

                        $title = matrix_migrate_dom_text($xpath->query('.//div[contains(@class,"pod-text")]//h2|.//h2', $pod)->item(0));
                        $description = matrix_migrate_dom_text($xpath->query('.//div[contains(@class,"pod-text")]//p', $pod)->item(0));
                        $href = '';
                        $anchor = $xpath->query('.//a[@href][1]', $pod)->item(0);

                        if ($anchor instanceof DOMElement) {
                            $raw_href = trim($anchor->getAttribute('href'));

                            if ($raw_href !== '' && $raw_href !== '#') {
                                $href = matrix_migrate_resolve_migrated_url($raw_href);
                            }
                        }

                        $image_id = 0;
                        $img = $xpath->query('.//img/@src', $pod)->item(0);

                        if ($img instanceof DOMNode) {
                            $image_id = matrix_migrate_attachment_id_for_source_path((string) $img->nodeValue);
                        }

                        if ($title === '' || $href === '') {
                            continue;
                        }

                        $pods[] = [
                            'title' => $title,
                            'description' => $description,
                            'url' => $href,
                            'image_id' => $image_id,
                        ];
                    }

                    if ($pods !== []) {
                        $blocks[] = [
                            'type' => 'pods',
                            'heading' => $pod_heading !== '' ? $pod_heading : 'Related',
                            'items' => $pods,
                        ];
                    }
                }
            }

            foreach ($xpath->query('.//section[contains(@class,"tier-3-pagination")]', $main) as $pagination) {
                if (! $pagination instanceof DOMElement) {
                    continue;
                }

                $pagination_links = [];

                foreach ($xpath->query('.//a[@href]', $pagination) as $anchor) {
                    if (! $anchor instanceof DOMElement) {
                        continue;
                    }

                    $raw_href = trim($anchor->getAttribute('href'));

                    if ($raw_href === '' || $raw_href === '#') {
                        continue;
                    }

                    $title = trim($anchor->textContent);

                    if ($title === '') {
                        continue;
                    }

                    $pagination_links[] = [
                        'title' => $title,
                        'description' => '',
                        'url' => matrix_migrate_resolve_migrated_url($raw_href),
                        'image_id' => 0,
                    ];
                }

                if ($pagination_links !== []) {
                    $blocks[] = [
                        'type' => 'pods',
                        'heading' => 'Continue to',
                        'items' => $pagination_links,
                    ];
                }
            }
        }

        return array_merge($base, [
            'blocks' => $blocks,
            'inner_nav' => $inner_nav,
            'breadcrumbs' => $breadcrumbs,
            'current_crumb_label' => $current_crumb_label,
            'hero_button' => $hero_button,
        ]);
    }
}

if (! function_exists('matrix_migrate_build_structured_flexi_rows')) {
    /**
     * @param array<string, mixed> $structured
     * @return array<int, array<string, mixed>>
     */
    function matrix_migrate_build_structured_flexi_rows(array $structured, int $hero_image_id = 0): array
    {
        $title = (string) ($structured['title'] ?? '');
        $intro = (string) ($structured['intro'] ?? '');
        $hero_button = is_array($structured['hero_button'] ?? null) ? $structured['hero_button'] : null;
        $breadcrumbs = is_array($structured['breadcrumbs'] ?? null) ? $structured['breadcrumbs'] : [];
        $inner_nav = is_array($structured['inner_nav'] ?? null) ? $structured['inner_nav'] : [];
        $blocks = is_array($structured['blocks'] ?? null) ? $structured['blocks'] : [];

        $manual_breadcrumbs = [];

        foreach ($breadcrumbs as $crumb) {
            if (! is_array($crumb) || ($crumb['title'] ?? '') === '' || ($crumb['url'] ?? '') === '') {
                continue;
            }

            if (strcasecmp((string) $crumb['title'], $title) === 0) {
                continue;
            }

            $manual_breadcrumbs[] = [
                'breadcrumb_link' => [
                    'title' => (string) $crumb['title'],
                    'url' => (string) $crumb['url'],
                    'target' => (string) ($crumb['target'] ?? ''),
                ],
            ];
        }

        if ($manual_breadcrumbs === []) {
            $manual_breadcrumbs[] = [
                'breadcrumb_link' => [
                    'title' => 'Home',
                    'url' => home_url('/'),
                    'target' => '',
                ],
            ];
        }

        $current_crumb = trim((string) ($structured['current_crumb_label'] ?? ''));

        if ($current_crumb === '') {
            $current_crumb = $title;
        }

        $hero = array_merge(matrix_get_utility_page_hero_config($title, $intro), [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'show_breadcrumbs' => 1,
            'text_max_width' => 'default',
            'breadcrumb_source' => 'manual',
            'manual_breadcrumbs' => $manual_breadcrumbs,
            'current_crumb_label' => $current_crumb,
            'hero_image' => $hero_image_id,
            'primary_button' => $hero_button ?: '',
        ]);

        $rows = [$hero];

        if ($inner_nav !== []) {
            $rows[] = [
                'acf_fc_layout' => 'useful_links',
                'heading_tag' => 'h2',
                'heading' => 'In this section',
                'variant' => 'flexi',
                'links' => $inner_nav,
                'background_color' => '#F1F8F9',
                'padding_settings' => [
                    ['screen_size' => 'mob', 'padding_top' => '1.5', 'padding_bottom' => '1.5'],
                    ['screen_size' => 'lg', 'padding_top' => '2', 'padding_bottom' => '2'],
                ],
            ];
        }

        $section_padding = matrix_migrate_restyle_section_padding();
        $accordion_padding = matrix_migrate_restyle_accordion_padding();
        $backgrounds = ['#FFFFFF', '#FBFAF7'];
        $bg_index = 0;
        $image_pool = matrix_migrate_collect_restyle_image_pool($structured, $hero_image_id);
        $pool_cursor = 0;
        $section_index = 0;

        foreach ($blocks as $block) {
            if (! is_array($block)) {
                continue;
            }

            $type = (string) ($block['type'] ?? '');

            if ($type === 'content') {
                $heading = trim((string) ($block['heading'] ?? ''));
                $content = trim((string) ($block['content'] ?? ''));
                $block_intro = trim((string) ($block['intro'] ?? ''));

                $prepared = matrix_migrate_prepare_content_section(
                    $heading,
                    $block_intro,
                    $content,
                    $image_pool,
                    $pool_cursor,
                    $section_index
                );

                if ($prepared === null) {
                    continue;
                }

                if (strcasecmp($prepared['heading'], 'Shared goals') !== 0 && strcasecmp(strip_tags($prepared['intro_text']), $intro) === 0) {
                    $prepared['intro_text'] = '';
                }

                $rows[] = matrix_migrate_make_content_flexi_row(
                    $prepared['heading'],
                    $prepared['intro_text'],
                    $prepared['content'],
                    $prepared['image_id'],
                    $prepared['layout_style'],
                    $backgrounds[$bg_index % 2],
                    $section_padding
                );
                $bg_index++;
                $section_index++;

                continue;
            }

            if ($type === 'accordion') {
                $heading = trim((string) ($block['heading'] ?? ''));
                $block_intro = trim((string) ($block['intro'] ?? ''));
                $items = is_array($block['items'] ?? null) ? $block['items'] : [];

                if ($heading !== '' || $block_intro !== '') {
                    $rows[] = [
                        'acf_fc_layout' => 'content',
                        'heading' => $heading !== '' ? $heading : 'Further information',
                        'heading_tag' => 'h2',
                        'accent_position' => 'below_heading',
                        'intro_text' => $block_intro,
                        'content' => '',
                        'image' => '',
                        'column_layout' => 'one_column',
                        'layout_style' => 'image_left',
                        'text_width' => 'wide',
                        'background_type' => 'color',
                        'background_color' => $backgrounds[$bg_index % 2],
                        'padding_settings' => [
                            ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '1'],
                            ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '1'],
                        ],
                    ];
                    $bg_index++;
                }

                $accordion_items = [];
                $item_index = 0;

                foreach ($items as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $accordion_items[] = matrix_migrate_restyle_accordion_item(
                        (string) ($item['title'] ?? ''),
                        (string) ($item['content'] ?? ''),
                        $item_index === 0
                    );
                    $item_index++;
                }

                if ($accordion_items !== []) {
                    $rows[] = [
                        'acf_fc_layout' => 'content_accordion',
                        'layout_style' => 'default',
                        'section_background' => $backgrounds[$bg_index % 2],
                        'panel_background' => '#FFFFFF',
                        'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
                        'items' => $accordion_items,
                        'padding_settings' => $accordion_padding,
                    ];
                    $bg_index++;
                }

                continue;
            }

            if ($type === 'gallery') {
                $slides = [];
                $images = is_array($block['images'] ?? null) ? $block['images'] : [];

                foreach ($images as $image_path) {
                    $attachment_id = matrix_migrate_attachment_id_for_source_path((string) $image_path);

                    if ($attachment_id < 1) {
                        continue;
                    }

                    $slides[] = [
                        'image' => $attachment_id,
                        'has_video' => 0,
                        'video_source_type' => 'youtube_vimeo',
                        'video_embed_url' => '',
                        'local_video_file' => '',
                        'video_link' => '',
                    ];
                }

                if ($slides !== []) {
                    $rows[] = [
                        'acf_fc_layout' => 'story_slider',
                        'show_heading' => 1,
                        'heading_tag' => 'h2',
                        'heading_text' => 'Partnership highlights',
                        'intro_text' => '',
                        'slides' => $slides,
                        'section_background' => '#FFFFFF',
                        'padding_settings' => $section_padding,
                    ];
                    $bg_index++;
                }

                continue;
            }

            if ($type === 'pods') {
                $cards = [];
                $pod_items = is_array($block['items'] ?? null) ? $block['items'] : [];

                foreach ($pod_items as $pod) {
                    if (! is_array($pod) || ($pod['title'] ?? '') === '') {
                        continue;
                    }

                    $url = trim((string) ($pod['url'] ?? ''));

                    if ($url === '' || $url === '#') {
                        continue;
                    }

                    $cards[] = [
                        'image' => max(0, (int) ($pod['image_id'] ?? 0)),
                        'title' => (string) $pod['title'],
                        'description' => (string) ($pod['description'] ?? ''),
                        'link' => [
                            'title' => (string) $pod['title'],
                            'url' => $url,
                            'target' => '',
                        ],
                    ];
                }

                if (matrix_normalize_related_cards($cards) !== []) {
                    $rows[] = [
                        'acf_fc_layout' => 'related_cards',
                        'heading_tag' => 'h2',
                        'heading' => trim((string) ($block['heading'] ?? 'Related')) ?: 'Related',
                        'intro_text' => '',
                        'cards' => $cards,
                        'background_color' => $backgrounds[$bg_index % 2],
                        'columns' => count($cards) === 2 ? '2' : '3',
                        'padding_settings' => $section_padding,
                    ];
                    $bg_index++;
                }
            }
        }

        return $rows;
    }
}

if (! function_exists('matrix_migrate_restyle_page')) {
    function matrix_migrate_restyle_page(int $post_id, bool $force = false): bool
    {
        if ($post_id < 1 || get_post_type($post_id) !== 'page') {
            return false;
        }

        if (! $force && get_post_meta($post_id, '_matrix_migrate_restyle_skip', true) === '1') {
            return false;
        }

        $old_path = trim((string) get_post_meta($post_id, '_matrix_migrate_old_path', true), '/');

        if ($old_path === '') {
            return false;
        }

        $file = matrix_migrate_html_file_for_old_path($old_path);

        if ($file === '' || ! is_readable($file)) {
            return false;
        }

        $html = (string) file_get_contents($file);
        $structured = matrix_migrate_extract_structured_page($html, $old_path);

        if ($structured === null) {
            return false;
        }

        $hero_image_id = 0;
        $og_image = (string) ($structured['og_image'] ?? '');

        if ($og_image !== '') {
            $hero_image_id = matrix_migrate_attachment_id_for_source_path($og_image);
        }

        $flexi_rows = matrix_migrate_build_structured_flexi_rows($structured, $hero_image_id);

        update_field('hero_content_blocks', [], $post_id);
        update_field('flexible_content_blocks', $flexi_rows, $post_id);
        update_post_meta($post_id, '_matrix_migrate_restyled', '1');

        return true;
    }
}

if (! function_exists('matrix_migrate_restyle_post')) {
    function matrix_migrate_restyle_post(int $post_id): bool
    {
        if ($post_id < 1 || get_post_type($post_id) !== 'post') {
            return false;
        }

        $old_path = trim((string) get_post_meta($post_id, '_matrix_migrate_old_path', true), '/');

        if ($old_path === '') {
            return false;
        }

        $file = matrix_migrate_html_file_for_old_path($old_path);

        if ($file === '' || ! is_readable($file)) {
            return false;
        }

        $html = (string) file_get_contents($file);
        $parsed = matrix_migrate_extract_parsed_page($html, $old_path);

        if ($parsed === null) {
            return false;
        }

        $content = (string) ($parsed['body_html'] ?? '');

        if (function_exists('matrix_format_migrated_post_content')) {
            $content = matrix_format_migrated_post_content($content, $post_id);
        } else {
            $content = matrix_migrate_clean_rte_html($content);
        }

        wp_update_post([
            'ID' => $post_id,
            'post_content' => $content,
            'post_excerpt' => (string) ($parsed['meta_description'] ?? get_post_field('post_excerpt', $post_id)),
        ]);

        $og_image = (string) ($parsed['og_image'] ?? '');

        if ($og_image !== '') {
            $attachment_id = matrix_migrate_attachment_id_for_source_path($og_image);

            if ($attachment_id > 0) {
                set_post_thumbnail($post_id, $attachment_id);
            }
        }

        update_post_meta($post_id, '_matrix_migrate_restyled', '1');

        return true;
    }
}

if (! function_exists('matrix_migrate_post_id_from_public_url')) {
    function matrix_migrate_post_id_from_public_url(string $url): int
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($path === '') {
            return 0;
        }

        $page = get_page_by_path($path, OBJECT, 'page');

        if ($page instanceof WP_Post) {
            return (int) $page->ID;
        }

        $posts = get_posts([
            'post_type' => 'post',
            'name' => basename($path),
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
        ]);

        return $posts !== [] ? (int) $posts[0] : 0;
    }
}
