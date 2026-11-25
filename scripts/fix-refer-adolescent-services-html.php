<?php

/**
 * Clean Word-paste HTML in the "Our adolescent services" video_showcase intro
 * on Refer an adolescent.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/fix-refer-adolescent-services-html.php
 */

$post = get_page_by_path('healthcare-professionals/refer-an-adolescent');
$post_id = $post instanceof WP_Post ? (int) $post->ID : 0;

if ($post_id === 0) {
    $message = 'Could not find healthcare-professionals/refer-an-adolescent.';
    if (class_exists('WP_CLI')) {
        WP_CLI::error($message);
    }
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows) || $rows === []) {
    $message = "No flexible_content_blocks on post {$post_id}.";
    if (class_exists('WP_CLI')) {
        WP_CLI::error($message);
    }
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$home = untrailingslashit(home_url('/'));
$updated = false;

foreach ($rows as $index => $row) {
    if (($row['acf_fc_layout'] ?? '') !== 'video_showcase') {
        continue;
    }

    $heading = trim((string) ($row['heading'] ?? ''));
    if (strcasecmp($heading, 'Our adolescent services') !== 0) {
        continue;
    }

    $intro = (string) ($row['intro'] ?? '');
    if ($intro === '') {
        continue;
    }

    $cleaned = function_exists('matrix_clean_pasted_editor_html')
        ? matrix_clean_pasted_editor_html($intro)
        : $intro;

    // Prefer site-relative service URLs (works across local/staging/prod).
    $cleaned = str_replace(
        [
            esc_url($home . '/inpatient-care/'),
            esc_url($home . '/what-we-offer/st-patricks-at-home/'),
            esc_url($home . '/what-we-offer/outpatient-care-dean-clinics/'),
            esc_url($home . '/what-we-offer/day-programmes/'),
            'https://st-patricks.s1.matrix-test.com/inpatient-care/',
            'https://st-patricks.s1.matrix-test.com/what-we-offer/st-patricks-at-home/',
            'https://st-patricks.s1.matrix-test.com/what-we-offer/outpatient-care-dean-clinics/',
            'https://st-patricks.s1.matrix-test.com/what-we-offer/day-programmes/',
            'http://localhost:10034/inpatient-care/',
            'http://localhost:10034/what-we-offer/st-patricks-at-home/',
            'http://localhost:10034/what-we-offer/outpatient-care-dean-clinics/',
            'http://localhost:10034/what-we-offer/day-programmes/',
        ],
        [
            '/inpatient-care/',
            '/what-we-offer/st-patricks-at-home/',
            '/what-we-offer/outpatient-care-dean-clinics/',
            '/what-we-offer/day-programmes/',
            '/inpatient-care/',
            '/what-we-offer/st-patricks-at-home/',
            '/what-we-offer/outpatient-care-dean-clinics/',
            '/what-we-offer/day-programmes/',
            '/inpatient-care/',
            '/what-we-offer/st-patricks-at-home/',
            '/what-we-offer/outpatient-care-dean-clinics/',
            '/what-we-offer/day-programmes/',
        ],
        $cleaned
    );

    // Convert en-dash bullet paragraphs into a real list when present.
    $cleaned = preg_replace(
        '#<p>\s*Referrals can be made:\s*</p>\s*<p>\s*[–—-]\s*(.*?)\s*</p>\s*<p>\s*[–—-]\s*(.*?)\s*</p>#su',
        '<p>Referrals can be made:</p><ul><li>$1</li><li>$2</li></ul>',
        $cleaned,
        1
    ) ?? $cleaned;

    if ($cleaned === $intro) {
        $log = "Row {$index}: intro already clean.";
        if (class_exists('WP_CLI')) {
            WP_CLI::log($log);
        } else {
            echo $log . PHP_EOL;
        }
        continue;
    }

    $rows[$index]['intro'] = $cleaned;
    $updated = true;

    $log = "Row {$index}: cleaned Our adolescent services intro (" . strlen($intro) . ' → ' . strlen($cleaned) . ' bytes).';
    if (class_exists('WP_CLI')) {
        WP_CLI::log($log);
    } else {
        echo $log . PHP_EOL;
    }
}

if (! $updated) {
    $message = 'No video_showcase intro changes were needed.';
    if (class_exists('WP_CLI')) {
        WP_CLI::success($message);
    } else {
        echo $message . PHP_EOL;
    }
    exit(0);
}

$field = get_field_object('flexible_content_blocks', $post_id);
$field_key = is_array($field) && ! empty($field['key']) ? (string) $field['key'] : 'flexible_content_blocks';
update_field($field_key, $rows, $post_id);

// ACF may return false even when the value was written; verify from the DB.
$verify_rows = get_field('flexible_content_blocks', $post_id);
$verify_ok = false;

if (is_array($verify_rows)) {
    foreach ($verify_rows as $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'video_showcase') {
            continue;
        }
        if (strcasecmp(trim((string) ($row['heading'] ?? '')), 'Our adolescent services') !== 0) {
            continue;
        }
        $intro = (string) ($row['intro'] ?? '');
        $verify_ok = $intro !== '' && ! str_contains($intro, 'MsoNormal') && ! str_contains($intro, 'font-size:');
        break;
    }
}

if (! $verify_ok) {
    $message = 'Intro still contains Word paste artefacts after update_field.';
    if (class_exists('WP_CLI')) {
        WP_CLI::error($message);
    }
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$message = "Updated refer-an-adolescent ({$post_id}) Our adolescent services intro HTML.";
if (class_exists('WP_CLI')) {
    WP_CLI::success($message);
} else {
    echo $message . PHP_EOL;
}
