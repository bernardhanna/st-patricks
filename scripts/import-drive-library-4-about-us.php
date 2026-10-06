<?php

/**
 * Force-import About Us Drive folders from Library 4.
 *
 * The About Us sheet has ~24 Y / Drive-link rows. This overwrites page copy
 * from those folders and keeps special layouts (team grid, locations, research cards).
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-drive-library-4-about-us.php
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-drive-library-4-about-us.php dry-run
 */

if (! defined('ABSPATH')) {
    exit(1);
}

define('MATRIX_DRIVE3_NO_RUN', true);

require_once __DIR__ . '/import-drive-library-3.php';
require_once __DIR__ . '/lib/page-seed-conventions.php';

$dry_run = in_array('dry-run', array_map('strval', $GLOBALS['argv'] ?? []), true);
$library = get_template_directory() . '/old/content/SPMHS-Content-Gathering-Library 4';

$log = static function (string $message): void {
    if (class_exists('WP_CLI')) {
        WP_CLI::log($message);
    }
};

$find_docx = static function (string $folder, array $prefer = []) use ($library): string {
    $path = $library . '/' . $folder;
    if ($prefer !== [] && is_dir($path)) {
        $files = glob($path . '/*.docx') ?: [];
        foreach ($prefer as $needle) {
            foreach ($files as $file) {
                $base = basename($file);
                if (str_starts_with($base, '~$')) {
                    continue;
                }
                if (preg_match('/already in media library|image for /i', $base)) {
                    continue;
                }
                if (str_contains(strtolower($base), strtolower($needle))) {
                    return $file;
                }
            }
        }
    }

    return matrix_drive3_find_docx($path);
};

$map = [
    ['folder' => '02-Page-content/About Us/Support Us', 'path' => 'about-us/support-us', 'prefer' => ['Support Us page.docx'], 'keep' => true],
    ['folder' => '02-Page-content/About Us/Psychiatrists', 'path' => 'about-us/our-team/psychiatrists', 'prefer' => ['Psychiatrists - text'], 'keep' => true],
    ['folder' => '02-Page-content/About Us/Social workers', 'path' => 'about-us/our-team/social-workers', 'prefer' => ['Social worker text'], 'keep' => true],
    ['folder' => '02-Page-content/About Us/Nurses', 'path' => 'about-us/our-team/nurses', 'prefer' => ['Nursing.docx'], 'keep' => true],
    ['folder' => '02-Page-content/About Us/Occupational therapists', 'path' => 'about-us/our-team/occupational-therapists', 'prefer' => ['Occupational therapy page'], 'keep' => true],
    ['folder' => '02-Page-content/About Us/Clinical psychologists', 'path' => 'about-us/our-team/psychologists', 'prefer' => ['Pscyhology page', 'Psychology page'], 'keep' => true],
    ['folder' => '02-Page-content/About Us/Policies and publications', 'path' => 'about-us/policies-and-publications', 'keep' => true],
    ['folder' => '02-Page-content/About Us/Recruitment and useful information', 'path' => 'about-us/careers/recruitment-and-useful-information'],
    ['folder' => '02-Page-content/About Us/Staff wellbeing', 'path' => 'about-us/careers/recruitment-and-useful-information/staff-wellbeing'],
    ['folder' => '02-Page-content/About Us/Apply for a role', 'path' => 'about-us/careers/recruitment-and-useful-information/how-to-apply-for-a-role'],
    ['folder' => '02-Page-content/About Us/Research', 'path' => 'about-us/research', 'prefer' => ['Research page.docx'], 'keep' => true],
    ['folder' => '02-Page-content/About Us/Advocacy', 'path' => 'about-us/advocacy', 'prefer' => ['Advocacy final']],
    ['folder' => '02-Page-content/About Us/Our present and future', 'path' => 'about-us/our-present-and-future', 'prefer' => ['Our present and future page'], 'keep' => true],
    ['folder' => '02-Page-content/About Us/National centre', 'path' => 'about-us/our-present-and-future/national-centre'],
    ['folder' => '02-Page-content/About Us/New hospital', 'path' => 'about-us/our-present-and-future/new-hospital'],
    ['folder' => '02-Page-content/About Us/Advocacy centre', 'path' => 'about-us/our-present-and-future/advocacy-centre'],
    ['folder' => '02-Page-content/About Us/Academic Institute', 'path' => 'about-us/our-present-and-future/academic-institute', 'prefer' => ['Academic Institute.docx']],
    ['folder' => '02-Page-content/About Us/Training Centre', 'path' => 'about-us/our-present-and-future/traning-centre', 'prefer' => ['Training Centre page (About Us)']],
    ['folder' => '02-Page-content/About Us/Training Centre', 'path' => 'about-us/our-present-and-future/training-centre', 'prefer' => ['Training Centre page (About Us)']],
    ['folder' => '02-Page-content/About Us/Extending our services', 'path' => 'about-us/our-present-and-future/extending-and-enhancing-our-services'],
    ['folder' => '02-Page-content/About Us/Partnering with service users', 'path' => 'about-us/our-present-and-future/partnering-with-service-users'],
    ['folder' => '02-Page-content/About Us/Our locations', 'path' => 'about-us/our-locations', 'prefer' => ['landing page content'], 'keep' => true],
    ['folder' => '02-Page-content/About Us/Payment', 'path' => 'about-us/payment'],
    ['folder' => '02-Page-content/About Us/Women_s Mental Health Network (WMHN)', 'path' => 'about-us/advocacy/women-s-mental-health-network', 'keep' => true],
    ['folder' => '02-Page-content/About Us/Safeguarding', 'path' => 'about-us/policies-and-publications/safeguarding'],
];

$log($dry_run ? 'Dry run: force About Us from Library 4' : 'Force-importing About Us from Library 4');

foreach ($map as $item) {
    $docx = $find_docx($item['folder'], $item['prefer'] ?? []);
    $label = $item['path'];
    if ($docx === '' || preg_match('/already in media library|image for /i', basename($docx))) {
        $log('SKIP no-docx ' . $label);
        continue;
    }

    $post_id = matrix_seed_resolve_page_id_by_path($item['path']);
    if ($post_id === 0) {
        $page = get_page_by_path($item['path']);
        $post_id = $page instanceof WP_Post ? (int) $page->ID : 0;
    }
    if ($post_id <= 0) {
        $log('SKIP missing-page ' . $label);
        continue;
    }

    if ($dry_run) {
        $log('[dry-run] ' . $label . ' #' . $post_id . ' <- ' . basename($docx));
        continue;
    }

    $status = matrix_drive3_apply_page($post_id, $docx, $library . '/' . $item['folder'], [
        'keep' => ! empty($item['keep']),
        'builder' => true,
        'status' => 'publish',
    ]);
    update_post_meta($post_id, 'matrix_content_on_drive', 'yes');
    $log($label . ' #' . $post_id . ' ' . $status . ' <- ' . basename($docx) . ' → ' . get_permalink($post_id));
}

if (class_exists('WP_CLI')) {
    WP_CLI::success($dry_run ? 'Dry run finished.' : 'About Us Library 4 folders imported.');
}
