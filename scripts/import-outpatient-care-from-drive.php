<?php

/**
 * Import Outpatient Care - Dean Clinics (page 222) from the local Drive copy.
 *
 * Source:
 *   old/content/SPMHS-Content-Gathering-Library 2/02-Page-content/What We Offer/Outpatient care/Outpatient care page.docx
 *
 * Run:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-outpatient-care-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/outpatient-clinics-seed.php';
require_once __DIR__ . '/lib/orlaith-page-helpers.php';

$theme = get_template_directory();
$docx = $theme . '/old/content/SPMHS-Content-Gathering-Library 2/02-Page-content/What We Offer/Outpatient care/Outpatient care page.docx';

$post_id = (int) (get_page_by_path('what-we-offer/outpatient-care-dean-clinics')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find Outpatient Care page.');
    }

    exit(1);
}

if (! is_readable($docx)) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Missing docx: ' . $docx);
    }

    exit(1);
}

if (! function_exists('matrix_outpatient_import_normalize_text')) {
    function matrix_outpatient_import_normalize_text(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\x{00A0}\x{202F}\x{2007}\x{2009}]/u', ' ', $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}

if (! function_exists('matrix_outpatient_import_docx_html')) {
    function matrix_outpatient_import_docx_html(string $path): string
    {
        $tmp = sys_get_temp_dir() . '/outpatient-care-' . md5($path) . '.html';
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

if (! function_exists('matrix_outpatient_import_rewrite_links')) {
    function matrix_outpatient_import_rewrite_links(string $html): string
    {
        $map = [
            'care-treatment/outpatient-clinics/dean-clinic-cork' => 'outpatient-clinics/dean-clinic-cork',
            'care-treatment/outpatient-clinics/dean-clinic-galway' => 'outpatient-clinics/dean-clinic-galway',
            'care-treatment/outpatient-clinics/dean-clinic-lucan' => 'outpatient-clinics/dean-clinic-lucan',
            'care-treatment/outpatient-clinics/dean-clinic-st-patrick-s' => 'outpatient-clinics/dean-clinic-st-patricks',
            'care-treatment/outpatient-clinics/about-the-dean-clinics' => 'outpatient-clinics/about-the-dean-clinics',
        ];

        $html = preg_replace_callback(
            '#https?://(?:www\.)?stpatricks\.ie/([^"\s<]+)#i',
            static function (array $m) use ($map): string {
                $path = trim($m[1], '/');
                if (isset($map[$path])) {
                    return esc_url(matrix_orlaith_permalink($map[$path]));
                }

                return esc_url(home_url('/' . $path . '/'));
            },
            $html
        );

        $replacements = [
            'Learn more about what to expect as an outpatient here. [Link to relevant page].' =>
                'Learn more about what to expect as an outpatient on our '
                . matrix_orlaith_a('service-users-and-visitors/attending-a-dean-clinic', 'attending a Dean Clinic')
                . ' page.',
            'Find out more about referrals to the Dean Clinic here.' =>
                'Find out more about '
                . matrix_orlaith_a('healthcare-professionals/refer-for-outpatient-care', 'referrals to the Dean Clinic')
                . '.',
            'Find more detailed information on attending appointments, and what to expect on the day here.' =>
                'Find more detailed information on '
                . matrix_orlaith_a('service-users-and-visitors/attending-a-dean-clinic', 'attending appointments and what to expect on the day')
                . '.',
        ];

        foreach ($replacements as $search => $replace) {
            $html = str_replace($search, $replace, $html);
        }

        return $html;
    }
}

if (! function_exists('matrix_outpatient_import_parse_sections')) {
    /**
     * @return array{
     *   meta_title:string,
     *   meta_description:string,
     *   h1:string,
     *   intro_html:string,
     *   sections:array<string,string>,
     *   faqs:array<string,string>
     * }
     */
    function matrix_outpatient_import_parse_sections(string $html): array
    {
        $meta_title = '';
        $meta_description = '';
        if (preg_match('#<strong>Meta title:</strong>\s*(.*?)</p>#is', $html, $m)) {
            $meta_title = matrix_outpatient_import_normalize_text(wp_strip_all_tags($m[1]));
        }
        if (preg_match('#<strong>Meta description:</strong>\s*(.*?)</p>#is', $html, $m)) {
            $meta_description = matrix_outpatient_import_normalize_text(wp_strip_all_tags($m[1]));
        }

        $chunks = preg_split('/(?=<p\b)/i', $html) ?: [];
        $h1 = '';
        $intro_parts = [];
        $sections = [];
        $faqs = [];
        $current_heading = '';
        $current_html = '';
        $in_faq = false;
        $current_question = '';
        $current_answer_parts = [];

        $flush_section = static function () use (&$sections, &$current_heading, &$current_html): void {
            if ($current_heading === '') {
                return;
            }
            $sections[$current_heading] = trim($current_html);
            $current_heading = '';
            $current_html = '';
        };

        $flush_faq = static function () use (&$faqs, &$current_question, &$current_answer_parts): void {
            if ($current_question === '') {
                return;
            }
            $faqs[$current_question] = trim(implode('', $current_answer_parts));
            $current_question = '';
            $current_answer_parts = [];
        };

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }

            if (preg_match('#<strong>Meta (title|description):#i', $chunk)) {
                continue;
            }
            if (preg_match('#figma\.com#i', $chunk)) {
                continue;
            }

            if (preg_match('#<strong>H1:\s*(.*?)</strong>#is', $chunk, $m)) {
                $h1 = matrix_outpatient_import_normalize_text(wp_strip_all_tags($m[1]));
                $rest = preg_replace('#<strong>H1:.*?</strong>#is', '', $chunk);
                $rest = trim((string) $rest);
                if ($rest !== '') {
                    $intro_parts[] = $rest;
                }
                continue;
            }

            if (preg_match('#<strong>H2:\s*(.*?)</strong>#is', $chunk, $m)) {
                $title = matrix_outpatient_import_normalize_text(wp_strip_all_tags($m[1]));

                if (strcasecmp($title, 'FAQs') === 0) {
                    $flush_section();
                    $in_faq = true;
                    continue;
                }

                $flush_section();
                $flush_faq();
                $in_faq = false;
                $current_heading = $title;
                $rest = preg_replace('#<strong>H2:.*?</strong>#is', '', $chunk);
                $rest = trim((string) $rest);
                $current_html = $rest !== '' ? $rest : '';
                continue;
            }

            if ($in_faq && preg_match('#<strong>(.*?)</strong>#is', $chunk, $m)) {
                $question = matrix_outpatient_import_normalize_text(wp_strip_all_tags($m[1]));
                if ($question !== '' && str_ends_with($question, '?')) {
                    $flush_faq();
                    $current_question = $question;
                    $rest = preg_replace('#<strong>.*?</strong>#is', '', $chunk, 1);
                    $rest = trim((string) $rest);
                    if ($rest !== '') {
                        $current_answer_parts[] = $rest;
                    }
                    continue;
                }
            }

            if ($in_faq && $current_question !== '') {
                $current_answer_parts[] = $chunk;
                continue;
            }

            if ($h1 !== '' && $current_heading === '' && ! $in_faq) {
                $intro_parts[] = $chunk;
                continue;
            }

            if ($current_heading !== '') {
                $current_html .= $chunk;
            }
        }

        $flush_section();
        $flush_faq();

        return [
            'meta_title' => $meta_title,
            'meta_description' => $meta_description,
            'h1' => $h1,
            'intro_html' => matrix_outpatient_import_rewrite_links(trim(implode('', $intro_parts))),
            'sections' => array_map('matrix_outpatient_import_rewrite_links', $sections),
            'faqs' => array_map('matrix_outpatient_import_rewrite_links', $faqs),
        ];
    }
}

if (! function_exists('matrix_outpatient_import_find_block')) {
    /**
     * @param array<int, array<string, mixed>> $rows
     */
    function matrix_outpatient_import_find_block(array $rows, string $layout): ?array
    {
        foreach ($rows as $row) {
            if (is_array($row) && ($row['acf_fc_layout'] ?? '') === $layout) {
                return $row;
            }
        }

        return null;
    }
}

$html = matrix_outpatient_import_docx_html($docx);

if ($html === '') {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not convert Outpatient care page.docx to HTML (is pandoc installed?).');
    }

    exit(1);
}

$parsed = matrix_outpatient_import_parse_sections($html);
$existing = get_field('flexible_content_blocks', $post_id, true);

if (! is_array($existing) || $existing === []) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Page has no flexible content blocks to update.');
    }

    exit(1);
}

$hero = matrix_outpatient_import_find_block($existing, 'hero_with_breadcrumbs') ?? [];
$locations = matrix_outpatient_import_find_block($existing, 'locations_grid') ?? [];
$testimonials = matrix_outpatient_import_find_block($existing, 'testimonials') ?? [];
$video = matrix_outpatient_import_find_block($existing, 'video_showcase') ?? [];
$content_cta = matrix_outpatient_import_find_block($existing, 'content_cta') ?? [];

if ($parsed['h1'] !== '') {
    $hero['heading'] = $parsed['h1'];
    $hero['current_crumb_label'] = 'Outpatient Care - Dean Clinics';
}
if ($parsed['intro_html'] !== '') {
    $hero['content'] = $parsed['intro_html'];
}

if (($parsed['sections']['Where you will receive care'] ?? '') !== '') {
    $locations['heading'] = '';
}

$content_after_locations = [];
$where_body = $parsed['sections']['Where you will receive care'] ?? '';
$other_sections = ['What to expect', 'Who we support', 'How to access'];

foreach ($other_sections as $heading) {
    $body = $parsed['sections'][$heading] ?? '';
    if ($body === '') {
        continue;
    }

    $content_after_locations[] = matrix_seed_outpatient_content_block($heading, $body, 'white');
}

$faq_items = [];
$first = true;
foreach ($parsed['faqs'] as $title => $answer) {
    $faq_items[] = matrix_seed_outpatient_accordion_item($title, $answer, $first);
    $first = false;
}

$faq_intro = '<p>Click on the questions you\'re interested in below to see the answers.</p>';
$faq_content = matrix_seed_outpatient_content_block('Frequently Asked Questions', $faq_intro, 'white');

$accordion = [
    'acf_fc_layout' => 'content_accordion',
    'layout_style' => 'default',
    'heading' => '',
    'heading_tag' => 'h2',
    'vertical_padding' => 'default',
    'section_background' => '#FBFAF7',
    'panel_background' => '#FFFFFF',
    'open_panel_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
    'items' => $faq_items,
];

$where_content = $where_body !== ''
    ? matrix_seed_outpatient_content_block('Where you will receive care', $where_body, 'white')
    : null;

$rows = array_values(array_filter([
    $hero !== [] ? $hero : null,
    $where_content,
    $locations !== [] ? $locations : null,
    ...$content_after_locations,
    $testimonials !== [] ? $testimonials : null,
    $video !== [] ? $video : null,
    $content_cta !== [] ? $content_cta : null,
    $faq_items !== [] ? $faq_content : null,
    $faq_items !== [] ? $accordion : null,
]));

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $rows, $post_id);

if ($parsed['meta_title'] !== '' && $parsed['meta_description'] !== '') {
    matrix_orlaith_set_seo($post_id, $parsed['meta_title'], $parsed['meta_description']);
}

update_post_meta($post_id, '_matrix_outpatient_drive_import', gmdate('c'));

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Imported Outpatient Care page (%d) from Drive docx: %d blocks, %d FAQs, hero "%s".',
        $post_id,
        count($rows),
        count($faq_items),
        $parsed['h1']
    ));
}
