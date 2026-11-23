<?php
/**
 * Add clinical / non-clinical brochure PDF links on the recruitment brochures hub.
 *
 * WP-CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/seed-recruitment-brochures-pdfs.php
 *
 * Browser (local/staging only):
 *   /wp-content/themes/matrix-starter/scripts/seed-recruitment-brochures-pdfs.php?key=seed-recruitment-brochures
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed_host = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed_host || ($_GET['key'] ?? '') !== 'seed-recruitment-brochures') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$log = [];
$p = static fn (string $html): string => '<p>' . $html . '</p>';

$find_pdf_url = static function (string $filename): string {
    $posts = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 5,
        'fields' => 'ids',
        's' => pathinfo($filename, PATHINFO_FILENAME),
    ]);

    foreach ($posts as $id) {
        $url = wp_get_attachment_url((int) $id);
        if (is_string($url) && str_contains($url, $filename)) {
            return $url;
        }
        $file = get_attached_file((int) $id);
        if (is_string($file) && str_ends_with($file, $filename)) {
            $url = wp_get_attachment_url((int) $id);
            if (is_string($url) && $url !== '') {
                return $url;
            }
        }
    }

    // Known staging/local upload path fallback.
    $candidates = [
        '/wp-content/uploads/2026/07/' . $filename,
        '/wp-content/uploads/2025/11/' . $filename,
    ];
    foreach ($candidates as $path) {
        $abs = rtrim(ABSPATH, '/') . $path;
        if (is_readable($abs)) {
            return home_url($path);
        }
    }

    return '';
};

$sideload = static function (string $abs, string $title) use (&$log): string {
    if (! is_readable($abs)) {
        $log[] = 'MISS file ' . $abs;
        return '';
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = wp_tempnam(basename($abs));
    if (! $tmp || ! copy($abs, $tmp)) {
        $log[] = 'FAIL copy ' . basename($abs);
        return '';
    }

    $file = [
        'name' => basename($abs),
        'tmp_name' => $tmp,
    ];
    $id = media_handle_sideload($file, 0, $title);
    if (is_wp_error($id)) {
        @unlink($tmp);
        $log[] = 'FAIL sideload ' . basename($abs) . ' ' . $id->get_error_message();
        return '';
    }

    update_post_meta((int) $id, '_matrix_drive_source', basename($abs));
    $url = wp_get_attachment_url((int) $id);
    $log[] = 'OK sideload #' . (int) $id . ' ' . $url;

    return is_string($url) ? $url : '';
};

$clinical_name = 'Clinical-Roles-at-St-Patrick_s-Mental-Health-Services.pdf';
$nonclinical_name = 'Non-Clinical-Roles-at-St-Patrick_s-Mental-Health-Services.pdf';

$clinical_pdf = $find_pdf_url($clinical_name);
$nonclinical_pdf = $find_pdf_url($nonclinical_name);

$library = get_template_directory() . '/new/SPMHS-Content-Gathering-Library/02-Page-content/About Us';
if ($clinical_pdf === '') {
    $clinical_pdf = $sideload(
        $library . '/Clinical careers brochure/Clinical Roles at St Patrick_s Mental Health Services.pdf',
        'Clinical Roles at St Patrick\'s Mental Health Services'
    );
}
if ($nonclinical_pdf === '') {
    $nonclinical_pdf = $sideload(
        $library . '/Non-clinical careers brochure/Non-Clinical Roles at St Patrick_s Mental Health Services.pdf',
        'Non-Clinical Roles at St Patrick\'s Mental Health Services'
    );
}

if ($clinical_pdf === '' || $nonclinical_pdf === '') {
    $log[] = 'FAIL missing PDF urls clinical=' . ($clinical_pdf !== '' ? 'ok' : 'empty')
        . ' nonclinical=' . ($nonclinical_pdf !== '' ? 'ok' : 'empty');
    echo implode("\n", $log) . "\n";
    return;
}

$hub = get_page_by_path('about-us/careers/recruitment-brochures')
    ?: get_page_by_path('about-us/recruitment-brochures')
    ?: get_page_by_path('careers/recruitment-brochures');

if (! $hub instanceof WP_Post) {
    $careers_id = (int) (get_page_by_path('about-us/careers')?->ID ?? 0);
    if ($careers_id <= 0) {
        $log[] = 'MISS careers parent';
        echo implode("\n", $log) . "\n";
        return;
    }
    $hub_id = (int) wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => 'Recruitment brochures',
        'post_name' => 'recruitment-brochures',
        'post_parent' => $careers_id,
        'post_content' => '',
    ], true);
    if ($hub_id <= 0) {
        $log[] = 'FAIL create hub';
        echo implode("\n", $log) . "\n";
        return;
    }
    $hub = get_post($hub_id);
    $log[] = 'OK created hub #' . $hub_id;
} else {
    $log[] = 'OK hub #' . (int) $hub->ID;
}

$hub_id = (int) $hub->ID;
$clinical_page = home_url('/about-us/clinical-careers-brochure/');
$nonclinical_page = home_url('/about-us/non-clinical-careers-brochure/');

$body = $p('Explore our recruitment brochures for an overview of clinical and non-clinical roles at St Patrick\'s Mental Health Services.')
    . '<ul>'
    . '<li><a href="' . esc_url($clinical_pdf) . '" target="_blank" rel="noopener noreferrer">Download clinical careers brochure (PDF)</a>'
    . ' — or <a href="' . esc_url($clinical_page) . '">view the clinical careers brochure page</a></li>'
    . '<li><a href="' . esc_url($nonclinical_pdf) . '" target="_blank" rel="noopener noreferrer">Download non-clinical careers brochure (PDF)</a>'
    . ' — or <a href="' . esc_url($nonclinical_page) . '">view the non-clinical careers brochure page</a></li>'
    . '</ul>';

$flexi = [
    matrix_orlaith_hero_row(
        'Recruitment brochures',
        $p('Learn more about working with us across clinical and non-clinical roles.'),
        0
    ),
    matrix_orlaith_content_row('', $body, 'white'),
    matrix_orlaith_useful_links_row([
        'Careers' => 'about-us/careers',
        'How to apply for a role' => 'about-us/careers/recruitment-and-useful-information/how-to-apply-for-a-role',
        'Recruitment and useful information' => 'about-us/careers/recruitment-and-useful-information',
        'Application form' => 'about-us/application-form',
    ]),
];

update_field('flexible_content_blocks', $flexi, $hub_id);
clean_post_cache($hub_id);

$log[] = 'OK clinical PDF ' . $clinical_pdf;
$log[] = 'OK nonclinical PDF ' . $nonclinical_pdf;
$log[] = 'OK hub updated ' . get_permalink($hub_id);

echo implode("\n", $log) . "\n";
