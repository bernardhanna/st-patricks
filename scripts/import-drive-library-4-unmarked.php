<?php

/**
 * Import unmarked Drive-mapped pages from SPMHS-Content-Gathering-Library 4.
 *
 * Targets sheet rows that have a Drive folder link but Content on Drive != Yes:
 * Research, Training Centre (About Us), Extending, Payment, Safeguarding,
 * SUAN, SUAS, Clinical / Non-clinical careers brochures.
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-drive-library-4-unmarked.php
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-drive-library-4-unmarked.php dry-run
 */

if (! defined('ABSPATH')) {
    exit(1);
}

define('MATRIX_DRIVE3_NO_RUN', true);

require_once __DIR__ . '/import-drive-library-3.php';
require_once __DIR__ . '/lib/orlaith-page-helpers.php';
require_once __DIR__ . '/lib/page-seed-conventions.php';

$dry_run = in_array('dry-run', array_map('strval', $GLOBALS['argv'] ?? []), true);
$library = get_template_directory() . '/old/content/SPMHS-Content-Gathering-Library 4';

$log = static function (string $message): void {
    if (class_exists('WP_CLI')) {
        WP_CLI::log($message);
    }
};

$mark_drive = static function (int $post_id): void {
    if ($post_id <= 0) {
        return;
    }
    update_post_meta($post_id, 'matrix_content_on_drive', 'yes');
    update_post_meta($post_id, '_matrix_drive3_import', gmdate('c'));
};

$ensure_page = static function (string $path, string $title, int $parent_id = 0): int {
    $existing = get_page_by_path($path);
    if ($existing instanceof WP_Post) {
        return (int) $existing->ID;
    }

    $slug = basename($path);
    $id = wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => $title,
        'post_name' => $slug,
        'post_parent' => $parent_id,
        'post_content' => '',
    ], true);

    return is_wp_error($id) ? 0 : (int) $id;
};

$apply_brochure = static function (int $post_id, string $folder, string $title) use ($library, $dry_run, $log, $mark_drive): string {
    $dir = $library . '/' . $folder;
    if ($post_id <= 0 || ! is_dir($dir)) {
        return 'missing';
    }

    $pdf_files = array_values(array_filter(glob($dir . '/*.pdf') ?: [], static function (string $file): bool {
        return ! str_starts_with(basename($file), '~$');
    }));
    if ($pdf_files === []) {
        return 'no-pdf';
    }

    if ($dry_run) {
        $log('[dry-run] brochure #' . $post_id . ' <- ' . basename($pdf_files[0]));

        return 'dry-run';
    }

    $pdfs = matrix_drive3_import_pdfs($dir, $post_id);
    if ($pdfs === []) {
        return 'no-pdf';
    }

    $pdf = $pdfs[0];
    $intro = '<p>Download our brochure for an overview of '
        . esc_html($title)
        . ' at St Patrick\'s Mental Health Services.</p>';

    $hero = matrix_orlaith_hero_row($title, $intro, 0);
    $hero['primary_button'] = matrix_orlaith_button('Download brochure (PDF)', $pdf['url'], '_blank');

    $body = '<p><a href="' . esc_url($pdf['url']) . '" target="_blank" rel="noopener noreferrer">'
        . esc_html($pdf['title'])
        . '</a></p>';

    $rows = [
        $hero,
        matrix_orlaith_content_row('', $body, 'white'),
    ];

    matrix_orlaith_save_page($post_id, $rows, true, 0);
    wp_update_post([
        'ID' => $post_id,
        'post_title' => $title,
        'post_status' => 'publish',
    ]);
    $mark_drive($post_id);

    return 'updated:' . $pdf['title'];
};

$results = [];
$log($dry_run ? 'Dry run: Library 4 unmarked batch' : 'Importing Library 4 unmarked batch');

// --- Extending (hand-built layout already matches Drive; refresh + mark) ---
$extending_id = (int) (get_page_by_path('about-us/our-present-and-future/extending-and-enhancing-our-services')?->ID ?? 0);
$extending_docx = matrix_drive3_find_docx($library . '/02-Page-content/About Us/Extending our services');
if ($extending_id > 0 && $extending_docx !== '') {
    if ($dry_run) {
        $results[] = 'extending: dry-run #' . $extending_id;
    } else {
        // Keep curated rebuild (links + video); just stamp Drive meta.
        $mark_drive($extending_id);
        $results[] = 'extending: marked #' . $extending_id;
    }
} else {
    $results[] = 'extending: missing';
}

// --- Payment ---
$payment_id = (int) (get_page_by_path('about-us/payment')?->ID ?? 0);
$payment_folder = $library . '/02-Page-content/About Us/Payment';
$payment_docx = matrix_drive3_find_docx($payment_folder);
if ($payment_id > 0 && $payment_docx !== '') {
    if ($dry_run) {
        $results[] = 'payment: dry-run #' . $payment_id . ' <- ' . basename($payment_docx);
    } else {
        // Prefer curated rebuild script content if present; only overwrite when empty.
        $flexi = get_field('flexible_content_blocks', $payment_id);
        if (! is_array($flexi) || count($flexi) < 2) {
            $status = matrix_drive3_apply_page($payment_id, $payment_docx, $payment_folder, [
                'builder' => true,
                'status' => 'publish',
            ]);
            $results[] = 'payment: ' . $status . ' #' . $payment_id;
        } else {
            $results[] = 'payment: kept-existing #' . $payment_id;
        }
        $mark_drive($payment_id);
    }
} else {
    $results[] = 'payment: missing';
}

// --- Safeguarding ---
$safe_id = (int) (get_page_by_path('about-us/policies-and-publications/safeguarding')?->ID ?? 0);
$safe_folder = $library . '/02-Page-content/About Us/Safeguarding';
$safe_docx = matrix_drive3_find_docx($safe_folder);
if ($safe_id > 0 && $safe_docx !== '') {
    if ($dry_run) {
        $results[] = 'safeguarding: dry-run #' . $safe_id;
    } else {
        $flexi = get_field('flexible_content_blocks', $safe_id);
        if (! is_array($flexi) || count($flexi) < 2) {
            $status = matrix_drive3_apply_page($safe_id, $safe_docx, $safe_folder, [
                'builder' => true,
                'status' => 'publish',
            ]);
            $results[] = 'safeguarding: ' . $status . ' #' . $safe_id;
        } else {
            $results[] = 'safeguarding: kept-existing #' . $safe_id;
        }
        $mark_drive($safe_id);
    }
} else {
    $results[] = 'safeguarding: missing';
}

// --- Research (keep research_cards_grid layout; refresh intro from Drive if needed) ---
$research_id = (int) (get_page_by_path('about-us/research')?->ID ?? 0);
$research_folder = $library . '/02-Page-content/About Us/Research';
$research_docx = matrix_drive3_find_docx($research_folder);
if ($research_id > 0 && $research_docx !== '') {
    if ($dry_run) {
        $results[] = 'research: dry-run #' . $research_id;
    } else {
        $flexi = get_field('flexible_content_blocks', $research_id);
        $has_cards = false;
        if (is_array($flexi)) {
            foreach ($flexi as $row) {
                if (($row['acf_fc_layout'] ?? '') === 'research_cards_grid') {
                    $has_cards = true;
                    break;
                }
            }
        }
        if ($has_cards) {
            $results[] = 'research: kept-cards-layout #' . $research_id;
        } else {
            $status = matrix_drive3_apply_page($research_id, $research_docx, $research_folder, [
                'builder' => true,
                'status' => 'publish',
            ]);
            $results[] = 'research: ' . $status . ' #' . $research_id;
        }
        $mark_drive($research_id);
    }
} else {
    $results[] = 'research: missing';
}

// --- Training Centre (About Us / Our present and future) ---
$present_id = (int) (get_page_by_path('about-us/our-present-and-future')?->ID ?? 0);
$training_path = 'about-us/our-present-and-future/training-centre';
$training_id = (int) (get_page_by_path($training_path)?->ID ?? 0);
if ($training_id === 0 && ! $dry_run) {
    $training_id = $ensure_page($training_path, 'Training Centre', $present_id);
}
$training_folder = $library . '/02-Page-content/About Us/Training Centre';
$training_docx = matrix_drive3_find_docx($training_folder);
if ($training_docx !== '' && ($training_id > 0 || $dry_run)) {
    if ($dry_run) {
        $results[] = 'training-centre-about: dry-run <- ' . basename($training_docx);
    } else {
        $status = matrix_drive3_apply_page($training_id, $training_docx, $training_folder, [
            'builder' => true,
            'status' => 'publish',
        ]);
        // Add strategy video if missing.
        $flexi = get_field('flexible_content_blocks', $training_id);
        if (is_array($flexi)) {
            $has_video = false;
            foreach ($flexi as $row) {
                if (($row['acf_fc_layout'] ?? '') === 'video_showcase') {
                    $has_video = true;
                    break;
                }
            }
            if (! $has_video) {
                $poster = matrix_orlaith_find_image(4091, 'Research and Training Video.png');
                $flexi[] = matrix_orlaith_video_row(
                    'Research and training',
                    '',
                    [[
                        'url' => 'https://www.youtube.com/watch?v=AjJQxOrmv1o',
                        'caption' => 'Learn more about our Academic Institute and our commitment to supporting staff and organisations working in mental health through our new training centre.',
                        'poster' => $poster,
                    ]]
                );
                update_field('flexible_content_blocks', $flexi, $training_id);
            }
        }
        $mark_drive($training_id);
        $results[] = 'training-centre-about: ' . $status . ' #' . $training_id . ' → ' . get_permalink($training_id);
    }
} else {
    $results[] = 'training-centre-about: missing';
}

// --- SUAN / SUAS (get_involved CPT + page drafts) ---
$participation = [
    [
        'label' => 'suan',
        'folder' => '02-Page-content/Service Users/Service User Advisory Network',
        'cpt_id' => 3102,
        'page_id' => 1405,
        'title' => 'Service User Advisory Network',
    ],
    [
        'label' => 'suas',
        'folder' => '02-Page-content/Service Users/Service User and Supporters Council (SUAS)',
        'cpt_id' => 3101,
        'page_id' => 1406,
        'title' => 'Service User and Supporters Council',
    ],
];

foreach ($participation as $item) {
    $folder = $library . '/' . $item['folder'];
    $docx = matrix_drive3_find_docx($folder);
    if ($docx === '') {
        $results[] = $item['label'] . ': no-docx';
        continue;
    }

    foreach (['cpt_id', 'page_id'] as $key) {
        $post_id = (int) $item[$key];
        if ($post_id <= 0 || ! get_post($post_id)) {
            continue;
        }
        if ($dry_run) {
            $results[] = $item['label'] . '/' . $key . ': dry-run #' . $post_id;
            continue;
        }
        $status = matrix_drive3_apply_page($post_id, $docx, $folder, [
            'builder' => true,
            'status' => 'publish',
            'keep' => true,
        ]);
        $mark_drive($post_id);
        $results[] = $item['label'] . '/' . $key . ': ' . $status . ' #' . $post_id . ' → ' . get_permalink($post_id);
    }
}

// --- Clinical / Non-clinical careers brochures ---
$brochures = [
    [
        'ids' => [3576, 208],
        'folder' => '02-Page-content/About Us/Clinical careers brochure',
        'title' => 'Clinical careers brochure',
        'label' => 'clinical-brochure',
    ],
    [
        'ids' => [3577],
        'folder' => '02-Page-content/About Us/Non-clinical careers brochure',
        'title' => 'Non-clinical careers brochure',
        'label' => 'non-clinical-brochure',
    ],
];

foreach ($brochures as $brochure) {
    foreach ($brochure['ids'] as $id) {
        if (! get_post($id)) {
            $results[] = $brochure['label'] . ': missing #' . $id;
            continue;
        }
        $status = $apply_brochure((int) $id, $brochure['folder'], $brochure['title']);
        $results[] = $brochure['label'] . ' #' . $id . ': ' . $status;
    }
}

$log('');
$log('Library 4 unmarked import summary');
foreach ($results as $line) {
    $log(' - ' . $line);
}

if (class_exists('WP_CLI')) {
    WP_CLI::success($dry_run ? 'Dry run finished.' : 'Library 4 unmarked content imported.');
}
