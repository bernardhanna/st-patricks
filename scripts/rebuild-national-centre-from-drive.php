<?php

/**
 * Rebuild National Centre from Drive Library 3.
 *
 * Source: old/content/.../National centre/National Centre.docx
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-national-centre-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('national-centre')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find national-centre page');
}

$home = untrailingslashit(home_url('/'));
$img_id = 4035;

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label): string {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};

$urls = [
    'advocacy' => $home . '/advocacy-centre/',
    'academic' => $home . '/academic-institute/',
    'training' => $home . '/healthcare-professionals/training-centre/',
];

$hero_intro = $p('St Patrick’s Mental Health Services is committed to developing a national centre for mentally healthy living.');

$body = $p('The national centre for mentally healthy living will provide mental health and wellbeing initiatives for the general population, as well as a range of services for people experiencing mental health difficulties.')
    . $p('Planning permission to proceed with phase one of this development was granted from Dublin City Council. This includes a programme of conservation work to repair the fabric of the Historic Building of St Patrick’s University Hospital in Dublin 8 and to transform its ground floor, which will be home to:')
    . '<ul>'
    . '<li>an Interactive Education Centre</li>'
    . '<li>an ' . $a($urls['advocacy'], 'Advocacy Centre') . '</li>'
    . '<li>an ' . $a($urls['academic'], 'Academic Institute') . '</li>'
    . '<li>a ' . $a($urls['training'], 'Training Centre') . '</li>'
    . '<li>clinical rooms and spaces for service delivery.</li>'
    . '</ul>';

$education = $p('The Interactive Education Centre will tackle misinformation about mental health; challenge stigma; and educate people, particularly young people, about the practical tools we can all use throughout our lives to support our wellbeing and mental health. It will also immerse them in the history of our founder, Jonathan Swift, and the hospital building. The education centre will be free of charge to the public, and will open up compassionate conversations around mental health and equip visitors with the knowledge and tools to better understand and support their mental health.');

$hero = matrix_orlaith_hero_row('National Centre', $hero_intro, $img_id);
$hero['layout_style'] = $img_id > 0 ? 'image_split' : 'title_accent';
$hero['text_max_width'] = 'default';
$hero['heading_max_width'] = 'default';
$hero['current_crumb_label'] = 'National Centre';
$hero['background_color'] = '#C6ECF4';
$hero['accent_color'] = '#6FC9C0';

$flexi = [
    $hero,
    matrix_orlaith_content_row('', $body, 'white'),
    matrix_orlaith_content_row('Interactive Education Centre', $education, 'cream'),
    matrix_orlaith_useful_links_row([
        'Advocacy Centre' => $urls['advocacy'],
        'Academic Institute' => $urls['academic'],
        'Training Centre' => $urls['training'],
    ]),
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'National Centre',
]);

update_field('flexible_content_blocks', $flexi, $post_id);

WP_CLI::success('Rebuilt National Centre ' . $post_id . ' → ' . get_permalink($post_id));
foreach (get_field('flexible_content_blocks', $post_id) as $i => $row) {
    WP_CLI::log(sprintf('[%d] %s %s', $i, $row['acf_fc_layout'] ?? '', $row['heading'] ?? ''));
}
