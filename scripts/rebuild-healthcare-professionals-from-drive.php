<?php

/**
 * Rebuild Healthcare Professionals pages that have Drive Library 3 docs.
 *
 * Sources (only folders with real docs — others are empty drop placeholders):
 * - Make a referral/Make a referral.docx → #200 /make-a-referral/
 * - Contact numbers/Key contacts.docx → #279 /healthcare-professionals/contact-numbers/
 * - Involuntary admissions/Involuntary admissions.docx → #3945
 * - Training Centre/Training Centre text content.docx + GP Training Centre.png → #275
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/rebuild-healthcare-professionals-from-drive.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$home = untrailingslashit(home_url('/'));
$theme = get_template_directory();
$drive = $theme . '/old/content/SPMHS-Content-Gathering-Library 3/02-Page-content/Healthcare Professionals';

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$a = static function (string $url, string $label, string $target = '_blank'): string {
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
$ol = static function (array $items): string {
    $html = '<ol>';
    foreach ($items as $item) {
        $html .= '<li>' . $item . '</li>';
    }

    return $html . '</ol>';
};

$contact_item = static function (
    string $title,
    string $phone = '',
    string $email = '',
    bool $starts_open = false
): array {
    return [
        'title' => $title,
        'starts_open' => $starts_open ? 1 : 0,
        'bullet_items' => [],
        'phone' => $phone,
        'email' => $email,
    ];
};

$sideload_drive_image = static function (string $src, int $post_id, string $meta_key, string $title) use ($theme): int {
    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => [[
            'key' => '_matrix_drive_source',
            'value' => $meta_key,
        ]],
    ]);
    if ($existing !== []) {
        return (int) $existing[0];
    }

    if (! is_readable($src)) {
        return 0;
    }

    $out = '/tmp/' . sanitize_file_name(pathinfo($meta_key, PATHINFO_FILENAME)) . '.jpg';
    $converted = false;
    if (function_exists('exec')) {
        exec('sips -s format jpeg -Z 2000 ' . escapeshellarg($src) . ' --out ' . escapeshellarg($out) . ' 2>/dev/null', $ignored, $code);
        $converted = ($code === 0 && is_readable($out));
    }
    $file_path = $converted ? $out : $src;
    $file_name = $converted ? basename($out) : basename($src);
    $tmp = wp_tempnam($file_name);
    if (! $tmp || ! copy($file_path, $tmp)) {
        return 0;
    }

    $sideloaded = media_handle_sideload([
        'name' => $file_name,
        'tmp_name' => $tmp,
    ], $post_id, $title);

    if (is_wp_error($sideloaded)) {
        return 0;
    }

    update_post_meta((int) $sideloaded, '_matrix_drive_source', $meta_key);

    return (int) $sideloaded;
};

$adult_form = wp_get_attachment_url(3913) ?: '';
$adolescent_form = wp_get_attachment_url(3914) ?: '';
$day_programmes_pdf = wp_get_attachment_url(3918) ?: '';
$referral_brochure = wp_get_attachment_url(977) ?: '';
$form5_guide = wp_get_attachment_url(855) ?: '';

$urls = [
    'make_referral' => $home . '/make-a-referral/',
    'adult_inpatient' => $home . '/healthcare-professionals/refer-an-adult-for-inpatient-care/',
    'adolescent_inpatient' => $home . '/healthcare-professionals/refer-an-adolescent-for-inpatient-care/',
    'outpatient' => $home . '/healthcare-professionals/refer-for-outpatient-care/',
    'day_programme' => $home . '/healthcare-professionals/refer-to-a-day-programme/',
    'at_home' => $home . '/healthcare-professionals/refer-to-the-st-patricks-at-home-service/',
    'involuntary' => $home . '/healthcare-professionals/involuntary-admissions/',
    'day_programmes' => $home . '/what-we-offer/day-programmes/',
    'how_to_access' => $home . '/how-to-access/',
    'inpatient_access' => $home . '/inpatient-hospital-care-how-to-access/',
    'clinician_insights' => $home . '/healthcare-professionals/clinician-insights/',
    'webinars' => $home . '/healthcare-professionals/webinars-events/',
    'hp_faqs' => $home . '/healthcare-professionals/frequently-asked-questions/',
    'contact_numbers' => $home . '/healthcare-professionals/contact-numbers/',
    'training' => $home . '/healthcare-professionals/training-centre/',
    'privacy' => matrix_orlaith_permalink('data-protection-policy'),
    'healthmail' => 'https://www.healthmail.ie/',
    'healthmail_register' => 'https://www.healthmail.ie/registration.cfm',
    'healthlink' => 'https://www.healthlink.ie/',
    'mhc_forms' => 'https://www.mhcirl.ie/for_H_Prof/Forms/',
    'mha_2001' => 'http://www.irishstatutebook.ie/eli/2001/act/25/enacted/en/html',
    'icgp_mha' => 'https://www.icgp.ie/go/courses/mental_health/mental_health_act_2001_faq',
    'youtube' => 'https://www.youtube.com/channel/UCOI_6n3TndtZlW34C4RCdQw',
];

// ---------------------------------------------------------------------------
// 1) Make a referral
// ---------------------------------------------------------------------------
$referral_id = (int) (get_page_by_path('make-a-referral')?->ID ?? 0);
if ($referral_id <= 0) {
    WP_CLI::error('Missing make-a-referral page');
}

$referral_hero = matrix_orlaith_find_image(3992, 'Adult referral healthcare professionals');
if ($referral_hero <= 0) {
    $referral_hero = matrix_orlaith_find_image(962, 'st-patricks-mental-health-services-gp-referral-banner-min');
}

$accept = $p('We accept referrals for a wide range of mental health difficulties, including:')
    . $ul([
        'Addiction and dual diagnosis',
        'Anxiety disorders',
        'Bipolar disorder',
        'Depression',
        'Eating disorders',
        'Mood disorders',
        'Obsessive Compulsive Disorder (OCD)',
        'Psychosis',
        'Young adult mental health difficulties',
        'Older adult mental health difficulties.',
    ])
    . $p('All referrals are carefully reviewed to determine whether we can offer an appropriate service and, if so, which service best meets the person\'s needs.');

$how = $p('Referrals can be made by GPs, psychiatrists, community mental health teams and some other healthcare professionals outside of SPMHS.')
    . $p('Referrals can be made through several pathways, including:')
    . '<h3>eReferrals</h3>'
    . $p('eReferrals can be sent electronically through '
        . $a($urls['healthlink'], 'Healthlink')
        . '.')
    . $p('To submit an eReferral:')
    . $ol([
        'Log into the relevant system',
        'Select “St Patrick’s Mental Health Services” from the private hospital list',
        'Choose "Psychiatric Referral Service" from the list of departments.',
    ])
    . $p('After this is completed, our Referral and Assessment Service team will respond.')
    . '<h3>Referral forms</h3>'
    . $p('We provide referral forms for our services. You can '
        . $a($adult_form, 'download our adult referral form here')
        . ', while the '
        . $a($adolescent_form, 'adolescent referral form is available here')
        . '. Please ensure to complete the form in full before submitting it.')
    . $p('You can send completed referral forms through Healthmail, the secure clinical email system from the Health Service Executive (HSE), by emailing our Referral and Assessment Service at '
        . $a('mailto:referrals@stpatricks.ie', 'referrals@stpatricks.ie', '')
        . '.')
    . $p('Our Referral and Assessment Service will either contact your patient directly or get in touch with you to discuss the referral.')
    . $p('For further enquiries, you can contact the Referral and Assessment Service on '
        . $a('tel:012493635', '01 249 3635', '')
        . '. If you cannot reach the Referral and Assessment Service by phone, please leave a voicemail. A member of the team will return your call as soon as possible. Messages left outside office hours will be returned on the next working day.');

$services = $p('We provide:')
    . $ul([
        'Inpatient care',
        'Homecare services',
        'Outpatient care, including one-to-one therapies',
        'Day programmes.',
    ])
    . $p('You can learn more about our services and referral pathways in our '
        . $a($referral_brochure, 'referral brochure here')
        . '.')
    . $p('Please note that some of our day programmes accept internal referrals only. You can see '
        . $a($urls['day_programmes'], 'here a list of our day programmes', '')
        . ' accepting direct referrals from GPs or other healthcare professionals, or download a brochure of our day programmes accepting direct referrals '
        . $a($day_programmes_pdf, 'here')
        . '. We provide specialist treatment for a range of mental health difficulties, including addiction and dual diagnosis. However, some addiction services are not available through SPMHS.');

$after = $p('Once a referral is received, our Referral and Assessment Service team may contact the person or referrer to discuss the referral further. The referral is then carefully reviewed internally. The person\'s needs, clinical presentation and level of urgency are considered when determining the most appropriate next steps. Some services have waiting lists in place. While we work to ensure people access treatment as quickly as possible, there may be a period between referral and the start of treatment.')
    . '<h3>Outpatient assessments</h3>'
    . $p('Some people referred to our outpatient clinics, the Dean Clinics, receive a free Prompt Assessment of Needs (PAON) with an experienced mental health nurse. Assessments take place remotely using the person\'s preferred method of communication, including:')
    . $ul([
        'Telephone',
        'Video call',
        'Microsoft Teams',
        'FaceTime',
        'Other online platforms.',
    ])
    . $p('The PAON helps identify the person\'s needs and supports referral to the most appropriate service or programme. Please note that PAON assessments apply to Dean Clinic referrals only. Referrals for other services are assessed separately.');

$clinical = $p('We are securely linked to Healthmail, the HSE\'s secure email service, which enables healthcare professionals to safely send and receive clinical information. Healthcare professionals with a Healthmail account can use this service to securely share clinical documentation, including prescriptions and referral information. If you do not already have a Healthmail account, you can '
        . $a($urls['healthmail_register'], 'apply for one through the Healthmail service')
        . '.');

$policies = $p('Our admissions policies and procedures are governed by the '
        . $a($urls['mha_2001'], 'Mental Health Act 2001')
        . '. If you have questions about making a referral or accessing our services, please contact the Referral and Assessment Service. You can also direct patients to our information on '
        . $a($urls['how_to_access'], 'accessing services and assessments', '')
        . ' for further guidance on the referral process.');

$pathway_cards = [];
$tones = ['bg1', 'bg2', 'bg3', 'bg4'];
$pathway_pages = [
    [$urls['adult_inpatient'], 'Refer an adult for inpatient care', 'Guidance for referring adults to inpatient care at St Patrick’s.'],
    [$urls['adolescent_inpatient'], 'Refer an adolescent for inpatient care', 'Referral pathway for Willow Grove Adolescent Unit.'],
    [$urls['outpatient'], 'Refer for Dean Clinics', 'Outpatient referral guidance for our Dean Clinics.'],
    [$urls['day_programme'], 'Refer for day services', 'How to refer to day programmes accepting direct referrals.'],
    [$urls['at_home'], 'Refer to St Patrick’s at Home', 'Homecare referral information for adults and adolescents.'],
    [$urls['involuntary'], 'Involuntary admissions', 'Support for GPs referring for involuntary admission.'],
];
foreach ($pathway_pages as $i => [$url, $title, $desc]) {
    $pathway_cards[] = [
        'icon' => '',
        'image_url' => '',
        'title' => $title,
        'description' => $desc,
        'link' => [
            'title' => $title,
            'url' => $url,
            'target' => '',
        ],
        'card_tone' => $tones[$i % count($tones)],
    ];
}

$referral_rows = [
    matrix_orlaith_hero_row(
        'Make a referral',
        $p('At St Patrick’s Mental Health Services (SPMHS), we provide mental health services for adolescents and adults through inpatient care, homecare services, outpatient clinics and day programmes.'),
        $referral_hero
    ),
    matrix_orlaith_content_row('What do you accept referrals for?', $accept, 'white'),
    matrix_orlaith_content_row('How can I make a referral?', $how, 'cream', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('Adult referral form (PDF)', $adult_form, '_blank'),
        'primary_button_variant' => 'filled',
        'secondary_button' => matrix_orlaith_button('Adolescent referral form (PDF)', $adolescent_form, '_blank'),
        'secondary_button_variant' => 'outline',
    ]),
    matrix_orlaith_content_row('What services can I refer to?', $services, 'white'),
    matrix_orlaith_about_links_grid_row(
        'Explore our referral pathways',
        $pathway_cards,
        [
            'intro_text' => 'Find detailed guidance for each of our referral and admissions pathways.',
            'layout_style' => 'compact_row',
            'columns' => '2',
        ]
    ),
    matrix_orlaith_content_row('What happens after a referral is received?', $after, 'cream'),
    matrix_orlaith_content_row('Sending clinical information', $clinical, 'white', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('Register for Healthmail', $urls['healthmail_register'], '_blank'),
        'primary_button_variant' => 'filled',
    ]),
    matrix_orlaith_content_row('Referral policies and further information', $policies, 'cream', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('Key contact numbers', $urls['contact_numbers']),
        'primary_button_variant' => 'filled',
        'secondary_button' => matrix_orlaith_button('Call 01 249 3635', 'tel:012493635'),
        'secondary_button_variant' => 'outline',
    ]),
];

matrix_orlaith_save_page($referral_id, $referral_rows, true, $referral_hero);
matrix_orlaith_set_seo(
    $referral_id,
    'Make a referral | St Patrick\'s Mental Health Services',
    'Learn how to refer a patient to St Patrick\'s Mental Health Services, including referral pathways, assessments, day programmes and clinical information requirements.'
);
WP_CLI::success('Make a referral → ' . get_permalink($referral_id));

// ---------------------------------------------------------------------------
// 2) Contact numbers (Key contacts)
// ---------------------------------------------------------------------------
$contacts_id = (int) (get_page_by_path('healthcare-professionals/contact-numbers')?->ID ?? 0);
if ($contacts_id <= 0) {
    WP_CLI::error('Missing contact-numbers page');
}

$contacts_hero = (int) get_post_thumbnail_id($contacts_id);
if ($contacts_hero <= 0) {
    $contacts_hero = matrix_orlaith_find_image(3992, 'Adult referral healthcare professionals');
}

$col1 = [
    $contact_item('General enquiries', '01 249 3200', '', true),
    $contact_item('Referral and Assessment Service', '01 249 3635'),
    $contact_item('St Patrick’s University Hospital, Dublin 8', '01 249 3200'),
    $contact_item('St Patrick’s Hospital, Lucan', '01 621 8200'),
];
$col2 = [
    $contact_item('Willow Grove Adolescent Unit', '01 249 3687'),
    $contact_item('Pharmacy', '01 249 3256'),
    $contact_item('Dean Clinic, Dublin 8', '01 249 3590'),
    $contact_item('Dean Clinic, Cork', '01 249 3502'),
];
$col3 = [
    $contact_item('Dean Clinic, Galway', '091 513 540'),
    $contact_item('Dean Clinic, Lucan', '01 249 3590'),
    $contact_item('Clinical Governance Office (Feedback and Complaints)', '', 'clinicalgovernance@stpatricks.ie'),
    $contact_item('Human Resources (HR)', '', 'hr@stpatricks.ie'),
];

$faq_items = [
    'How do I make a referral?' => $p('All referrals should be made to the Referral and Assessment Service. Detailed referral guidance is available on our '
        . $a($urls['make_referral'], 'referral page', '')
        . ' on the website.'),
    'Who should I contact about a current service user?' => $p('If the service user is currently under our care, please contact the relevant service directly, such as the inpatient unit, homecare team or Dean Clinic involved in their treatment.'),
    'Where can I find referral criteria?' => $p('Referral criteria and required documentation are available on the '
        . $a($urls['make_referral'], 'referral section of our website', '')
        . '.'),
    'How do I provide feedback or raise a concern?' => $p('The Clinical Governance Office manages feedback and complaints. Healthcare professionals can contact the office directly at '
        . $a('mailto:clinicalgovernance@stpatricks.ie', 'clinicalgovernance@stpatricks.ie', '')
        . '.'),
];

$contacts_rows = [
    matrix_orlaith_hero_row(
        'Key contact information',
        $p('We work closely with healthcare professionals across Ireland and are committed to making it easy to connect with the right team.')
            . $p('Below you will find key contact details for departments and services across St Patrick’s Mental Health Services. If you are unsure who to contact, our main switchboard will be happy to direct your query.'),
        $contacts_hero
    ),
    [
        'acf_fc_layout' => 'key_contact_info',
        'columns' => [
            ['items' => $col1],
            ['items' => $col2],
            ['items' => $col3],
        ],
        'section_background' => '#FFFFFF',
        'closed_panel_background' => '#FBFAF7',
        'open_panel_background' => 'linear-gradient(-79.46deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
    ],
    matrix_orlaith_accordion_row($faq_items, 'default', 'Frequently Asked Questions (FAQs)'),
];

wp_update_post([
    'ID' => $contacts_id,
    'post_title' => 'Contact numbers',
]);
matrix_orlaith_save_page($contacts_id, $contacts_rows, true, $contacts_hero);
matrix_orlaith_set_seo(
    $contacts_id,
    'Key Contacts for Healthcare Professionals | St Patrick’s Mental Health Services',
    'Contact details for key departments and services at St Patrick’s Mental Health Services for healthcare professionals.'
);
WP_CLI::success('Contact numbers → ' . get_permalink($contacts_id));

// ---------------------------------------------------------------------------
// 3) Involuntary admissions
// ---------------------------------------------------------------------------
$inv_id = (int) (get_page_by_path('healthcare-professionals/involuntary-admissions')?->ID ?? 0);
if ($inv_id <= 0) {
    WP_CLI::error('Missing involuntary-admissions page');
}

$inv_hero = matrix_orlaith_find_image(963, 'involuntary-admissions-st-patricks-mental-health-services');
if ($inv_hero <= 0) {
    $inv_hero = matrix_orlaith_find_image(854, 'involuntary-admissions-st-patricks-mental-health-services-banner');
}

$what = $p('An involuntary admission is when a person is admitted to hospital for mental health treatment against their own wishes.')
    . $p('Involuntary admissions are covered under the Mental Health Act 2001. Under this law, there are very strict conditions that must apply before a person can be involuntarily admitted to hospital.')
    . $p('You can find out more about the laws and conditions for involuntary admissions '
        . $a($urls['mha_2001'], 'here')
        . '.')
    . $p('As an approved centre regulated by the Mental Health Commission (MHC), mental healthcare and treatment can be provided for involuntary adult service users in St Patrick’s University Hospital (SPUH).')
    . $p('Involuntary admissions take place with the person’s best interests at heart, and care is delivered in a way that:')
    . $ul([
        'respects the service user’s dignity, ability to consent, and right to freedom',
        'is fully compliant with the legal and procedural requirements set out in law.',
    ]);

$how_intro = $p('Certain people, as set out in law, can apply to a GP for another person to be medically assessed for involuntary admission. They must have seen the person within 48 hours before making the application.')
    . $p('The applicant must have completed either Form 1, 2, 3 or 4 (“Application to a Registered Medical Practitioner”) from the MHC. The form the applicant completes depends on their relationship to the person.')
    . $p('As a GP, when you get a completed application form, you must see and examine the person within 24 hours of receiving the form.')
    . $p('If you feel the person requires mental health treatment and wish to refer them to SPUH, please make sure you have access to the following two forms and then take the steps outlined below.')
    . $ul([
        $a($urls['mhc_forms'], 'Form 5 (“Recommendation by a Registered Medical Practitioner”) from the MHC'),
        'The St Patrick’s Mental Health Services (SPMHS) '
            . $a($adult_form, 'adult referral form')
            . '.',
    ]);

$steps = [
    'Contact SPUH' => $p('You can contact our Referral and Assessment Service by phoning '
        . $a('tel:012493635', '01 249 3635', '')
        . ' or '
        . $a('tel:012493640', '01 249 3640', '')
        . ' between 9am and 5pm, Monday to Friday. Outside of these hours, you can call '
        . $a('tel:012493200', '01 249 3200', '')
        . ' and you will be put in touch with the Assistant Director of Nursing (ADON). The ADON may put you in touch with the on-call registrar if needed.'),
    'Check all forms' => $p('You must ensure that the relevant application form (Form 1, 2, 3 or 4), recommendation form (Form 5) and SPMHS referral form are completed fully and correctly.')
        . $p('We want to make this process as calm and straightforward for your patient as we possibly can: if forms are not completed correctly or the wrong information is provided, it can mean that we are not able to process the application and admit the person, or can lead to the person’s unlawful detention in hospital.')
        . $p('The Referrals and Assessment Service team, ADON or on-call registrar can help with questions you have about completing Form 5 or the SPMHS referral form to ensure all required information is present and correct.')
        . $p('The Irish College of General Practitioners (ICGP) also provides a guidance document on avoiding common errors when using Form 5, which may be helpful to review'
            . ($form5_guide !== '' ? (' — ' . $a($form5_guide, 'download the guidance (PDF)')) : '')
            . '. See also the '
            . $a($urls['icgp_mha'], 'ICGP Mental Health Act FAQ')
            . '.'),
    'Send all forms' => $p('You will need to send the completed application form, recommendation form, and SPMHS referral form to SPUH. The forms should only be sent by secure email by using Healthmail; the email address to contact through Healthmail is '
        . $a('mailto:referrals@stpatricks.ie', 'referrals@stpatricks.ie', '')
        . '.')
        . $p('Please note that, in the person’s best interest, it is vital that forms are sent before you advise your patient to come to SPUH to allow time for our staff to review the forms and ensure they are completed correctly. We will contact you if we have any concerns or queries about the forms which need to be addressed before you advise your patient to come to the hospital.')
        . $p('Please also be aware that there is a seven-day limit on Form 5: we cannot process an application if it has been more than seven days since you completed Form 5 when we get the form.'),
    'Support your patient’s admission to hospital' => $p('You can request that SPUH arranges for assistance in bringing your patient to hospital: if this is needed, please let us know through Healthmail.')
        . $p('You must ensure that original copies of the correctly completed MHC forms (Forms 1, 2, 3 or 4 and Form 5) accompany your patient to SPUH with a family member or with the team assisting their admission.')
        . $p('Without these forms, we are unable to process the application and admit the person.'),
];

$next = $p('When a person is admitted to SPUH under an involuntary admission, a consultant psychiatrist will examine your patient within 24 hours of their admission. If they feel your patient requires care in hospital but your patient is not willing for this, your patient will be admitted against their will as set out in law.')
    . $p('Please do not hesitate to contact us if you have any queries about the involuntary admissions process at any stage.');

$inv_rows = [
    matrix_orlaith_hero_row(
        'Involuntary admissions',
        $p('Here, we provide information and guidance to support GPs who need to refer a patient for an involuntary admission so that the process is as smooth and timely as it can be and the person can get the care they need as soon as possible.'),
        $inv_hero
    ),
    matrix_orlaith_content_row('What is an involuntary admission?', $what, 'white'),
    matrix_orlaith_content_row('How are referrals for involuntary admissions made?', $how_intro, 'cream', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('MHC forms', $urls['mhc_forms'], '_blank'),
        'primary_button_variant' => 'filled',
        'secondary_button' => matrix_orlaith_button('Adult referral form (PDF)', $adult_form, '_blank'),
        'secondary_button_variant' => 'outline',
    ]),
    matrix_orlaith_accordion_row($steps, 'default', 'Steps for GPs'),
    matrix_orlaith_content_row('Next steps and queries', $next, 'white', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('Key contact numbers', $urls['contact_numbers']),
        'primary_button_variant' => 'filled',
        'secondary_button' => matrix_orlaith_button('Healthcare FAQs', $urls['hp_faqs']),
        'secondary_button_variant' => 'outline',
    ]),
];

wp_update_post([
    'ID' => $inv_id,
    'post_title' => 'Involuntary admissions',
]);
matrix_orlaith_save_page($inv_id, $inv_rows, true, $inv_hero);
matrix_orlaith_set_seo(
    $inv_id,
    'Involuntary admissions | St Patrick’s Mental Health Services',
    'Guidance for GPs on referring a patient for involuntary admission to St Patrick’s University Hospital under the Mental Health Act 2001.'
);
WP_CLI::success('Involuntary admissions → ' . get_permalink($inv_id));

// ---------------------------------------------------------------------------
// 4) Training Centre (GP education supports from HP Drive doc)
// ---------------------------------------------------------------------------
$training_id = (int) (get_page_by_path('healthcare-professionals/training-centre')?->ID ?? 0);
if ($training_id <= 0) {
    WP_CLI::error('Missing training-centre page');
}

$training_hero = $sideload_drive_image(
    $drive . '/Training Centre/GP Training Centre.png',
    $training_id,
    'GP Training Centre.png',
    'GP Training Centre'
);
if ($training_hero <= 0) {
    $training_hero = matrix_orlaith_find_image(1911, 'future-in-mind-research-training-poster.jpg');
}

$poster = matrix_orlaith_find_image(4091, 'Research and Training Video.png');
if ($poster <= 0) {
    $poster = $training_hero;
}

$training_intro = $p('St Patrick’s Mental Health Services (SPMHS) offers a wide range of mental health education supports for GPs and healthcare professionals.')
    . $p('We have developed a range of resources to help GPs in their practice with patients who present with mental health difficulties.')
    . $p('If you have any questions about mental health information supports or continuous professional development (CPD) opportunities for GPs, please email '
        . $a('mailto:communications@stpatricks.ie', 'communications@stpatricks.ie', '')
        . '.');

$webinars = $p('Each year, we host a GP Webinar Series. These webinars are presented by clinicians from across our services, who, in each webinar, focus on a mental health topic relevant to GP practice. Each webinar also includes a question and answer session with the presenting clinicians.')
    . $p('The webinars are recognised for Accredited CE (CE) by the Irish College of General Practitioners (ICGP), with available points confirmed ahead of each webinar. Please note that Accredited CE is only available to those who attend the live webinar.')
    . $p('Registration for the webinars is free.')
    . $p('Check our '
        . $a($urls['webinars'], 'events calendar', '')
        . ' to see and register for upcoming GP Webinars.');

$films = $p('We also host a range of on-demand mental health information films for GPs.')
    . $p('These films cover a wide range of mental health topics relevant to the GP surgery, including:')
    . $ul([
        'recognising and assessing different mental health difficulties',
        'supporting people living with mental health difficulties',
        'exploring different types of therapy, medication management and treatment approaches.',
    ])
    . $p('Visit our YouTube channel '
        . $a($urls['youtube'], 'here')
        . ' to get the full playlist, or find out more about and watch the films '
        . $a($urls['webinars'], 'here', '')
        . '.');

$insights = $p('Clinical staff from across our mental health services regularly contribute to our specialist blogs and articles for GPs and healthcare professionals. These articles cover diverse mental health topics to support patients presenting with mental health difficulties. '
        . $a($urls['clinician_insights'], 'Read our clinician insights here', '')
        . '.');

$newsletter_intro = $p('We issue a quarterly digital newsletter especially tailored to GPs, covering mental health news, research findings, service updates and clinical insights. Sign up using the form below.');

$training_rows = [
    matrix_orlaith_hero_row('Training Centre', $training_intro, $training_hero),
    matrix_orlaith_content_row('GP Webinar Series', $webinars, 'white', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('Upcoming webinars and events', $urls['webinars']),
        'primary_button_variant' => 'filled',
    ]),
    matrix_orlaith_content_row('Mental health films for GPs', $films, 'cream', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('SPMHS on YouTube', $urls['youtube'], '_blank'),
        'primary_button_variant' => 'filled',
    ]),
    matrix_orlaith_content_row('Clinician insights', $insights, 'white', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('Read clinician insights', $urls['clinician_insights']),
        'primary_button_variant' => 'filled',
    ]),
    matrix_orlaith_video_row(
        'Research and training',
        $p('Watch how our Academic Institute and Training Centre support staff and organisations working in mental health.'),
        [[
            'url' => 'https://www.youtube.com/watch?v=AjJQxOrmv1o',
            'caption' => 'Learn more about our Academic Institute and our commitment to supporting staff and organisations working in mental health through our new training centre.',
            'poster' => $poster,
        ]]
    ),
    matrix_orlaith_contact_form_row('GP newsletter', $newsletter_intro, [
        'form_name' => 'GP newsletter signup',
        'email_subject' => 'GP newsletter – new signup',
        'recipient_email' => 'communications@stpatricks.ie',
        'success_message' => 'Thanks! You are signed up for the GP newsletter.',
        'submit_label' => 'Sign up',
        'background_type' => 'cream',
        'privacy_policy_link' => [
            'title' => 'Privacy Notice',
            'url' => $urls['privacy'],
            'target' => '_blank',
        ],
    ]),
];

wp_update_post([
    'ID' => $training_id,
    'post_title' => 'Training Centre',
]);
matrix_orlaith_save_page($training_id, $training_rows, true, $training_hero);
matrix_orlaith_set_seo(
    $training_id,
    'Training Centre | St Patrick’s Mental Health Services',
    'GP webinars, mental health films, clinician insights and newsletter updates from the St Patrick’s Mental Health Services Training Centre.'
);
WP_CLI::success('Training Centre → ' . get_permalink($training_id));

WP_CLI::log('');
WP_CLI::log('Skipped empty Drive folders (no draft docs yet):');
WP_CLI::log('- Healthcare professionals (landing)');
WP_CLI::log('- Clinician insights');
WP_CLI::log('- Frequently Asked Questions');
WP_CLI::log('- Webinars and events');
WP_CLI::log('- Refer an adult / adolescent / Dean Clinics / day services / St Patrick’s at Home');
