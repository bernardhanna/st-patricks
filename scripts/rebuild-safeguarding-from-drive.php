<?php

/**
 * Rebuild About Us > Safeguarding from Drive Library 3.
 *
 * Sources:
 * - Safeguarding.docx
 * - Safeguarding.png
 *
 * Page: about-us/policies-and-publications/safeguarding
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-safeguarding-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$post_id = (int) (get_page_by_path('about-us/policies-and-publications/safeguarding')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/policies-and-publications/safeguarding');
}

$home = untrailingslashit(home_url('/'));
$live = 'https://www.stpatricks.ie';

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label, string $target = ''): string {
    $attrs = ' href="' . esc_url($url) . '"';
    if ($target !== '') {
        $attrs .= ' target="' . esc_attr($target) . '" rel="noopener noreferrer"';
    }

    return '<a' . $attrs . '>' . esc_html($label) . '</a>';
};
$ul = static function (array $items): string {
    $html = '<ul>';
    foreach ($items as $item) {
        $html .= '<li>' . $item . '</li>';
    }

    return $html . '</ul>';
};

$img_id = 0;
$existing = get_posts([
    'post_type' => 'attachment',
    'post_status' => 'inherit',
    'posts_per_page' => 1,
    'fields' => 'ids',
    'meta_query' => [[
        'key' => '_matrix_drive_source',
        'value' => 'Safeguarding.png',
    ]],
]);
if ($existing !== []) {
    $img_id = (int) $existing[0];
} else {
    $src = get_template_directory()
        . '/old/content/SPMHS-Content-Gathering-Library 3/02-Page-content/About Us/Safeguarding/Safeguarding.png';
    $out = '/tmp/safeguarding-hero.jpg';
    if (is_readable($src)) {
        $converted = false;
        if (function_exists('exec')) {
            exec('sips -s format jpeg -Z 2000 ' . escapeshellarg($src) . ' --out ' . escapeshellarg($out) . ' 2>/dev/null', $ignored, $code);
            $converted = ($code === 0 && is_readable($out));
        }
        $file_path = $converted ? $out : $src;
        $file_name = $converted ? 'safeguarding-hero.jpg' : 'Safeguarding.png';
        $tmp = wp_tempnam($file_name);
        if ($tmp && copy($file_path, $tmp)) {
            $sideloaded = media_handle_sideload([
                'name' => $file_name,
                'tmp_name' => $tmp,
            ], $post_id, 'Safeguarding');
            if (! is_wp_error($sideloaded)) {
                $img_id = (int) $sideloaded;
                update_post_meta($img_id, '_matrix_drive_source', 'Safeguarding.png');
            }
        }
    }
}
if ($img_id <= 0) {
    $img_id = (int) get_post_thumbnail_id($post_id);
}
if ($img_id <= 0) {
    $img_id = 3947;
}

$urls = [
    'policies' => $home . '/about-us/policies-and-publications/',
    'willow' => $home . '/service-users-and-visitors/your-care-with-willow-grove/',
    'willow_loc' => $home . '/locations/willow-grove-adolescent-unit/',
    'dean' => $home . '/what-we-offer/outpatient-care-dean-clinics/',
];

$hero_intro = $p('Here at St Patrick\'s Mental Health Services (SPMHS), it is our policy to safeguard child welfare. See our Child Protection and Welfare Statement and our Child Safeguarding Statements below.');

$protection = $p('It is the policy of SPMHS to safeguard the welfare of all children by protecting them from physical, sexual and emotional harm and from neglect. The welfare of children is of paramount concern.')
    . $p('SPMHS takes all possible care in its recruitment processes to employ people who will not abuse or neglect users of our services. SPMHS provides ongoing training to staff and volunteers so that they are aware of signs and risks of abuse and neglect of children and so that they know the reporting structure of SPMHS.')
    . $p('When reports of concerns or allegations of abuse or neglect, past or present, are made to staff, these will be followed up and, if reasonable grounds for concern are established, these will be reported to Tusla Child and Family Services and/or to An Garda Síochána for assessment and investigation.')
    . $p('Any form of behaviour that harms a child such as neglect, physical, sexual and/or emotional abuse is unacceptable. All concerns will be followed up in a fair and impartial manner and in accordance with the principles of natural justice.')
    . $p('Children have the right to be protected, treated with respect, listened to and have their views taken into consideration, regardless of all other considerations.');

$safeguarding_intro = $p('SPMHS provides mental healthcare to young people between 12 and 17 years of age on an '
        . $a($urls['willow'], 'inpatient')
        . ' basis ('
        . $a($urls['willow_loc'], 'Willow Grove Adolescent Unit')
        . ') and outpatient basis (Willow Grove Adolescent Service; '
        . $a($urls['dean'], 'Dean Clinics')
        . ' in Lucan and Cork).')
    . $p('In accordance with the requirement of the Children\'s Act 2015, Children\'s First: National Guidance for the Protection and Welfare of Children 2017, SPMHS has developed this Child Safeguarding Statement.');

$local_pdf = static function (string $search, string $fallback_path) use ($live): string {
    $found = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        's' => $search,
    ]);
    if ($found !== []) {
        $url = wp_get_attachment_url((int) $found[0]);
        if (is_string($url) && $url !== '') {
            return $url;
        }
    }

    return $live . $fallback_path;
};

$pdf_urls = [
    'spmhs' => $local_pdf('css-overarching', '/media/4215/css-overarching.pdf'),
    'willow' => $local_pdf('css-wgau', '/media/4216/css-wgau.pdf'),
    'dean_dublin' => $local_pdf('css-dean-clinic-dublin', '/media/4217/css-dean-clinic-dublin.pdf'),
    'dean_cork' => $local_pdf('css-dean-clinic-cork', '/media/4218/css-dean-clinic-cork.pdf'),
];

$accordion = [
    'Guiding principles' => $p('SPMHS recognises that the welfare and protection of children is of paramount importance, regardless of all other considerations.')
        . $p('SPMHS commits to the following:')
        . $ul([
            'Fully comply with statutory obligations under the Children First Act 2015',
            'Fully cooperate with the relevant authorities in relation to child protection and welfare',
            'Adopt safe practices to minimise the possibility of harm or accidents happening to children',
            'Fully respect confidentiality requirements in dealing with child protection matters.',
        ]),
    'Designated Liaison Person' => $p('A Designated Liaison Person (DLP) is a resource person for any staff member or volunteer who has child protection concerns. The DLP facilitates liaison with outside agencies. They are responsible for ensuring that reporting procedures within SPMHS are followed. They record all concerns brought to their attention and the actions taken in relation to a concern.')
        . $p('The DLP in SPMHS is Sheila O’Connor ('
            . $a('mailto:soconnor@stpatricks.ie', 'soconnor@stpatricks.ie')
            . ').')
        . $p('The Deputy DLP in SPMHS is Elaine Donnelly (Head of Social Work (CORU 003072); '
            . $a('mailto:edonnelly@stpatricks.ie', 'edonnelly@stpatricks.ie')
            . ').')
        . $p('SPMHS has undertaken a risk assessment to identify any potential harm to a child while availing of our services. This is available in the SPMHS Child Protection Policy.'),
    'Procedures' => $p('SPMHS has a variety of policies and protocols in place to ensure the safeguarding of children and adolescents to whom we provide services (listed in the Child Protection Policy).')
        . $p('On admission to Willow Grove Adolescent Unit, parents and young people receive an information booklet which provides an overview of the service and expectations of service users, their families and staff as part of the service user\'s care and treatment.')
        . $p('SPMHS operates a robust incident reporting system which ensures issues identified are addressed promptly and changes are implemented when required.')
        . $p('All procedures and policies are available on request.'),
    'Implementation' => $p('We recognise that implementation is an ongoing process. SPMHS is committed to the implementation of this Child Safeguarding Statement and the procedures that support our intention to keep children safe from harm while availing of our service.')
        . $p('The below Child Safeguarding Statements were previously reviewed on 21 January 2025. They have been reviewed most recently on 17 September 2025. They will next be reviewed on 17 September 2027, or as soon as practicable after there has been a material change in any matter to which the statement refers.')
        . $ul([
            $a($pdf_urls['spmhs'], 'SPMHS | Child Safeguarding Statement', '_blank'),
            $a($pdf_urls['willow'], 'Willow Grove | Child Safeguarding Statement', '_blank'),
            $a($pdf_urls['dean_dublin'], 'Dean Clinics Dublin | Child Safeguarding Statement', '_blank'),
            $a($pdf_urls['dean_cork'], 'Dean Clinic Cork | Child Safeguarding Statement', '_blank'),
        ]),
];

$flexi = [
    matrix_orlaith_hero_row('Safeguarding', $hero_intro, $img_id),
    matrix_orlaith_content_row('Child Protection and Welfare Statement', $protection, 'white'),
    matrix_orlaith_content_row('Child Safeguarding Statement', $safeguarding_intro, 'cream'),
    matrix_orlaith_accordion_row($accordion),
    matrix_orlaith_useful_links_row([
        'Policies and publications' => 'about-us/policies-and-publications',
        'Your care with Willow Grove' => 'service-users-and-visitors/your-care-with-willow-grove',
    ]),
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Safeguarding',
]);

update_field('flexible_content_blocks', $flexi, $post_id);
if ($img_id > 0) {
    set_post_thumbnail($post_id, $img_id);
}

WP_CLI::success(sprintf(
    'Rebuilt Safeguarding #%d → %s',
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
    if ($layout === 'useful_links' && ! empty($row['links'])) {
        $extra = ' | links=' . count($row['links']);
    }
    WP_CLI::log("[{$i}] {$layout} {$heading}{$extra}");
}
