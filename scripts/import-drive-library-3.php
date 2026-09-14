<?php

/**
 * Import client Drive copy from SPMHS-Content-Gathering-Library 3.
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-drive-library-3.php
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-drive-library-3.php dry-run
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once __DIR__ . '/lib/page-seed-conventions.php';
require_once __DIR__ . '/lib/orlaith-page-helpers.php';
require_once __DIR__ . '/lib/orlaith-docx.php';

$migrate = get_template_directory() . '/inc/migrate-functions.php';
if (is_readable($migrate)) {
    require_once $migrate;
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$dry_run = in_array('dry-run', array_map('strval', $GLOBALS['argv'] ?? []), true);
$library = get_template_directory() . '/old/content/SPMHS-Content-Gathering-Library 3';

$log = static function (string $message): void {
    if (class_exists('WP_CLI')) {
        WP_CLI::log($message);
    }
};
$warn = static function (string $message): void {
    if (class_exists('WP_CLI')) {
        WP_CLI::warning($message);
    }
};

if (! is_dir($library)) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Missing library folder: ' . $library);
    }
    exit(1);
}

if (! function_exists('matrix_drive3_localise_html')) {
    function matrix_drive3_localise_html(string $html): string
    {
        $html = str_replace(
            [
                'https://st-patricks.s1.matrix-test.com',
                'http://st-patricks.s1.matrix-test.com',
            ],
            untrailingslashit(home_url()),
            $html
        );

        if (function_exists('matrix_migrate_rewrite_internal_urls')) {
            $html = matrix_migrate_rewrite_internal_urls($html);
        }

        return $html;
    }
}

if (! function_exists('matrix_drive3_plain')) {
    function matrix_drive3_plain(string $html): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', wp_strip_all_tags($html)));
    }
}

if (! function_exists('matrix_drive3_find_docx')) {
    function matrix_drive3_find_docx(string $folder): string
    {
        if (! is_dir($folder)) {
            $parent = dirname($folder);
            $needle = basename($folder);
            if (is_dir($parent)) {
                foreach (scandir($parent) ?: [] as $entry) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }
                    $normalized = str_replace(["\u{2019}", "\u{2018}", "'"], "'", $entry);
                    $want = str_replace(["\u{2019}", "\u{2018}", "'"], "'", $needle);
                    if (strcasecmp($normalized, $want) === 0) {
                        $folder = $parent . '/' . $entry;
                        break;
                    }
                }
            }
        }
        if (! is_dir($folder)) {
            return '';
        }
        $files = glob($folder . '/*.docx') ?: [];
        $preferred = [];
        $fallback = [];
        foreach ($files as $file) {
            $base = basename($file);
            if (str_starts_with($base, '~$')) {
                continue;
            }
            if (preg_match('/layout|comments on migrated|already in media library/i', $base)) {
                $fallback[] = $file;
                continue;
            }
            $preferred[] = $file;
        }

        return $preferred[0] ?? $fallback[0] ?? '';
    }
}

if (! function_exists('matrix_drive3_extract_meta')) {
    /**
     * @return array{title:string,description:string}
     */
    function matrix_drive3_extract_meta(string $docx_path): array
    {
        $title = '';
        $description = '';
        $tmp = sys_get_temp_dir() . '/drive3-meta-' . md5($docx_path) . '.txt';
        exec('pandoc ' . escapeshellarg($docx_path) . ' -t plain --wrap=none -o ' . escapeshellarg($tmp));
        if (is_readable($tmp)) {
            $plain = (string) file_get_contents($tmp);
            @unlink($tmp);
            if (preg_match('/Meta\s*title:\s*(.+)/i', $plain, $m)) {
                $title = trim($m[1]);
            }
            if (preg_match('/Meta\s*description:\s*(.+)/i', $plain, $m)) {
                $description = trim($m[1]);
            }
        }

        return ['title' => $title, 'description' => $description];
    }
}

if (! function_exists('matrix_drive3_attachment_by_filename')) {
    function matrix_drive3_attachment_by_filename(string $filename): int
    {
        global $wpdb;
        $like = '%' . $wpdb->esc_like(basename($filename)) . '%';
        $id = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
                $like
            )
        );

        return $id;
    }
}

if (! function_exists('matrix_drive3_flexi_from_parse')) {
    /**
     * @param array{h1:string,intro:string,blocks:array<int,array<string,mixed>>} $parsed
     * @return list<array<string,mixed>>
     */
    function matrix_drive3_flexi_from_parse(array $parsed, string $title, int $image_id): array
    {
        $heading = $parsed['h1'] !== '' ? $parsed['h1'] : $title;
        $rows = [matrix_orlaith_hero_row($heading, matrix_drive3_localise_html($parsed['intro']), $image_id)];
        $alt = false;
        foreach ($parsed['blocks'] as $block) {
            $type = (string) ($block['type'] ?? '');
            if ($type === 'content') {
                $rows[] = matrix_orlaith_content_row(
                    (string) ($block['heading'] ?? ''),
                    matrix_drive3_localise_html((string) ($block['html'] ?? '')),
                    $alt ? 'cream' : 'white'
                );
                $alt = ! $alt;
            } elseif ($type === 'accordion') {
                $items = [];
                foreach ($block['items'] ?? [] as $item) {
                    $item_title = trim((string) ($item['title'] ?? ''));
                    if ($item_title === '') {
                        continue;
                    }
                    $items[$item_title] = matrix_drive3_localise_html((string) ($item['html'] ?? ''));
                }
                if ($items !== []) {
                    $rows[] = matrix_orlaith_accordion_row($items);
                }
            } elseif ($type === 'videos') {
                $slides = [];
                foreach ($block['urls'] ?? [] as $url) {
                    $slides[] = ['url' => (string) $url];
                }
                if ($slides !== []) {
                    $rows[] = matrix_orlaith_video_row((string) ($block['heading'] ?? 'Videos'), '', $slides);
                }
            }
        }

        return $rows;
    }
}

if (! function_exists('matrix_drive3_html_from_parse')) {
    /**
     * @param array{h1:string,intro:string,blocks:array<int,array<string,mixed>>} $parsed
     */
    function matrix_drive3_html_from_parse(array $parsed): string
    {
        $html = matrix_drive3_localise_html($parsed['intro']);
        foreach ($parsed['blocks'] as $block) {
            $type = (string) ($block['type'] ?? '');
            if ($type === 'content') {
                $heading = trim((string) ($block['heading'] ?? ''));
                if ($heading !== '') {
                    $html .= '<h2>' . esc_html($heading) . '</h2>';
                }
                $html .= matrix_drive3_localise_html((string) ($block['html'] ?? ''));
            } elseif ($type === 'accordion') {
                foreach ($block['items'] ?? [] as $item) {
                    $item_title = trim((string) ($item['title'] ?? ''));
                    if ($item_title !== '') {
                        $html .= '<h2>' . esc_html($item_title) . '</h2>';
                    }
                    $html .= matrix_drive3_localise_html((string) ($item['html'] ?? ''));
                }
            }
        }

        return $html;
    }
}

if (! function_exists('matrix_drive3_summary_from_parse')) {
    /**
     * @param array{intro:string} $parsed
     */
    function matrix_drive3_summary_from_parse(array $parsed): string
    {
        $text = matrix_drive3_plain((string) ($parsed['intro'] ?? ''));
        if ($text === '') {
            return '';
        }
        if (strlen($text) > 280) {
            $cut = substr($text, 0, 277);
            $cut = preg_replace('/\s+\S*$/', '', $cut) ?? $cut;

            return rtrim($cut, '.,;:') . '…';
        }

        return $text;
    }
}

if (! function_exists('matrix_drive3_apply_page')) {
    /**
     * @param array{status?:string,builder?:bool,keep?:bool} $opts
     */
    function matrix_drive3_apply_page(int $post_id, string $docx, string $folder, array $opts = []): string
    {
        if ($post_id <= 0 || ! is_readable($docx)) {
            return 'missing';
        }
        $post = get_post($post_id);
        if (! $post instanceof WP_Post) {
            return 'missing';
        }
        $parsed = matrix_orlaith_parse_docx($docx);
        $image_path = matrix_orlaith_first_image($folder);
        $image_id = $image_path !== '' ? matrix_orlaith_import_image($image_path, $post->post_title, $post_id) : 0;
        if ($image_id <= 0) {
            $image_id = (int) get_post_thumbnail_id($post_id);
        }
        $rows = matrix_drive3_flexi_from_parse($parsed, $post->post_title, $image_id);

        if (! empty($opts['keep'])) {
            $existing = get_field('flexible_content_blocks', $post_id);
            if (is_array($existing)) {
                foreach ($existing as $row) {
                    $layout = (string) ($row['acf_fc_layout'] ?? $row['acf_fc_layout'] ?? '');
                    if (in_array($layout, [
                        'locations_grid',
                        'about_links_grid',
                        'programmes_therapies_archive',
                        'multidisciplinary_team_grid',
                        'stories',
                        'contact_form',
                    ], true)) {
                        $rows[] = $row;
                    }
                }
            }
        }

        matrix_orlaith_save_page($post_id, $rows, (bool) ($opts['builder'] ?? true), $image_id);
        wp_update_post([
            'ID' => $post_id,
            'post_status' => (string) ($opts['status'] ?? 'publish'),
        ]);
        $meta = matrix_drive3_extract_meta($docx);
        if ($meta['title'] !== '' || $meta['description'] !== '') {
            matrix_orlaith_set_seo($post_id, $meta['title'], $meta['description']);
        }
        update_post_meta($post_id, '_matrix_drive3_import', gmdate('c'));

        return 'updated';
    }
}

if (! function_exists('matrix_drive3_ensure_programme')) {
    /**
     * @param array{slug:string,title:string,type:string,care?:string,delivery?:string} $spec
     */
    function matrix_drive3_ensure_programme(array $spec): int
    {
        $found = get_posts([
            'post_type' => 'programmes_therapies',
            'name' => $spec['slug'],
            'post_status' => 'any',
            'posts_per_page' => 1,
        ]);
        if ($found !== []) {
            return (int) $found[0]->ID;
        }
        $by_title = get_posts([
            'post_type' => 'programmes_therapies',
            'title' => $spec['title'],
            'post_status' => 'any',
            'posts_per_page' => 1,
        ]);
        if ($by_title !== []) {
            return (int) $by_title[0]->ID;
        }
        $id = wp_insert_post([
            'post_type' => 'programmes_therapies',
            'post_status' => 'publish',
            'post_title' => $spec['title'],
            'post_name' => $spec['slug'],
            'post_content' => '',
        ], true);

        return is_wp_error($id) ? 0 : (int) $id;
    }
}

if (! function_exists('matrix_drive3_apply_programme')) {
    /**
     * @param array{slug:string,title:string,type:string,care?:string,delivery?:string} $spec
     */
    function matrix_drive3_apply_programme(array $spec, string $docx, string $folder): string
    {
        $post_id = matrix_drive3_ensure_programme($spec);
        if ($post_id <= 0 || ! is_readable($docx)) {
            return 'missing';
        }
        $parsed = matrix_orlaith_parse_docx($docx);
        $html = matrix_drive3_html_from_parse($parsed);
        $summary = matrix_drive3_summary_from_parse($parsed);
        $title = $parsed['h1'] !== '' ? $parsed['h1'] : $spec['title'];
        wp_update_post([
            'ID' => $post_id,
            'post_title' => $title,
            'post_content' => $html,
            'post_excerpt' => $summary,
            'post_status' => 'publish',
        ]);
        if ($summary !== '' && function_exists('update_field')) {
            update_field('listing_summary', $summary, $post_id);
        }
        $type_slug = $spec['type'] === 'therapy' ? 'therapies' : 'programmes';
        wp_set_object_terms($post_id, [$type_slug], 'programme_therapy_type', false);
        if (! empty($spec['care'])) {
            wp_set_object_terms($post_id, [$spec['care']], 'care_setting', false);
        }
        if (! empty($spec['delivery'])) {
            wp_set_object_terms($post_id, [$spec['delivery']], 'delivery_format', false);
        }
        $image_path = matrix_orlaith_first_image($folder);
        if ($image_path !== '') {
            $image_id = matrix_orlaith_import_image($image_path, $title, $post_id);
            if ($image_id > 0) {
                set_post_thumbnail($post_id, $image_id);
            }
        }
        $meta = matrix_drive3_extract_meta($docx);
        if ($meta['title'] !== '' || $meta['description'] !== '') {
            matrix_orlaith_set_seo($post_id, $meta['title'], $meta['description']);
        }
        update_post_meta($post_id, '_matrix_drive3_import', gmdate('c'));

        return 'updated';
    }
}

if (! function_exists('matrix_drive3_import_pdfs')) {
    /**
     * @return list<array{title:string,url:string,id:int}>
     */
    function matrix_drive3_import_pdfs(string $dir, int $parent_id): array
    {
        $out = [];
        foreach (glob($dir . '/*.pdf') ?: [] as $file) {
            $title = preg_replace('/\.pdf$/i', '', basename($file)) ?? basename($file);
            $title = str_replace('_', "'", $title);
            $key = 'drive-pdf:' . md5($file);
            $existing = get_posts([
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'posts_per_page' => 1,
                'meta_key' => '_matrix_drive_source',
                'meta_value' => $key,
                'fields' => 'ids',
            ]);
            if ($existing !== []) {
                $id = (int) $existing[0];
            } else {
                $tmp = wp_tempnam(basename($file));
                if (! $tmp || ! copy($file, $tmp)) {
                    continue;
                }
                $id = media_handle_sideload([
                    'name' => sanitize_file_name(basename($file)),
                    'tmp_name' => $tmp,
                ], $parent_id, $title);
                if (is_wp_error($id)) {
                    @unlink($tmp);
                    continue;
                }
                update_post_meta((int) $id, '_matrix_drive_source', $key);
            }
            $url = wp_get_attachment_url((int) $id);
            if (is_string($url) && $url !== '') {
                $out[] = ['title' => $title, 'url' => $url, 'id' => (int) $id];
            }
        }

        return $out;
    }
}

if (! function_exists('matrix_drive3_update_homepage')) {
    function matrix_drive3_update_homepage(): string
    {
        $post_id = (int) get_option('page_on_front');
        if ($post_id <= 0) {
            return 'missing';
        }

        $help = home_url('/service-users-and-visitors/frequently-asked-questions-faqs/');
        $refer = home_url('/healthcare-professionals/');
        $nursing = matrix_drive3_attachment_by_filename('nursing-staff-hero-645x440.png');
        if ($nursing <= 0) {
            $nursing = matrix_drive3_attachment_by_filename('nursing-staff-from-st-patricks.png');
        }
        $portrait = matrix_drive3_attachment_by_filename('Historic-entrance-porttait.png');
        $reform = matrix_drive3_attachment_by_filename('mental-health-reform-logo.png');
        if ($reform <= 0) {
            $reform = matrix_drive3_attachment_by_filename('mental-health-reform-partner.png');
        }
        if ($reform <= 0) {
            $reform = matrix_drive3_attachment_by_filename('Mental-Health-Reform.webp');
        }
        $ibec = matrix_drive3_attachment_by_filename('ibec-keepwell-logo.png');
        if ($ibec <= 0) {
            $ibec = matrix_drive3_attachment_by_filename('ibec-keepwell-partner.png');
        }
        if ($ibec <= 0) {
            $ibec = matrix_drive3_attachment_by_filename('ibec-keep-well-mark.png');
        }
        $mhc = matrix_drive3_attachment_by_filename('Card.svg');

        $slides = [
            [
                'heading' => "Ireland's largest independent, not-for-profit mental health service",
                'description' => "<p>Welcome to St Patrick's Mental Health Services, Ireland's largest independent not-for-profit mental healthcare provider.</p>",
            ],
            [
                'heading' => 'High quality mental healthcare',
                'description' => '<p>We provide multidisciplinary care and treatment for adults and adolescents experiencing mental health difficulties, including complex and enduring mental illness.</p>',
                'image' => $nursing,
            ],
            [
                'heading' => 'Recovery-focused services',
                'description' => '<p>We provide inpatient care; homecare services; outpatient care through our Dean Clinics; and a wide range of day programmes.</p>',
            ],
            [
                'heading' => 'Experienced mental health teams',
                'description' => '<p>Our teams work to provide compassionate care, promote mentally healthy living, advocate for human rights, and innovate through research and education.</p>',
            ],
        ];
        $offer = [
            [
                'title' => 'Inpatient care',
                'text' => "Specialist care in St Patrick's University Hospital and St Patrick's Hospital Lucan for adults, and in Willow Grove Adolescent Unit for young people aged 12 to 17.",
                'url' => home_url('/inpatient-care/'),
            ],
            [
                'title' => "St Patrick's at Home",
                'text' => 'Multidisciplinary mental healthcare delivered to adults and adolescents, offering all the elements of inpatient care but in the comfort of their own homes.',
                'url' => home_url('/what-we-offer/st-patricks-at-home/'),
            ],
            [
                'title' => 'Outpatient care',
                'text' => 'Community-based Dean Clinics across Ireland offering mental health assessment, care planning and one-to-one treatment to support adults and adolescents.',
                'url' => home_url('/what-we-offer/outpatient-care-dean-clinics/'),
            ],
            [
                'title' => 'Day programmes',
                'text' => 'Structured, group-based day programmes to support adults and adolescents at various stages of their mental health recovery.',
                'url' => home_url('/what-we-offer/day-programmes/'),
            ],
        ];
        $cards = [
            1 => ['Addiction and dual diagnosis', 'If you live with an addiction, you find it difficult to control or stop how much you use a substance or carry out a behavior.', home_url('/mental-health/addiction-dual-diagnosis/')],
            2 => ['Anxiety', 'Anxiety is a natural reaction to threat or danger, but severe symptoms may be part of an anxiety disorder.', home_url('/mental-health/anxiety/')],
            3 => ['Bipolar disorder', 'Bipolar disorder is a mood disorder which is marked by extreme changes in your mood, thinking and energy.', home_url('/mental-health/bipolar-disorder/')],
            4 => ['Depression', 'Depression is a common mood disorder which affects how you feel, think and act.', home_url('/mental-health/depression/')],
            5 => ['Eating disorders', 'An eating disorder is a mental health disorder where you use food and weight to cope with emotional distress.', home_url('/mental-health/eating-disorders/')],
            6 => ['Psychosis', 'Psychosis is a condition where you may have difficulty recognising what is real and what is not.', home_url('/mental-health/schizophrenia-psychosis/')],
        ];
        $counters = [
            ['value' => '100', 'suffix' => '%', 'title' => 'Compliance', 'description' => 'Full compliance across our three approved inpatient centres awarded by Mental Health Commission.'],
            ['value' => '3,152', 'suffix' => '', 'title' => 'People supported', 'description' => 'Through inpatient and homecare admissions last year.'],
            ['value' => '16,061', 'suffix' => '', 'title' => 'Outpatient appointments', 'description' => 'For people attending our Dean Clinics last year.'],
        ];
        $about = "<p>We were founded in 1746 through a gift left in Jonathan Swift's will. 280 years later, we remain committed to his charitable legacy, with all income reinvested in our services, education, research and advocacy.</p>";
        $news = "Stay up to date with the latest news, events, blogs and media from our team here in St Patrick's Mental Health Services, including mental health campaigns, clinical insights, research and education.";

        $hero = get_field('hero_content_blocks', $post_id);
        if (is_array($hero)) {
            foreach ($hero as &$hero_row) {
                if (($hero_row['acf_fc_layout'] ?? '') !== 'hero_slider' || ! is_array($hero_row['slides'] ?? null)) {
                    continue;
                }
                foreach ($hero_row['slides'] as $i => &$slide) {
                    if (! isset($slides[$i])) {
                        continue;
                    }
                    $slide['heading_text'] = $slides[$i]['heading'];
                    $slide['description'] = $slides[$i]['description'];
                    $slide['primary_button'] = ['title' => 'Looking for help?', 'url' => $help, 'target' => ''];
                    $slide['secondary_button'] = ['title' => 'Make a referral', 'url' => $refer, 'target' => ''];
                    if (! empty($slides[$i]['image'])) {
                        $slide['hero_image'] = (int) $slides[$i]['image'];
                    }
                }
                unset($slide);
            }
            unset($hero_row);
            update_field('hero_content_blocks', $hero, $post_id);
        }

        $rows = get_field('flexible_content_blocks', $post_id);
        if (! is_array($rows)) {
            return 'missing-flexi';
        }
        foreach ($rows as &$row) {
            $layout = (string) ($row['acf_fc_layout'] ?? '');
            if ($layout === 'partners') {
                // Homepage.docx: MHC + Mental Health Reform + KeepWell only.
                // Use resized partner derivatives when available so badges fit.
                $row['heading_text'] = 'Committed to quality care, human rights, and innovation';
                $partner_rows = [];
                if ($mhc > 0) {
                    $partner_rows[] = [
                        'logo' => $mhc,
                        'link' => ['title' => 'Mental Health Commission', 'url' => 'https://www.mhcirl.ie/', 'target' => '_blank'],
                    ];
                }
                if ($reform > 0) {
                    $partner_rows[] = [
                        'logo' => $reform,
                        'link' => ['title' => 'Mental Health Reform', 'url' => 'https://mentalhealthreform.ie', 'target' => '_blank'],
                    ];
                }
                if ($ibec > 0) {
                    $partner_rows[] = [
                        'logo' => $ibec,
                        'link' => ['title' => 'KeepWell Mark', 'url' => 'https://www.ibec.ie/employer-hub/corporate-wellness/the-keepwell-mark-public-page', 'target' => '_blank'],
                    ];
                }
                if ($partner_rows !== []) {
                    $row['partners'] = $partner_rows;
                }
            }
            if ($layout === 'what_we_offer' && is_array($row['services'] ?? null)) {
                foreach ($row['services'] as $i => &$service) {
                    if (! isset($offer[$i])) {
                        continue;
                    }
                    $service['service_title'] = $offer[$i]['title'];
                    $service['service_description'] = $offer[$i]['text'];
                    $service['service_link'] = ['title' => $offer[$i]['title'], 'url' => $offer[$i]['url'], 'target' => ''];
                }
                unset($service);
            }
            if ($layout === 'about_us') {
                $row['heading'] = 'About mental health';
                if ($portrait > 0) {
                    $row['main_image'] = $portrait;
                }
                $row['view_more_link'] = [
                    'title' => 'View more',
                    'url' => home_url('/service-users-and-visitors/about-mental-health/'),
                    'target' => '',
                ];
                foreach ($cards as $n => $card) {
                    $row['card_' . $n . '_title'] = $card[0];
                    $row['card_' . $n . '_text'] = $card[1];
                    $row['card_' . $n . '_link'] = ['title' => $card[0], 'url' => $card[2], 'target' => ''];
                }
            }
            if ($layout === 'counters') {
                $row['counter_items'] = $counters;
            }
            if ($layout === 'content' && ($row['heading'] ?? '') === 'About us') {
                $row['content'] = $about;
            }
            if ($layout === 'content_two') {
                $row['description'] = $news;
            }
        }
        unset($row);
        update_field('flexible_content_blocks', $rows, $post_id);
        update_post_meta($post_id, '_matrix_drive3_import', gmdate('c'));

        return 'updated';
    }
}

$page_map = [
    ['folder' => '02-Page-content/About Us/Support Us', 'path' => 'about-us/support-us', 'keep' => true, 'pdfs' => true],
    ['folder' => '02-Page-content/About Us/Psychiatrists', 'path' => 'about-us/psychiatrists'],
    ['folder' => '02-Page-content/About Us/Social workers', 'path' => 'about-us/social-workers'],
    ['folder' => '02-Page-content/About Us/Nurses', 'path' => 'about-us/nurses'],
    ['folder' => '02-Page-content/About Us/Occupational therapists', 'path' => 'about-us/occupational-therapists'],
    ['folder' => '02-Page-content/About Us/Clinical psychologists', 'path' => 'about-us/psychologists'],
    ['folder' => '02-Page-content/About Us/Recruitment and useful information', 'path' => 'recruitment-and-useful-information'],
    ['folder' => '02-Page-content/About Us/Staff wellbeing', 'path' => 'recruitment-and-useful-information/staff-wellbeing'],
    ['folder' => '02-Page-content/About Us/Apply for a role', 'path' => 'recruitment-and-useful-information/how-to-apply-for-a-role'],
    ['folder' => '02-Page-content/About Us/Thank you page', 'path' => 'thank-you-page'],
    ['folder' => '02-Page-content/About Us/Our locations', 'path' => 'about-us/our-locations', 'keep' => true],
    ['folder' => '02-Page-content/About Us/Academic Institute', 'path' => 'academic-institute'],
    ['folder' => '02-Page-content/About Us/Training Centre', 'path' => 'healthcare-professionals/training-centre'],
    ['folder' => '02-Page-content/About Us/Extending our services', 'path' => 'about-us/extending-our-services'],
    ['folder' => '02-Page-content/About Us/National centre', 'path' => 'national-centre'],
    ['folder' => '02-Page-content/About Us/New hospital', 'path' => 'new-hospital'],
    ['folder' => '02-Page-content/About Us/Advocacy', 'path' => 'about-us/advocacy'],
    ['folder' => '02-Page-content/About Us/Advocacy centre', 'path' => 'advocacy-centre'],
    ['folder' => '02-Page-content/About Us/Our present and future', 'path' => 'about-us/our-present-and-future'],
    ['folder' => '02-Page-content/About Us/Partnering with service users', 'path' => 'about-us/partnering-with-service-users'],
    ['folder' => '02-Page-content/About Us/Policies and publications', 'path' => 'about-us/policies-and-publications'],
    ['folder' => '02-Page-content/What We Offer/Inpatient care', 'path' => 'inpatient-care', 'keep' => true],
    ['folder' => '02-Page-content/What We Offer/St Patrick_s at Home', 'path' => 'what-we-offer/st-patricks-at-home'],
    ['folder' => '02-Page-content/What We Offer/Day programmes', 'path' => 'what-we-offer/day-programmes', 'keep' => true],
    ['folder' => '02-Page-content/What We Offer/Outpatient care', 'path' => 'what-we-offer/outpatient-care-dean-clinics', 'keep' => true],
    ['folder' => '02-Page-content/Healthcare Professionals/Make a referral', 'path' => 'make-a-referral'],
    ['folder' => '02-Page-content/Healthcare Professionals/Contact numbers', 'path' => 'healthcare-professionals/contact-numbers'],
    ['folder' => '02-Page-content/Service Users/About Your Portal', 'path' => 'about-your-portal'],
    ['folder' => '02-Page-content/Service Users/About mental health', 'path' => 'service-users-and-visitors/about-mental-health'],
    ['folder' => '02-Page-content/Service Users/Attending a Dean Clinic', 'path' => 'service-users-and-visitors/attending-a-dean-clinic'],
    ['folder' => '02-Page-content/Service Users/Attending day programmes', 'path' => 'service-users-and-visitors/attending-day-programmes'],
    ['folder' => '02-Page-content/Service Users/Frequently Asked Questions', 'path' => 'service-users-and-visitors/frequently-asked-questions-faqs'],
    ['folder' => '02-Page-content/Service Users/Older adult mental health', 'path' => 'service-users-and-visitors/older-adult-mental-health'],
    ['folder' => '02-Page-content/Service Users/Young adult mental health', 'path' => 'service-users-and-visitors/young-adult-mental-health'],
    ['folder' => '02-Page-content/Service Users/Personality disorders', 'cpt' => 'mental_health', 'slug' => 'personality-disorders'],
    ['folder' => '02-Page-content/Service Users/Stories and support', 'path' => 'service-users-and-visitors/stories-and-support'],
    ['folder' => '02-Page-content/Service Users/Service User Participation', 'path' => 'service-users-and-visitors/service-user-participation'],
    ['folder' => "01-Set-pages/About our St Patrick's at Home Service", 'path' => 'service-users-and-visitors/about-our-st-patricks-at-home-service'],
];

$programme_map = [
    'Acceptance and Commitment Therapy' => ['slug' => 'acceptance-commitment-therapy-act', 'title' => 'Acceptance and Commitment Therapy', 'type' => 'therapy', 'care' => 'day-patient-programme', 'delivery' => 'online'],
    'Access to Recovery' => ['slug' => 'access-to-recovery-programme', 'title' => 'Access to Recovery', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Alcohol-Chemical Step-Down Programme' => ['slug' => 'alcohol-chemical-step-down-programme', 'title' => 'Alcohol / Chemical Step Down Programme', 'type' => 'programme', 'care' => 'inpatient-programme', 'delivery' => 'in-person'],
    'Anxiety Disorders Programme' => ['slug' => 'anxiety-disorders-programme-2', 'title' => 'Anxiety Disorders Programme', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Art Therapy' => ['slug' => 'art-therapy', 'title' => 'Art Therapy', 'type' => 'therapy', 'care' => 'inpatient-programme', 'delivery' => 'in-person'],
    'Bipolar Recovery Programme' => ['slug' => 'bipolar-education-programme', 'title' => 'Bipolar Recovery Programme', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Cognitive Behavioural Therapy (CBT)' => ['slug' => 'cognitive-behavioural-therapy', 'title' => 'Cognitive Behavioural Therapy (CBT)', 'type' => 'therapy', 'care' => 'inpatient-programme', 'delivery' => 'in-person'],
    'Compassion Focused Therapy (CFT)' => ['slug' => 'compassion-focused-therapy', 'title' => 'Compassion-Focused Therapy', 'type' => 'therapy', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'CFT for Adolescents and Families' => ['slug' => 'cft-for-adolescents-and-families', 'title' => 'Compassion-Focused Therapy for Adolescents and Families', 'type' => 'therapy', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'CFT for Older Adults' => ['slug' => 'cft-for-older-adults', 'title' => 'Compassion-Focused Therapy for Older Adults', 'type' => 'therapy', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Depression Recovery Programme' => ['slug' => 'depression-recovery-programme', 'title' => 'Depression Recovery Programme', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Electroconvulsive Therapy' => ['slug' => 'electroconvulsive-therapy', 'title' => 'Electroconvulsive Therapy (ECT)', 'type' => 'therapy', 'care' => 'inpatient-programme', 'delivery' => 'in-person'],
    'Emotion-Focused Therapy for Young Adults' => ['slug' => 'emotion-focused-therapy-for-young-adults', 'title' => 'Emotion-Focused Therapy for Young Adults', 'type' => 'therapy', 'care' => 'day-patient-programme', 'delivery' => 'in-person'],
    'Living Well with Mild Cognitive Impairment' => ['slug' => 'living-well-with-mild-cognitive-impairment', 'title' => 'Living Well with Mild Cognitive Impairment', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'in-person'],
    'Older Adult Formulation Group' => ['slug' => 'older-adult-formulation-group', 'title' => 'Older Adult Formulation Group', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'in-person'],
    'Pathways to Wellness' => ['slug' => 'pathways-to-wellness', 'title' => 'Pathways to Wellness', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Psychosis Recovery Programme' => ['slug' => 'psychosis-recovery-programme', 'title' => 'Psychosis Recovery Programme', 'type' => 'programme', 'care' => 'inpatient-programme', 'delivery' => 'hybrid'],
    'SAGE' => ['slug' => 'sage', 'title' => 'SAGE', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
    'Skills for Attention, Behaviour and Emotions for Adolescents and Families' => ['slug' => 'psychology-skills-group-for-adolescents-2', 'title' => 'Skills for Attention, Behaviour and Emotions for Adolescents and Families', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'online'],
    'Young Adult Formulation Group' => ['slug' => 'young-adult-psychology-groups', 'title' => 'Young Adult Formulation Group', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'in-person'],
    'Young Adult Programme' => ['slug' => 'young-adult-programme', 'title' => 'Young Adult Programme', 'type' => 'programme', 'care' => 'day-patient-programme', 'delivery' => 'hybrid'],
];

if (defined('MATRIX_DRIVE3_NO_RUN') && MATRIX_DRIVE3_NO_RUN) {
    return;
}

$results = [];

$log($dry_run ? 'Dry run: Drive Library 3' : 'Importing Drive Library 3');
if ($dry_run) {
    $results[] = 'Homepage: dry-run';
} else {
    $results[] = 'Homepage: ' . matrix_drive3_update_homepage();
}

foreach ($page_map as $item) {
    $folder = $library . '/' . $item['folder'];
    $docx = matrix_drive3_find_docx($folder);
    $label = $item['path'] ?? (($item['cpt'] ?? '') . '/' . ($item['slug'] ?? ''));
    if ($docx === '') {
        $warn('No docx in ' . $item['folder']);
        $results[] = $label . ': no-docx';
        continue;
    }
    $post_id = 0;
    if (! empty($item['path'])) {
        $post_id = matrix_seed_resolve_page_id_by_path($item['path']);
        if ($post_id === 0) {
            $page = get_page_by_path($item['path']);
            $post_id = $page instanceof WP_Post ? (int) $page->ID : 0;
        }
    } elseif (! empty($item['cpt']) && ! empty($item['slug'])) {
        $found = get_posts([
            'post_type' => $item['cpt'],
            'name' => $item['slug'],
            'post_status' => 'any',
            'posts_per_page' => 1,
        ]);
        $post_id = $found !== [] ? (int) $found[0]->ID : 0;
    }
    if ($post_id <= 0) {
        $warn('Missing WP destination for ' . $label);
        $results[] = $label . ': missing-page';
        continue;
    }
    if ($dry_run) {
        $log('[dry-run] ' . $label . ' <- ' . basename($docx) . ' (#' . $post_id . ')');
        $results[] = $label . ': dry-run #' . $post_id;
        continue;
    }
    $status = matrix_drive3_apply_page($post_id, $docx, $folder, [
        'keep' => ! empty($item['keep']),
        'builder' => true,
        'status' => 'publish',
    ]);
    if (! empty($item['pdfs'])) {
        $pdf_dir = $folder . '/Documents for Support Us page';
        if (! is_dir($pdf_dir)) {
            $pdf_dir = $folder;
        }
        $pdfs = matrix_drive3_import_pdfs($pdf_dir, $post_id);
        if ($pdfs !== []) {
            $rows = get_field('flexible_content_blocks', $post_id);
            if (! is_array($rows)) {
                $rows = [];
            }
            $list = '<ul>';
            foreach ($pdfs as $pdf) {
                $list .= '<li><a href="' . esc_url($pdf['url']) . '">' . esc_html($pdf['title']) . '</a></li>';
            }
            $list .= '</ul>';
            $pdf_html = '<p>Below are our Commitment to Standards in Fundraising Practice and Public Compliance Statements, donor charter, policies and forms.</p>' . $list;
            $merged = false;
            foreach ($rows as &$row) {
                $layout = (string) ($row['acf_fc_layout'] ?? $row['acf_fc_layout'] ?? '');
                $heading = strtolower((string) ($row['heading'] ?? ''));
                if ($layout === 'content' && str_contains($heading, 'fundraising')) {
                    $row['content'] = (string) ($row['content'] ?? '') . $pdf_html;
                    $merged = true;
                    break;
                }
            }
            unset($row);
            if (! $merged) {
                $rows[] = matrix_orlaith_content_row('Our fundraising principles and standards', $pdf_html, 'cream');
            }
            matrix_orlaith_save_page($post_id, $rows, true, (int) get_post_thumbnail_id($post_id));
        }
    }
    $log($label . ' #' . $post_id . ' ' . $status);
    $results[] = $label . ': ' . $status;
}

$offer_root = $library . '/02-Page-content/What We Offer';
foreach ($programme_map as $folder_name => $spec) {
    $folder = $offer_root . '/' . $folder_name;
    $docx = matrix_drive3_find_docx($folder);
    if ($docx === '') {
        $warn('Programme missing docx: ' . $folder_name);
        $results[] = 'programme/' . $spec['slug'] . ': no-docx';
        continue;
    }
    if ($dry_run) {
        $log('[dry-run] programme ' . $spec['title'] . ' <- ' . basename($docx));
        $results[] = 'programme/' . $spec['slug'] . ': dry-run';
        continue;
    }
    $status = matrix_drive3_apply_programme($spec, $docx, $folder);
    $log('programme ' . $spec['title'] . ' ' . $status);
    $results[] = 'programme/' . $spec['slug'] . ': ' . $status;
}

foreach (
    [
        ['folder' => '02-Page-content/Service Users/Older adult mental health', 'slug' => 'older-adults'],
        ['folder' => '02-Page-content/Service Users/Young adult mental health', 'slug' => 'young-adults'],
    ] as $mh
) {
    $found = get_posts([
        'post_type' => 'mental_health',
        'name' => $mh['slug'],
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    $docx = matrix_drive3_find_docx($library . '/' . $mh['folder']);
    if ($found !== [] && $docx !== '') {
        if ($dry_run) {
            $results[] = 'mental_health/' . $mh['slug'] . ': dry-run';
        } else {
            matrix_drive3_apply_page((int) $found[0]->ID, $docx, $library . '/' . $mh['folder'], [
                'builder' => true,
                'status' => 'publish',
            ]);
            $results[] = 'mental_health/' . $mh['slug'] . ': updated';
        }
    }
}

$log('');
$log('Drive Library 3 import summary');
foreach ($results as $line) {
    $log(' - ' . $line);
}

if (class_exists('WP_CLI')) {
    WP_CLI::success($dry_run ? 'Dry run finished.' : 'Imported client Drive copy. Footer pages skipped; Dani still owns leftover advocacy/programme pages.');
}
