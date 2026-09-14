<?php

/**
 * Rebuild About Us > Payment from Drive Library 3.
 *
 * Sources:
 * - Payment.docx
 * - Cover for our services.png
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-payment-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$post_id = (int) (get_page_by_path('about-us/payment')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/payment');
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
    'inpatient' => $home . '/inpatient-care/',
    'homecare' => $home . '/what-we-offer/st-patricks-at-home/',
    'outpatient' => $home . '/what-we-offer/outpatient-care-dean-clinics/',
    'day' => $home . '/what-we-offer/day-programmes/',
    'getting_help' => $home . '/getting-help/',
    'insurance_guide' => $home . '/getting-help/insurance-information/health-insurance-plans/',
    'advocacy' => $home . '/about-us/advocacy/',
    'hse_services' => 'https://www2.hse.ie/services/mental-health/',
    'your_mental_health' => 'https://www2.hse.ie/mental-health/',
];

$drive_image = get_template_directory()
    . '/old/content/SPMHS-Content-Gathering-Library 3/02-Page-content/About Us/Payment/Cover for our services.png';

$img_id = (int) get_post_thumbnail_id($post_id);
if ($img_id <= 0 || get_post_type($img_id) !== 'attachment') {
    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => [[
            'key' => '_matrix_drive_source',
            'value' => 'Cover for our services.png',
        ]],
    ]);
    if ($existing !== []) {
        $img_id = (int) $existing[0];
    } elseif (is_readable($drive_image)) {
        $tmp = wp_tempnam('Cover-for-our-services.png');
        if ($tmp && copy($drive_image, $tmp)) {
            $sideloaded = media_handle_sideload([
                'name' => 'Cover-for-our-services.png',
                'tmp_name' => $tmp,
            ], $post_id, 'Cover for our services');
            if (! is_wp_error($sideloaded)) {
                $img_id = (int) $sideloaded;
                update_post_meta($img_id, '_matrix_drive_source', 'Cover for our services.png');
            }
        }
    }
}
if ($img_id <= 0) {
    $img_id = 3949;
}

$hero_intro = $p("At St Patrick's Mental Health Services (SPMHS), we aim to give you as much information as we can to make it as easy as possible for you to avail of our mental health services.");

$covered = $p('We offer a number of care pathways, including '
        . $a($urls['inpatient'], 'inpatient care')
        . ', '
        . $a($urls['homecare'], 'homecare services')
        . ', '
        . $a($urls['outpatient'], 'outpatient care')
        . ', and '
        . $a($urls['day'], 'day programmes')
        . '. We are an independent, not-for-profit organisation, and we do not receive government funding.')
    . $p($a($urls['getting_help'], 'Talking to your GP')
        . ' is the best first step to accessing our mental healthcare and treatment.')
    . $p('Our services can be covered in three key ways:')
    . $ul([
        'private health insurance',
        'self-funding',
        'the Health Service Executive (HSE) in certain circumstances.',
    ])
    . $p('Below, we share more information on these three options.');

$accordion = [
    'Health insurance cover' => $p('We are covered by all health insurance companies in Ireland (Irish Life Health, Laya or VHI). Where it is appropriate, we '
            . $a($urls['advocacy'], 'advocate on behalf of our service users')
            . ' for mental healthcare benefits provided by insurance companies and other related issues in the sector.')
        . $p('We understand that the health insurance market is complex. There is a wide range of policies and plans available, with each one covering different services and having its own terms and conditions. If you are considering a health insurance plan or changing your current plan, '
            . $a($urls['insurance_guide'], 'see our guide to understanding insurance plans here')
            . '.')
        . $p('If you have been referred to our services, your health insurer will be best placed to confirm your level of cover and any specific waiting periods that may currently exist on your policy. You will need the name of the policy holder, policy number and your date of birth before you call your health insurer.')
        . $p('The main insurers’ helplines are below:')
        . $ul([
            'Irish Life | <a href="tel:015625100">01 562 5100</a>',
            'LAYA | <a href="tel:0212022000">021 202 2000</a>',
            'VHI | <a href="tel:0564444444">056 444 4444</a>.',
        ])
        . $p('If you would like to ask any additional questions specific to insurance cover for your SPMHS referral, please '
            . $a('tel:012493533', 'call our Finance Department on 01 249 3533')
            . '. Our team will be happy to assist you.'),
    'Self-funding' => $p('If you do not have private health insurance, our services can be accessed through self-funding, which means you cover the costs of your care and treatment directly.')
        . $p('If you would like to ask any questions specific to the costs of self-funding care and treatment for your SPMHS referral, please call our Finance team, who will be happy to assist you. You can '
            . $a('tel:012493533', 'phone our Finance team on 01 249 3533')
            . '.'),
    'HSE referrals' => $p('We accept referrals from the HSE. If you are under the care of a HSE service, in certain circumstances, your HSE clinical team may find it is appropriate to refer you to our services.')
        . $p('Your GP can refer you to HSE services. You can '
            . $a($urls['hse_services'], 'find out more about HSE services and how to access them here')
            . '. You can also call the YourMentalHealth information line any time on '
            . $a('tel:1800111888', '1800 111 888')
            . ' for more information about your local HSE services.')
        . $p('You can find out more about free mental health supports and resources from the HSE, including helplines and advocacy services provided by organisations which receive funding from the HSE, from '
            . $a($urls['your_mental_health'], 'YourMentalHealth.ie')
            . '.'),
];

$flexi = [
    matrix_orlaith_hero_row('Payment for our services', $hero_intro, $img_id),
    matrix_orlaith_content_row('How our services are covered', $covered, 'white'),
    matrix_orlaith_accordion_row($accordion),
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Payment for our services',
]);

update_field('flexible_content_blocks', $flexi, $post_id);
if ($img_id > 0) {
    set_post_thumbnail($post_id, $img_id);
}

WP_CLI::success(sprintf(
    'Rebuilt Payment #%d → %s',
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
    WP_CLI::log("[{$i}] {$layout} {$heading}{$extra}");
}
