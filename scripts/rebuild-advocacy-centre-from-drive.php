<?php

/**
 * Rebuild Our Present and Future > Advocacy Centre from Drive Library.
 *
 * Source: old/content/.../About Us/Advocacy centre/Advocacy Centre page.docx
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-advocacy-centre-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('about-us/our-present-and-future/advocacy-centre')?->ID
    ?? get_page_by_path('advocacy-centre')?->ID
    ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find advocacy-centre page');
}

$home = untrailingslashit(home_url('/'));
$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label): string {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};

// "Advocacy and education" media library image used across advocacy/education pages.
$hero_image = 4090;

$urls = [
    'advocacy' => $home . '/about-us/advocacy/',
    'policies' => $home . '/about-us/policies-and-publications/',
    'research' => $home . '/about-us/research/',
    'participation' => $home . '/service-users-and-visitors/service-user-participation/',
    'history' => $home . '/about-us/our-history/',
    'strategy' => $home . '/about-us/our-present-and-future/',
    'national' => $home . '/about-us/our-present-and-future/national-centre/',
];

$hero_intro = $p('The Advocacy Centre at St Patrick’s Mental Health Services works to address the issues that affect people experiencing mental health difficulties.');

$body = $p('The Advocacy Centre is a core component of our '
        . $a($urls['national'], 'national centre for mentally healthy living')
        . '.')
    . $p('Through the centre, we strive to promote greater understanding of mental health difficulties in Ireland and beyond and to remove the stigma associated with such difficulties.')
    . $p('We aim to advance the rights of people experiencing mental health difficulties to be treated with dignity, respect and without discrimination, educating on mental health rights and equality issues.')
    . $p('We also work to strengthen and develop collaborative advocacy partnerships and continue our contributions to consultations and submissions that inform policy development.')
    . $p('The Advocacy Centre will complement and contribute to the activities of the national centre, and especially the activities of the Education Centre, through:')
    . '<ul>'
    . '<li>promoting human rights issues in relation to mental health</li>'
    . '<li>educating on mental health rights and equality issues</li>'
    . '<li>engaging the public to support positive change in areas such as climate change and mental health, among others.</li>'
    . '</ul>'
    . $p('It is intended that the Advocacy Centre will align with European child mental health advocacy goals and with the vision of the World Health Organisation (WHO) Mental Health Action Plan, and will have a particular focus on child mental health.');

$hero = matrix_orlaith_hero_row('Advocacy Centre', $hero_intro, $hero_image);
$hero['layout_style'] = $hero_image > 0 ? 'image_split' : 'title_accent';
$hero['text_max_width'] = 'default';
$hero['heading_max_width'] = 'default';
$hero['current_crumb_label'] = 'Advocacy Centre';
$hero['background_color'] = '#C6ECF4';
$hero['accent_color'] = '#6FC9C0';

$flexi = [
    $hero,
    matrix_orlaith_content_row('', $body, 'white'),
    matrix_orlaith_video_row(
        'Advocacy and Education strategy',
        '',
        [
            [
                'url' => 'https://www.youtube.com/watch?v=b2bj9aNjxRE',
                'caption' => 'See how the establishment of an interactive Education Centre and ongoing development of our Advocacy Centre will help us to empower more people to live a mentally healthy life.',
                'poster' => $hero_image,
            ],
        ]
    ),
    matrix_orlaith_useful_links_row([
        'More on our advocacy' => $urls['advocacy'],
        'Our policies and publications' => $urls['policies'],
        'Our research' => $urls['research'],
        'Service user engagement' => $urls['participation'],
        'Our history' => $urls['history'],
        'Our strategy' => $urls['strategy'],
    ]),
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Advocacy Centre',
]);

update_field('flexible_content_blocks', $flexi, $post_id);

update_post_meta($post_id, '_yoast_wpseo_title', 'Advocacy Centre | St Patrick’s Mental Health Services');
update_post_meta($post_id, '_yoast_wpseo_metadesc', 'Learn about the Advocacy Centre at St Patrick’s Mental Health Services and how it advances rights, education, and partnerships for mentally healthy living.');

WP_CLI::success('Rebuilt Advocacy Centre ' . $post_id . ' → ' . get_permalink($post_id));
foreach (get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $img = $row['hero_image'] ?? null;
    $img_id = is_array($img) ? (int) ($img['ID'] ?? $img['id'] ?? 0) : (int) $img;
    WP_CLI::log(sprintf(
        '[%d] %s %s img=%d',
        $i,
        $row['acf_fc_layout'] ?? '',
        $row['heading'] ?? '',
        $img_id
    ));
}
