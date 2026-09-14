<?php

/**
 * Rebuild About Us > Extending/Expanding our services from Drive Library 3.
 *
 * Source: old/content/.../Extending our services/Extending our services.docx
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-extending-our-services-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('about-us/extending-our-services')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/extending-our-services');
}

$home = untrailingslashit(home_url('/'));
$img_id = 4034;

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label): string {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};

$urls = [
    'homecare' => $home . '/what-we-offer/st-patricks-at-home/',
    'inpatient' => $home . '/inpatient-care/',
    'day' => $home . '/what-we-offer/day-programmes/',
    'dean' => $home . '/what-we-offer/outpatient-care-dean-clinics/',
    'what_we_offer' => $home . '/what-we-offer/',
    'team' => $home . '/about-us/our-team/',
    'locations' => $home . '/about-us/our-locations/',
];

$hero_intro = $p('St Patrick’s Mental Health Services is committed to playing our part in meeting the increased demand for mental healthcare services and supports in the coming years.');

$enhancing = $p('By enhancing and expanding our services, we can respond to service users’ needs at every stage of their recovery journey.')
    . $p('We will use in-person, remote and hybrid care models to extend our mental healthcare services.')
    . '<h3>Homecare</h3>'
    . $p('We will continue to expand and enhance our '
        . $a($urls['homecare'], 'St Patrick’s at Home services')
        . ' for adults and adolescents so that as many service users as possible can access care from their homes, where it is appropriate to their needs.')
    . '<h3>Inpatient services</h3>'
    . $p('We know how important it is to ensure that we continue to provide the highest quality '
        . $a($urls['inpatient'], 'inpatient services')
        . ' for people who need to be cared for in person. We will continue to invest in the inpatient services we provide.')
    . '<h3>Day services</h3>'
    . $p('Our St Patrick’s Wellness and Recovery Centre offers a '
        . $a($urls['day'], 'range of onsite, online and hybrid day programmes')
        . '. We will expand these day services to make mental healthcare more accessible from across the country.')
    . '<h3>Outpatient services</h3>'
    . $p('Our '
        . $a($urls['dean'], 'outpatient Dean Clinics')
        . ' provide an integrated service where service users can access mental health assessment and treatment for the first time, or as part of their continued care. We will continue to refine and improve our outpatient services by further embedding remote access.');

$hero = matrix_orlaith_hero_row('Expanding our services', $hero_intro, $img_id);
$hero['layout_style'] = $img_id > 0 ? 'image_split' : 'title_accent';
$hero['text_max_width'] = 'default';
$hero['heading_max_width'] = 'default';
$hero['current_crumb_label'] = 'Expanding our services';
$hero['background_color'] = '#C6ECF4';
$hero['accent_color'] = '#6FC9C0';

$flexi = [
    $hero,
    matrix_orlaith_content_row('Enhancing our services', $enhancing, 'white'),
    matrix_orlaith_video_row('', '', [[
        'url' => 'https://www.youtube.com/watch?v=ZkX7DqQE_60',
        'title' => '',
    ]]),
    matrix_orlaith_useful_links_row([
        'What we offer' => $urls['what_we_offer'],
        'Our team' => $urls['team'],
        'Our locations' => $urls['locations'],
    ]),
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Expanding our services',
]);

update_field('flexible_content_blocks', $flexi, $post_id);

WP_CLI::success('Rebuilt Extending/Expanding our services ' . $post_id . ' → ' . get_permalink($post_id));
foreach (get_field('flexible_content_blocks', $post_id) as $i => $row) {
    WP_CLI::log(sprintf('[%d] %s %s', $i, $row['acf_fc_layout'] ?? '', $row['heading'] ?? ''));
}
