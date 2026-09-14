<?php

/**
 * Rebuild About Us > Our locations from Drive Library 3 content + layout.
 *
 * Sources:
 * - Our locations landing page content.docx
 * - Our locations page layout.docx
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-our-locations-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$post_id = (int) (get_page_by_path('about-us/our-locations')?->ID ?? 0);
if ($post_id === 0) {
    WP_CLI::error('Could not find about-us/our-locations');
}

$home = untrailingslashit(home_url('/'));
$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label): string {
    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};

$urls = [
    'directions' => $home . '/directions-and-parking/',
    'faqs' => $home . '/service-users-and-visitors/frequently-asked-questions-faqs/',
    'referrals' => $home . '/make-a-referral/',
    'spuh' => $home . '/locations/st-patricks-university-hospital/',
    'lucan' => $home . '/locations/st-patricks-hospital-lucan/',
    'willow' => $home . '/locations/willow-grove-adolescent-unit/',
    'dean' => $home . '/what-we-offer/outpatient-care-dean-clinics/',
];

$hero_intro = $p('Find contact details, visiting information and directions for our hospitals and Dean Clinics across Ireland.');

$overview = $p('St Patrick’s Mental Health Services (SPMHS) provides care across a number of locations in Ireland, including our hospitals in Dublin, Willow Grove Adolescent Unit and our network of Dean Clinics. If you are referred to our services and an assessment finds that inpatient care is the most suitable option for you, your care team will discuss where you will receive care based on your individual needs and treatment plan.');

$hospitals = $p('We provide inpatient care for adults at '
        . $a($urls['spuh'], 'St Patrick’s University Hospital')
        . ' in Dublin 8 and '
        . $a($urls['lucan'], 'St Patrick’s Hospital Lucan')
        . ' in County Dublin.')
    . $p('Adolescent inpatient care is provided in '
        . $a($urls['willow'], 'Willow Grove Adolescent Unit')
        . ', located on the grounds of St Patrick’s University Hospital in Dublin 8.');

$outpatient = $p('Our '
        . $a($urls['dean'], 'Dean Clinics')
        . ' are a network of community-based mental health clinics located in Dublin, Cork, Galway and Lucan.');

$access_intro = $p('You can ' . $a($urls['directions'], 'find directions to our locations here') . '.')
    . $p('You will find overviews of the accessibility supports available in our locations below.');

$access_items = [
    'St Patrick’s University Hospital' => $p('Accessibility supports available at St Patrick’s University Hospital include:')
        . '<ul>'
        . '<li>accessible parking spaces near the main entrance</li>'
        . '<li>accessible toilets and a Changing Places facility</li>'
        . '<li>lift access throughout the hospital</li>'
        . '<li>wheelchair assistance, where required</li>'
        . '<li>hearing loops in selected locations</li>'
        . '<li>sign language interpreters, where possible and arranged in advance</li>'
        . '<li>closed captions for online appointments and groups</li>'
        . '<li>access for trained service dogs.</li>'
        . '</ul>',
    'St Patrick’s Hospital Lucan' => $p('Accessibility supports available at St Patrick’s Hospital Lucan include:')
        . '<ul>'
        . '<li>accessible parking</li>'
        . '<li>ramp access to the main entrance</li>'
        . '<li>wheelchair-accessible bedrooms</li>'
        . '<li>accessible visitor toilets</li>'
        . '<li>lift access to upper floors</li>'
        . '<li>sign language interpreters, where possible and arranged in advance</li>'
        . '<li>closed captions for online appointments and groups</li>'
        . '<li>access for trained service dogs.</li>'
        . '</ul>',
    'Willow Grove Adolescent Unit' => $p('Accessibility supports available at Willow Grove include:')
        . '<ul>'
        . '<li>ground-floor access throughout the unit</li>'
        . '<li>a wheelchair-accessible bedroom</li>'
        . '<li>wheelchair access to the gym and basketball court with a wheelchair lift</li>'
        . '<li>sign language interpreters, where possible and arranged in advance</li>'
        . '<li>closed captions for online appointments and groups</li>'
        . '<li>access for trained service dogs.</li>'
        . '</ul>'
        . $p('A member of the team will discuss any support needs or accommodations with the young person and their family before admission.'),
    'Dean Clinics' => $p('Accessibility supports vary by clinic location and may include:')
        . '<ul>'
        . '<li>accessible entrances and facilities</li>'
        . '<li>sign language interpretation, where possible and arranged in advance</li>'
        . '<li>closed captions for online appointments</li>'
        . '<li>access for trained service dogs.</li>'
        . '</ul>'
        . $p('If you require support when attending a Dean Clinic appointment, we encourage you to contact the clinic in advance so that arrangements can be made where possible.'),
];

$queries = $p('For general queries, please call us. For more on mental health and our services, '
    . $a($urls['faqs'], 'see our Frequently Asked Questions (FAQs)')
    . '.');

$referrals = $p('GPs and healthcare professionals can contact our Referrals and Assessments Team for queries on referrals to our services. '
    . $a($urls['referrals'], 'See more from our referrals team')
    . '.');

$directions = $p('Find details of how to travel to and park at our locations.');

$hero = matrix_orlaith_hero_row('Our locations', $hero_intro, 0);
$hero['layout_style'] = 'title_accent';
$hero['text_max_width'] = 'default';
$hero['heading_max_width'] = 'default';
$hero['current_crumb_label'] = 'Our locations';
$hero['background_color'] = '#C6ECF4';
$hero['accent_color'] = '#6FC9C0';
$hero['show_breadcrumbs'] = 1;
$hero['breadcrumb_source'] = 'auto';

$locations_grid = [
    'acf_fc_layout' => 'locations_grid',
    'heading_tag' => 'h2',
    'heading' => 'Find us',
    'source_mode' => 'locations',
    'selected_locations' => [711, 712, 2131, 2132, 2133, 2134, 2135, 2136],
    'cards' => '',
    'footer_button_link' => '',
];

$flexi = [
    $hero,
    matrix_orlaith_content_row('', $overview, 'white'),
    matrix_orlaith_content_row('Our hospitals and inpatient services', $hospitals, 'cream'),
    matrix_orlaith_content_row('Our outpatient services', $outpatient, 'white'),
    $locations_grid,
    matrix_orlaith_content_row('Accessibility', $access_intro, 'cream'),
    matrix_orlaith_accordion_row($access_items),
    matrix_orlaith_content_row('Queries', $queries, 'white', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('Call us', 'tel:012493200'),
        'primary_button_variant' => 'filled',
    ]),
    matrix_orlaith_content_row('Referrals', $referrals, 'cream', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('Call our referrals team', 'tel:012493635'),
        'primary_button_variant' => 'filled',
    ]),
    matrix_orlaith_content_row('Directions and parking', $directions, 'white', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('Get directions here', $urls['directions']),
        'primary_button_variant' => 'filled',
    ]),
];

wp_update_post([
    'ID' => $post_id,
    'post_title' => 'Our locations',
]);

update_field('flexible_content_blocks', $flexi, $post_id);

WP_CLI::success('Rebuilt Our locations ' . $post_id . ' → ' . get_permalink($post_id));
foreach (get_field('flexible_content_blocks', $post_id) as $i => $row) {
    $btn = is_array($row['primary_button'] ?? null) ? ($row['primary_button']['title'] ?? '') : '';
    WP_CLI::log(sprintf(
        '[%d] %s %s%s',
        $i,
        $row['acf_fc_layout'] ?? '',
        $row['heading'] ?? '',
        $btn !== '' ? " | btn={$btn}" : ''
    ));
}
