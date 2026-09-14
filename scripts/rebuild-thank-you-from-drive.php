<?php

/**
 * Rebuild Thank you page from Drive Library 3.
 *
 * Source: Thank you page.docx
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-thank-you-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('thank-you-page')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find thank-you-page');
}

$home = untrailingslashit(home_url('/'));
$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label): string {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};

$recruitment = $home . '/recruitment-and-useful-information/';

$hero_intro = $p("Thank you for applying for a role at St Patrick's Mental Health Services (SPMHS). Your application has been received successfully.")
    . $p('To learn more about working with us, including our interview process and how to prepare, please '
        . $a($recruitment, 'click here')
        . '.')
    . $p('If you have any questions in the meantime, you can contact our Human Resources team at '
        . $a('mailto:hr@stpatricks.ie', 'hr@stpatricks.ie')
        . ' or call '
        . $a('tel:012493435', '01 249 3435')
        . '.');

$hero = matrix_orlaith_hero_row('Thank you for your application', $hero_intro, 0);
$hero['current_crumb_label'] = 'Thank you';
$hero['primary_button'] = matrix_orlaith_button('Learn about recruitment', $recruitment);

$flexi = [
    $hero,
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Thank you page',
]);

update_field('flexible_content_blocks', $flexi, $post_id);

WP_CLI::success(sprintf(
    'Rebuilt Thank you page #%d → %s',
    $post_id,
    get_permalink($post_id)
));

foreach ((array) get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '?');
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
    $extra = ! empty($row['primary_button']['title']) ? ' | btn=' . $row['primary_button']['title'] : '';
    WP_CLI::log("[{$i}] {$layout} {$heading}{$extra}");
}
