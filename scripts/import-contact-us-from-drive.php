<?php

/**
 * Import Contact us (page 273) from the local Drive copy.
 *
 * Source:
 *   old/content/SPMHS-Content-Gathering-Library 2/02-Page-content/Contact Us/Contact Us/Contact us.docx
 *
 * Run:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-contact-us-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/orlaith-page-helpers.php';

$theme = get_template_directory();
$docx = $theme . '/old/content/SPMHS-Content-Gathering-Library 2/02-Page-content/Contact Us/Contact Us/Contact us.docx';
$post_id = (int) (get_page_by_path('contact-us')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find Contact us page.');
    }

    exit(1);
}

if (! is_readable($docx)) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Missing docx: ' . $docx);
    }

    exit(1);
}

if (! function_exists('matrix_contact_import_normalize_text')) {
    function matrix_contact_import_normalize_text(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\x{00A0}\x{202F}\x{2007}\x{2009}]/u', ' ', $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}

if (! function_exists('matrix_contact_import_docx_html')) {
    function matrix_contact_import_docx_html(string $path): string
    {
        $tmp = sys_get_temp_dir() . '/contact-us-' . md5($path) . '.html';
        $cmd = 'pandoc ' . escapeshellarg($path) . ' -t html --wrap=none -o ' . escapeshellarg($tmp);
        exec($cmd, $out, $code);

        if ($code !== 0 || ! is_readable($tmp)) {
            return '';
        }

        $html = (string) file_get_contents($tmp);
        @unlink($tmp);

        $html = preg_replace('/[\x{00A0}\x{202F}\x{2007}\x{2009}]/u', ' ', $html);
        $html = preg_replace('#<li>\s*<p>(.*?)</p>\s*</li>#is', '<li>$1</li>', $html);
        $html = preg_replace('#</?(?:mark|u)>#i', '', $html);

        return is_string($html) ? $html : '';
    }
}

if (! function_exists('matrix_contact_import_location_id')) {
    function matrix_contact_import_location_id(string $slug): int
    {
        $post = get_page_by_path($slug, OBJECT, 'locations');

        return $post instanceof WP_Post ? (int) $post->ID : 0;
    }
}

if (! function_exists('matrix_contact_import_manual_item')) {
    /**
     * @param array<int, string> $bullet_items
     * @return array<string, mixed>
     */
    function matrix_contact_import_manual_item(
        string $title,
        string $phone = '',
        string $email = '',
        array $bullet_items = [],
        bool $starts_open = false
    ): array {
        $item = [
            'item_source' => 'manual',
            'title' => $title,
            'starts_open' => $starts_open ? 1 : 0,
            'bullet_items' => [],
            'phone' => $phone,
            'email' => $email,
            'opening_hours' => [],
            'location' => '',
        ];

        foreach ($bullet_items as $label) {
            $item['bullet_items'][] = ['label' => $label];
        }

        return $item;
    }
}

if (! function_exists('matrix_contact_import_location_item')) {
    /**
     * @return array<string, mixed>
     */
    function matrix_contact_import_location_item(string $slug, string $title, string $phone = ''): array
    {
        return [
            'item_source' => 'location',
            'location' => matrix_contact_import_location_id($slug),
            'title' => $title,
            'starts_open' => 0,
            'bullet_items' => [],
            'phone' => $phone,
            'email' => '',
            'opening_hours' => [],
        ];
    }
}

if (! function_exists('matrix_contact_import_parse_list_item')) {
    /**
     * @return array{title:string,phone:string,email:string}
     */
    function matrix_contact_import_parse_list_item(string $html): array
    {
        $plain = str_replace(['<br />', '<br/>', '<br>'], "\n", $html);
        $plain = matrix_contact_import_normalize_text(wp_strip_all_tags($plain));

        $title = $plain;
        $phone = '';
        $email = '';

        if (preg_match('/\bPhone:\s*/i', $plain)) {
            $parts = preg_split('/\bPhone:\s*/i', $plain, 2) ?: [];
            $title = trim((string) ($parts[0] ?? ''));
            $rest = trim((string) ($parts[1] ?? ''));

            if (preg_match('/\bEmail:\s*/i', $rest)) {
                $contact_parts = preg_split('/\bEmail:\s*/i', $rest, 2) ?: [];
                $phone = trim((string) ($contact_parts[0] ?? ''));
                $email = trim((string) ($contact_parts[1] ?? ''));
            } else {
                $phone = $rest;
            }
        } elseif (preg_match('/\bEmail:\s*/i', $plain)) {
            $parts = preg_split('/\bEmail:\s*/i', $plain, 2) ?: [];
            $title = trim((string) ($parts[0] ?? ''));
            $email = trim((string) ($parts[1] ?? ''));
        }

        return [
            'title' => $title,
            'phone' => $phone,
            'email' => $email,
        ];
    }
}

if (! function_exists('matrix_contact_import_location_slug_for_title')) {
    function matrix_contact_import_location_slug_for_title(string $title): string
    {
        $map = [
            "St Patrick's University Hospital, Dublin 8" => 'st-patricks-university-hospital',
            "St Patrick's Hospital, Lucan" => 'st-patricks-hospital-lucan',
            'Willow Grove Adolescent Unit' => 'willow-grove-adolescent-unit',
            'Dean Clinic, Dublin 8' => 'dean-clinic-st-patricks',
            'Dean Clinic, Cork' => 'dean-clinic-cork',
            'Dean Clinic, Galway' => 'dean-clinic-galway',
            'Dean Clinic, Lucan' => 'dean-clinic-lucan',
        ];

        return $map[$title] ?? '';
    }
}

if (! function_exists('matrix_contact_import_build_item')) {
    /**
     * @param array{title:string,phone:string,email:string} $parsed
     * @return array<string, mixed>
     */
    function matrix_contact_import_build_item(array $parsed, bool $starts_open = false): array
    {
        $slug = matrix_contact_import_location_slug_for_title($parsed['title']);

        if ($slug !== '' && matrix_contact_import_location_id($slug) > 0) {
            return matrix_contact_import_location_item($slug, $parsed['title'], $parsed['phone']);
        }

        return matrix_contact_import_manual_item(
            $parsed['title'],
            $parsed['phone'],
            $parsed['email'],
            [],
            $starts_open
        );
    }
}

if (! function_exists('matrix_contact_import_parse_doc')) {
    /**
     * @return array{
     *   meta_title:string,
     *   meta_description:string,
     *   heading:string,
     *   intro_html:string,
     *   column_1:array<int, array<string,mixed>>,
     *   column_2:array<int, array<string,mixed>>,
     *   directions_intro:string
     * }
     */
    function matrix_contact_import_parse_doc(string $html): array
    {
        $meta_title = '';
        $meta_description = '';
        if (preg_match('#<strong>Meta title:</strong>\s*(.*?)</p>#is', $html, $m)) {
            $meta_title = matrix_contact_import_normalize_text(wp_strip_all_tags($m[1]));
        }
        if (preg_match('#<strong>Meta description:</strong>\s*(.*?)</p>#is', $html, $m)) {
            $meta_description = matrix_contact_import_normalize_text(wp_strip_all_tags($m[1]));
        }

        $heading = 'Contact us';
        if (preg_match('#<strong>H1:\s*(.*?)</strong>#is', $html, $m)) {
            $heading = matrix_contact_import_normalize_text(wp_strip_all_tags($m[1]));
        }

        $intro_html = '';
        if (preg_match('#<strong>H1:.*?</strong>\s*(.*?)\s*<strong>#is', $html, $m)) {
            $intro = trim($m[1]);
            $intro = preg_replace('#<br\s*/?>#i', ' ', $intro);
            $intro = matrix_contact_import_normalize_text(wp_strip_all_tags((string) $intro));
            if ($intro !== '') {
                $intro_html = '<p>' . esc_html($intro) . '</p>';
            }
        }

        $sections = [];
        if (preg_match_all('#<strong>(?:<br\s*/?>\s*)*H2:\s*(.*?)</strong>(.*?)(?=<strong>(?:<br\s*/?>\s*)*H2:|$)#is', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $section_title = matrix_contact_import_normalize_text(wp_strip_all_tags($match[1]));
                $section_html = (string) $match[2];
                $items = [];

                if (preg_match_all('#<li>(.*?)</li>#is', $section_html, $li_matches)) {
                    foreach ($li_matches[1] as $li_html) {
                        $parsed = matrix_contact_import_parse_list_item($li_html);
                        if ($parsed['title'] !== '') {
                            $items[] = $parsed;
                        }
                    }
                }

                if ($section_title !== '') {
                    $sections[$section_title] = [
                        'items' => $items,
                        'body' => trim(wp_strip_all_tags($section_html)),
                    ];
                }
            }
        }

        if (! isset($sections['Directions and locations']) && preg_match(
            '#<strong>H2:</strong>\s*Directions and locations.*?<p>(.*?)</p>#is',
            $html,
            $directions_match
        )) {
            $sections['Directions and locations'] = [
                'items' => [],
                'body' => matrix_contact_import_normalize_text(wp_strip_all_tags((string) $directions_match[1])),
            ];
        }

        $column_1 = [];
        $column_2 = [];
        $first = true;

        foreach ($sections as $section_title => $section) {
            if (strcasecmp($section_title, 'Directions and locations') === 0) {
                continue;
            }

            foreach ($section['items'] as $parsed) {
                $item = matrix_contact_import_build_item($parsed, $first);
                $first = false;

                if (in_array($section_title, ['General enquiries', 'Hospitals and services'], true)) {
                    $column_1[] = $item;
                    continue;
                }

                $column_2[] = $item;
            }
        }

        $directions_intro = '';
        $directions = $sections['Directions and locations'] ?? null;
        if (is_array($directions)) {
            $body = trim((string) ($directions['body'] ?? ''));
            $paragraphs = [];
            if (preg_match('/^(Whether.+?\.)\s*(You can find.+?\.)\s*$/is', $body, $m)) {
                $paragraphs = [trim($m[1]), trim($m[2])];
            } else {
                $paragraphs = array_values(array_filter(array_map('trim', preg_split('/\n{2,}/', $body) ?: [])));
                if ($paragraphs === [] && $body !== '') {
                    $paragraphs = [$body];
                }
            }
            $parts = [];
            foreach ($paragraphs as $paragraph) {
                $parts[] = '<p>' . esc_html($paragraph) . '</p>';
            }
            $directions_intro = implode('', $parts);
        }

        if ($directions_intro === '' && preg_match(
            '#Directions and locations.*?((?:Whether you are visiting us.+?)(?:You can find information.+?links below\.))#is',
            wp_strip_all_tags($html),
            $directions_match
        )) {
            $body = matrix_contact_import_normalize_text((string) $directions_match[1]);
            if (preg_match('/^(Whether.+?\.)\s*(You can find.+?\.)\s*$/is', $body, $m)) {
                $directions_intro = '<p>' . esc_html(trim($m[1])) . '</p><p>' . esc_html(trim($m[2])) . '</p>';
            }
        }

        return [
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'heading' => $heading,
            'intro_html' => $intro_html,
            'column_1' => $column_1,
            'column_2' => $column_2,
            'directions_intro' => $directions_intro,
        ];
    }
}

$html = matrix_contact_import_docx_html($docx);

if ($html === '') {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not convert Contact us.docx to HTML (is pandoc installed?).');
    }

    exit(1);
}

$parsed = matrix_contact_import_parse_doc($html);
$rows = get_field('flexible_content_blocks', $post_id, true);

if (! is_array($rows) || $rows === []) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Contact us page has no flexible content blocks to update.');
    }

    exit(1);
}

foreach ($rows as &$row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '');

    if ($layout === 'contact_directory') {
        if ($parsed['heading'] !== '') {
            $row['heading'] = $parsed['heading'];
        }
        if ($parsed['intro_html'] !== '') {
            $row['intro_text'] = $parsed['intro_html'];
        }
        if ($parsed['column_1'] !== [] || $parsed['column_2'] !== []) {
            $row['columns'] = array_values(array_filter([
                $parsed['column_1'] !== [] ? ['items' => $parsed['column_1']] : null,
                $parsed['column_2'] !== [] ? ['items' => $parsed['column_2']] : null,
            ]));
        }
    }

    if ($layout === 'locations_map') {
        if ($parsed['directions_intro'] !== '') {
            $row['intro_text'] = $parsed['directions_intro'];
        }
        $row['heading'] = 'Directions and locations';
    }
}
unset($row);

update_field('flexible_content_blocks', $rows, $post_id);

if ($parsed['meta_title'] !== '' && $parsed['meta_description'] !== '') {
    matrix_orlaith_set_seo($post_id, $parsed['meta_title'], $parsed['meta_description']);
}

update_post_meta($post_id, '_matrix_contact_us_drive_import', gmdate('c'));

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Imported Contact us page (%d): %d + %d directory items, heading "%s".',
        $post_id,
        count($parsed['column_1']),
        count($parsed['column_2']),
        $parsed['heading']
    ));
}
