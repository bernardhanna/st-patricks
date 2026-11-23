<?php
/**
 * Create /about-us/application-form/ with the careers application form template.
 *
 * WP-CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/seed-application-form-page.php
 *
 * Browser (local/staging only):
 *   /wp-content/themes/matrix-starter/scripts/seed-application-form-page.php?key=seed-application-form
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed_host = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed_host || ($_GET['key'] ?? '') !== 'seed-application-form') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$log = [];
$p = static fn (string $html): string => '<p>' . $html . '</p>';

$about_id = (int) (get_page_by_path('about-us')?->ID ?? 0);
if ($about_id <= 0) {
    $log[] = 'MISS about-us parent';
    echo implode("\n", $log) . "\n";
    return;
}

$path = 'about-us/application-form';
$existing = get_page_by_path($path);
$post_id = $existing instanceof WP_Post ? (int) $existing->ID : 0;

if ($post_id <= 0) {
    $post_id = (int) wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => 'Application form',
        'post_name' => 'application-form',
        'post_parent' => $about_id,
        'post_content' => '',
    ], true);

    if ($post_id <= 0) {
        $log[] = 'FAIL create application-form page';
        echo implode("\n", $log) . "\n";
        return;
    }
    $log[] = 'OK created page #' . $post_id;
} else {
    $log[] = 'OK existing page #' . $post_id;
}

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Application form',
    'post_status' => 'publish',
    'post_parent' => $about_id,
    'post_name' => 'application-form',
]);

update_post_meta($post_id, '_wp_page_template', 'templates/page-application-form.php');
update_post_meta($post_id, '_yoast_wpseo_title', "Application form | St Patrick's Mental Health Services");

$vacancies = home_url('/careers/');
$how_to_apply = home_url('/about-us/careers/recruitment-and-useful-information/how-to-apply-for-a-role/');
$intro = $p('Use this form to apply for a role at St Patrick\'s Mental Health Services (SPMHS). Enter the vacancy title you are applying for, then complete your details below.')
    . $p('You can <a href="' . esc_url($vacancies) . '">view current vacancies</a> first, or read <a href="' . esc_url($how_to_apply) . '">how to apply for a role</a>.');

$flexi = [
    matrix_orlaith_hero_row(
        'Application form',
        $p('Submit your application to work with St Patrick\'s Mental Health Services.'),
        0
    ),
    matrix_orlaith_content_row('', $intro, 'white'),
    matrix_orlaith_useful_links_row([
        'Careers' => 'about-us/careers',
        'How to apply for a role' => 'about-us/careers/recruitment-and-useful-information/how-to-apply-for-a-role',
        'Recruitment and useful information' => 'about-us/careers/recruitment-and-useful-information',
        'Attending an interview' => 'about-us/careers/attending-an-interview',
    ]),
];

update_field('flexible_content_blocks', $flexi, $post_id);
clean_post_cache($post_id);

$permalink = get_permalink($post_id);
$log[] = 'OK template + flexi on #' . $post_id;
$log[] = 'OK permalink ' . $permalink;

echo implode("\n", $log) . "\n";
