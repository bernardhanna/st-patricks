<?php

/**
 * Rebuild Recruitment and useful information from Drive Library 3.
 *
 * Sources:
 * - Recruitment and useful information.docx
 * - Hero image.jpg
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-recruitment-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$post_id = (int) (get_page_by_path('recruitment-and-useful-information')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find recruitment-and-useful-information');
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

$urls = [
    'careers' => $home . '/careers/',
    'apply' => $home . '/recruitment-and-useful-information/how-to-apply-for-a-role/',
    'directions' => $home . '/directions-and-parking/',
    'spuh' => $home . '/locations/st-patricks-university-hospital/',
    'team' => $home . '/about-us/our-team/',
];

$img_id = 0;
$existing = get_posts([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'posts_per_page' => 1,
    'fields' => 'ids',
    'meta_query' => [[
        'key' => '_matrix_drive_source',
        'value' => 'Hero image.jpg',
    ]],
]);
if ($existing !== []) {
    $img_id = (int) $existing[0];
} else {
    $src = get_template_directory()
        . '/old/content/SPMHS-Content-Gathering-Library 3/02-Page-content/About Us/Recruitment and useful information/Hero image.jpg';
    if (is_readable($src)) {
        $tmp = wp_tempnam('recruitment-hero.jpg');
        if ($tmp && copy($src, $tmp)) {
            $sideloaded = media_handle_sideload([
                'name' => 'recruitment-hero.jpg',
                'tmp_name' => $tmp,
            ], $post_id, 'Recruitment at St Patrick\'s');
            if (! is_wp_error($sideloaded)) {
                $img_id = (int) $sideloaded;
                update_post_meta($img_id, '_matrix_drive_source', 'Hero image.jpg');
            }
        }
    }
}
if ($img_id <= 0) {
    $img_id = (int) get_post_thumbnail_id($post_id);
}
if ($img_id <= 0) {
    $img_id = 4027;
}

$hero_intro = $p("At St Patrick's Mental Health Services (SPMHS), we are always looking for talented and motivated people who want to make a positive difference in mental healthcare.")
    . $p('As Ireland’s largest independent, not-for-profit mental health service provider, we offer opportunities across a wide range of clinical and non-clinical roles in a supportive and collaborative environment.');

$why_join = $p('By joining SPMHS, you will become part of a team with more than 250 years of experience in mental healthcare and a strong commitment to innovation, quality care and human rights.')
    . $p('We offer:')
    . $ul([
        'Meaningful and rewarding work that supports people experiencing mental health difficulties',
        'Opportunities to work as part of multidisciplinary teams',
        'A culture of learning, collaboration and continuous improvement',
        'Innovative services and approaches to mental healthcare',
        'Career development opportunities across a wide range of roles',
        'A workplace that values staff wellbeing and professional growth',
    ]);

$applying = $p('You can view our current vacancies through our Careers section '
        . $a($urls['careers'], 'here')
        . '.')
    . $p('If a role you are interested in is not currently advertised, you can email your cover letter and CV to '
        . $a('mailto:hr@stpatricks.ie', 'hr@stpatricks.ie')
        . ' for consideration.')
    . $p('If you have any questions about the recruitment process or need support with your application, please contact our Human Resources team:')
    . $p('Email: ' . $a('mailto:hr@stpatricks.ie', 'hr@stpatricks.ie') . '<br>Phone: ' . $a('tel:012493435', '01 249 3435'))
    . $p('Please note that all applications must be submitted online, and paper applications cannot be accepted.');

$interview_intro = $p('If you are invited to interview at SPMHS, we will provide you with all the information you need in advance.');

$interview_accordion = [
    'Who will be on the interview panel?' => $p('All interviews are conducted by a panel, which may include:')
        . $ul([
            'A member of our Human Resources (HR) team',
            'Operational and/or senior management',
            'A service user representative',
            'External expert',
        ])
        . $p('You will be told in advance who will be on your panel.')
        . $p('Some roles may include task-based or skills assessment and may involve more than one interview stage.'),
    'How to prepare' => $p('We recommend that you:')
        . $ul([
            'Read the job description and person specification carefully',
            'Reread your CV',
            'Arrive in plenty of time, and be ready to begin your interview',
            'Dress appropriately for an interview',
        ])
        . $p('Please let us know in advance if you need any specific arrangements to support you at interview.')
        . $p('We also welcome any questions you may have about the role, our organisation, or your future career with us.'),
    'Where interviews take place' => $p('Interviews may take place online or in person.')
        . $p('Online interviews are held through Microsoft Teams. You will receive a link to join your scheduled interview. In-person interviews are usually held at '
            . $a($urls['spuh'], "St Patrick's University Hospital (SPUH)")
            . ', Dublin 8. SPUH is a five-minute walk from Heuston Station and is well served by Dublin Bus and intercounty routes. Visitor parking is also available.')
        . $p('Directions to SPUH will be shared with you ahead of your interview. You can also '
            . $a($urls['directions'], 'see directions and parking here')
            . '.')
        . $p('If you are unable to attend, please contact the HR team in advance so we can rearrange your interview, where possible.'),
];

$cta = matrix_orlaith_content_row(
    'Explore current vacancies',
    $p('View our latest opportunities and find out how you can join the team at St Patrick\'s Mental Health Services.'),
    'cream',
    0,
    'image_left',
    [
        'primary_button' => matrix_orlaith_button('Explore current vacancies', $urls['careers']),
    ]
);

$hero = matrix_orlaith_hero_row("Recruitment at St Patrick's", $hero_intro, $img_id);
$hero['current_crumb_label'] = "Recruitment at St Patrick's";

$flexi = [
    $hero,
    matrix_orlaith_content_row('Why join St Patrick’s Mental Health Services?', $why_join, 'white'),
    matrix_orlaith_content_row('Applying for a role', $applying, 'cream', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('See current vacancies', $urls['careers']),
    ]),
    matrix_orlaith_content_row('Preparing for your interview', $interview_intro, 'white'),
    matrix_orlaith_accordion_row($interview_accordion),
    $cta,
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Recruitment and useful information',
]);

update_post_meta($post_id, '_yoast_wpseo_title', "Recruitment at St Patrick's | St Patrick's Mental Health Services");
update_post_meta($post_id, '_yoast_wpseo_metadesc', "Learn more about recruitment at St Patrick's Mental Health Services, including career development opportunities, staff supports and how to apply for a role.");

update_field('flexible_content_blocks', $flexi, $post_id);
if ($img_id > 0) {
    set_post_thumbnail($post_id, $img_id);
}

WP_CLI::success(sprintf(
    'Rebuilt Recruitment #%d → %s',
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
