<?php

/**
 * Rebuild Staff wellbeing from Drive Library 3.
 *
 * Sources:
 * - Staff wellbeing.docx
 * - Hero image.jpg
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-staff-wellbeing-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$post_id = (int) (get_page_by_path('recruitment-and-useful-information/staff-wellbeing')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find recruitment-and-useful-information/staff-wellbeing');
}

$home = untrailingslashit(home_url('/'));
$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label): string {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};
$ul = static function (array $items): string {
    $html = '<ul>';
    foreach ($items as $item) {
        $html .= '<li>' . $item . '</li>';
    }

    return $html . '</ul>';
};

$careers = $home . '/careers/';
$recruitment = $home . '/recruitment-and-useful-information/';

$img_id = 0;
$existing = get_posts([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'posts_per_page' => 1,
    'fields' => 'ids',
    'meta_query' => [[
        'key' => '_matrix_drive_source',
        'value' => 'Staff wellbeing Hero image.jpg',
    ]],
]);
if ($existing !== []) {
    $img_id = (int) $existing[0];
} else {
    $src = get_template_directory()
        . '/old/content/SPMHS-Content-Gathering-Library 3/02-Page-content/About Us/Staff wellbeing/Hero image.jpg';
    if (is_readable($src)) {
        $tmp = wp_tempnam('staff-wellbeing-hero.jpg');
        if ($tmp && copy($src, $tmp)) {
            $sideloaded = media_handle_sideload([
                'name' => 'staff-wellbeing-hero.jpg',
                'tmp_name' => $tmp,
            ], $post_id, 'Staff wellbeing');
            if (! is_wp_error($sideloaded)) {
                $img_id = (int) $sideloaded;
                update_post_meta($img_id, '_matrix_drive_source', 'Staff wellbeing Hero image.jpg');
            }
        }
    }
}
if ($img_id <= 0) {
    $img_id = (int) get_post_thumbnail_id($post_id);
}
if ($img_id <= 0) {
    $img_id = 4029;
}

$hero_intro = $p("At St Patrick's Mental Health Services (SPMHS), staff wellbeing is a core part of how we support our people and our wider mission of delivering high-quality mental healthcare.")
    . $p('Our staff are our most important asset. Across clinical and non-clinical roles, they support people every day on their journey towards recovery. In turn, we are committed to creating a positive, supportive and rewarding workplace where staff can grow, develop and feel valued in their work.');

$supporting = $p('We provide a wide range of supports and benefits to help staff thrive in their roles and progress in their careers.')
    . $p('These include:')
    . $ul([
        'A generous contributory pension scheme and Employee Assistance Programme',
        'Ongoing training and professional development opportunities',
        'Funding for further education and paid study leave',
        'Opportunities for research, promotion and career progression',
        'Staff recognition and service awards',
        'Flexible working arrangements, including remote and hybrid options where appropriate',
        'A subsidised canteen and onsite gym',
        'Bike to Work and TaxSaver Commuter Ticket schemes',
    ])
    . $p('We were the first hospital and healthcare organisation in Ireland to receive the IBEC KeepWell Mark in 2018 in recognition of our workplace wellbeing initiatives, and we have retained this accreditation annually since.')
    . $p('We also have an active Staff Wellbeing Committee that supports initiatives and activities to promote health and wellbeing across the organisation.');

$culture_intro = $p('We are committed to building a workplace culture that reflects our values of quality care, human rights and innovation.');

$culture_accordion = [
    'A Culture to Value' => $p('Our A Culture to Value campaign highlights and celebrates the contribution of staff across the organisation. It recognises the important role every staff member plays in delivering compassionate, high-quality mental healthcare and in shaping a positive workplace culture.'),
    'CancerCare at work' => $p('SPMHS has adopted the CancerCare at Work Framework in collaboration with Purple House Cancer Support, a non-profit organisation in Ireland that provides support services to people affected by cancer.')
        . $p('Purple House Cancer Support was founded in 1990 by Veronica O’Leary and her husband Brendan following their own experience of cancer and the limited supports available at the time. It was the first community-based cancer support centre in Ireland and continues to provide a range of supports to individuals and families.')
        . $p('Through this framework, we aim to better support employees affected by cancer by promoting awareness, understanding and access to appropriate workplace supports. This includes emotional support, practical assistance and access to complementary therapies where appropriate.'),
];

$working = $p('We are always welcoming of applications from dedicated and motivated people who want to be part of an inclusive and progressive team.')
    . $p('Our roles span a wide range of clinical and non-clinical areas, from entry level through to senior leadership positions.')
    . $p('If you would like to work with us, you can view our latest vacancies through our Careers section '
        . $a($careers, 'here')
        . '. If a role you are interested in is not currently advertised, you can email your CV and cover letter to '
        . $a('mailto:hr@stpatricks.ie', 'hr@stpatricks.ie')
        . '.')
    . $p('For any queries or support with applications, you can contact our HR team:')
    . $p('Email: ' . $a('mailto:hr@stpatricks.ie', 'hr@stpatricks.ie') . '<br>Phone: ' . $a('tel:012493435', '01 249 3435'))
    . $p('Please note that all applications must be submitted online.');

$hero = matrix_orlaith_hero_row('Staff wellbeing', $hero_intro, $img_id);
$hero['current_crumb_label'] = 'Staff wellbeing';

$flexi = [
    $hero,
    matrix_orlaith_content_row('Supporting our people', $supporting, 'white'),
    matrix_orlaith_content_row('Our commitment to a positive workplace culture', $culture_intro, 'cream'),
    matrix_orlaith_accordion_row($culture_accordion),
    matrix_orlaith_content_row('Working with us', $working, 'white', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('See current vacancies', $careers),
    ]),
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Staff wellbeing',
]);

update_post_meta($post_id, '_yoast_wpseo_title', "Staff wellbeing | St Patrick's Mental Health Services");
update_post_meta($post_id, '_yoast_wpseo_metadesc', "Learn about staff wellbeing at St Patrick's Mental Health Services, including supports, workplace initiatives and our commitment to a positive working environment.");

update_field('flexible_content_blocks', $flexi, $post_id);
if ($img_id > 0) {
    set_post_thumbnail($post_id, $img_id);
}

WP_CLI::success(sprintf(
    'Rebuilt Staff wellbeing #%d → %s',
    $post_id,
    get_permalink($post_id)
));

foreach ((array) get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '?');
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
    $extra = '';
    if ($layout === 'content_accordion' && ! empty($row['items'])) {
        $extra = ' | items=' . count($row['items']);
    }
    if (! empty($row['primary_button']['title'])) {
        $extra .= ' | btn=' . $row['primary_button']['title'];
    }
    WP_CLI::log("[{$i}] {$layout} {$heading}{$extra}");
}
