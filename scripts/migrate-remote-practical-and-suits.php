<?php

/**
 * Migrate missing remote practical-info content and publish SUITS.
 *
 * Creates / what-you-need-to-know-about-remote-care / from live SPMHS content
 * (practical-information-remote-services), copies draft SUITS flexi onto the
 * published Service User IT Support page, and remaps broken links on page 266.
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/migrate-remote-practical-and-suits.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$home = untrailingslashit(home_url('/'));
$a = static function (string $path, string $label) use ($home): string {
    $url = str_starts_with($path, 'http') || str_starts_with($path, 'tel:') || str_starts_with($path, 'mailto:')
        ? $path
        : $home . '/' . ltrim($path, '/');

    return '<a href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
};

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};

// ---------------------------------------------------------------------------
// 1) Publish SUITS content from draft 3419 onto published page 215
// ---------------------------------------------------------------------------
$suits_publish_id = 215;
$suits_draft_id = 3419;
$suits_draft = get_post($suits_draft_id);
$suits_publish = get_post($suits_publish_id);

if (! $suits_publish instanceof WP_Post) {
    WP_CLI::error('Published SUITS page 215 not found');
}

$suits_rows = get_field('flexible_content_blocks', $suits_draft_id);
if (! is_array($suits_rows) || $suits_rows === []) {
    WP_CLI::error('Draft SUITS page 3419 has no flexible content to copy');
}

$suits_hero = $suits_rows[0];
$suits_hero['layout_style'] = 'title_accent';
$suits_hero['heading'] = 'Service User IT Support';
$suits_hero['current_crumb_label'] = 'Service User IT Support';
$suits_hero['content'] = $p('Our Service User IT Support (SUITS) service can help you to use Your Portal or attend remote appointments.');
$suits_hero['background_color'] = '#C6ECF4';
$suits_hero['accent_color'] = '#6FC9C0';
unset($suits_hero['hero_image']);

$suits_body = ''
    . $p('Our SUITS team offers a range of supports for service users here in St Patrick’s Mental Health Services (SPMHS), including:')
    . '<ul>'
    . '<li>helping service users to ' . $a('/register-for-your-portal/', 'register for Your Portal') . '</li>'
    . '<li>dealing with technical queries relating to ' . $a('/about-your-portal/', 'Your Portal') . ' or accessing remote care</li>'
    . '<li>providing devices to ' . $a('/inpatient-hospital-care/', 'inpatient service users') . ' admitted to a ward, for the duration of their stay.</li>'
    . '</ul>'
    . $p('SUITS can also answer queries or troubleshoot problems if you experience issues while using Your Portal. If you are using Microsoft Teams (MS Teams) to access our remote services, SUITS can also offer you technical advice and help if you come across any issues.');

$suits_what = ''
    . $p('SUITS deals with technical queries and information support relating to Your Portal or access to MS Teams.')
    . $p('SUITS is not a clinical service. When you use the contact details for SUITS, your queries are directed to SUITS staff, not your clinician. We ask that you do not submit any queries about your medical condition or medication to the SUITS team. If you have a mental health or medical query, please contact your ' . $a('/about-us/our-team/', 'clinical team') . '.');

$suits_contact = ''
    . $p('You can ' . $a('tel:012493629', 'call SUITS on 01 249 3629') . ', or email your query to ' . $a('mailto:suits@stpatricks.ie', 'suits@stpatricks.ie') . '.')
    . $p('SUITS is available from 9am to 5pm, Monday to Friday. Any contact outside of these hours will be checked the next working day.')
    . $p('When you get in touch with the team, please provide as much information as possible for your query. This can include the device you are using when trying to access your session (for example, an iPhone or iPad, Android device, Mac, PC or others), the web browser you are using (such as Google Chrome, Safari, Firefox and others) or anything else that could be useful for the team to resolve the issue.')
    . $p('When you request assistance from SUITS, you are agreeing for the SUITS team to contact you in order to provide support. Any emails and voice messages collected during the team’s contact with you will be deleted when:')
    . '<ul>'
    . '<li>the problem is resolved</li>'
    . '<li>the team’s support is no longer required, or</li>'
    . '<li>the team confirms that it has taken all measures to resolve your problem without success.</li>'
    . '</ul>';

$suits_flexi = [
    $suits_hero,
    matrix_orlaith_content_row('', $suits_body, 'white'),
    matrix_orlaith_content_row('What does SUITS deal with?', $suits_what, 'cream'),
    matrix_orlaith_content_row('How can you contact SUITS?', $suits_contact, 'white', 0, 'image_left', [
        'primary_button' => matrix_orlaith_button('Read our Privacy Notice', $home . '/cookie-privacy-policy/'),
        'primary_button_variant' => 'filled',
    ]),
];

update_field('flexible_content_blocks', $suits_flexi, $suits_publish_id);

if ($suits_draft instanceof WP_Post) {
    wp_delete_post($suits_draft_id, true);
}

wp_update_post([
    'ID' => $suits_publish_id,
    'post_title' => 'Service User IT Support',
    'post_status' => 'publish',
    'post_name' => 'service-user-it-support',
]);
clean_post_cache($suits_publish_id);

WP_CLI::success('Published SUITS content on page ' . $suits_publish_id . ' → ' . get_permalink($suits_publish_id));

// ---------------------------------------------------------------------------
// 2) Create / update remote practical information page
// ---------------------------------------------------------------------------
$remote_slug = 'what-you-need-to-know-about-remote-care';
$remote_title = 'What you need to know about remote care';
$remote_id = (int) (get_page_by_path($remote_slug)?->ID ?? 0);

if ($remote_id === 0) {
    $remote_id = (int) wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => $remote_title,
        'post_name' => $remote_slug,
        'post_parent' => 0,
    ], true);
    if ($remote_id <= 0 || is_wp_error($remote_id)) {
        WP_CLI::error('Failed to create remote practical page');
    }
} else {
    wp_update_post([
        'ID' => $remote_id,
        'post_title' => $remote_title,
        'post_status' => 'publish',
        'post_name' => $remote_slug,
    ]);
}

$remote_url = untrailingslashit(get_permalink($remote_id));
$suits_url = untrailingslashit((string) get_permalink($suits_publish_id));
$portal_url = $home . '/about-your-portal/';
$portal_register_url = $home . '/register-for-your-portal/';
$remote_services_url = $home . '/care-treatment/remote-services/';
$carers_url = untrailingslashit((string) get_permalink(3929));
if ($carers_url === '' || $carers_url === '0') {
    $carers_url = $home . '/carers-and-supporters/';
}

$remote_intro = ''
    . $p('Find important practical information to support you in receiving remote care.')
    . $p('At St Patrick’s Mental Health Services (SPMHS), we provide ' . $a($remote_services_url, 'remote access to our services') . ' through phone, video or online channels. When you are referred to us, you will receive an assessment to identify what service and type of care is best for you. You can be assured that remote care is right for you if it is recommended following your assessment.')
    . $p('Our remote services have been designed to ensure that you receive high quality mental healthcare at home. You have the same access to our team as you would through attending in-person services. Anything you can do or talk about during in-person appointments, you can do online. Depending on the service you are attending, you can meet and engage with other people who are in mental health recovery also, and there are different informational, social and recreational activities and events you can take part in online too.')
    . $p('When you begin in remote services, your team will make arrangements with you about how to attend; these arrangements will depend on the service you are attending.')
    . $p('If you are an inpatient, you might also attend some programmes or appointments through remote access.')
    . $p('Our Service User IT Support (SUITS) team can support you in getting set up with and using the technological channels being used in your remote care. You can ' . $a($suits_url, 'contact SUITS here') . '.')
    . $p('You might also find it helpful to register for and use ' . $a($portal_url, 'Your Portal') . ', which is a secure, online platform that we offer to our service users and which you can access anytime, anywhere on your computer, smartphone or tablet. It allows you to access and share your own health-related information and to monitor your care and treatment in SPMHS.')
    . $p('Below, we share more information on the above and on some of the other practical aspects of accessing remote services. You can also watch our video on an introduction to remote care below.');

$devices = ''
    . $p('Your remote appointments may take place through different technological channels, depending on the service and/or the technology that best meets your needs.')
    . $p('You might use different devices to access your appointments. By “device”, we mean a computer, laptop, tablet, smartphone or mobile phone.')
    . $p('If your appointment takes place by phone, your team will use the phone number you provide to us to contact you.')
    . $p('If you are attending an appointment by videocall, it will take place using Microsoft Teams; you might also hear this called “MS Teams” or “Teams”. SUITS can help you to download Teams, perform a test call ahead of an appointment, or offer technical advice and help if you come across any issues. If you are an inpatient, SUITS can provide you with a tablet that has Teams installed on it for the duration of your stay if needed.');

$appointments = ''
    . $p('You will be given information on your timetables and on how you will get notifications of your appointments. Your notifications will depend on the service you are attending and whether or not you are using Your Portal.')
    . $p('Service users can receive appointment information through a number of different channels for inpatient and Dean Clinic services, including email, SMS, and Your Portal notifications where registered.')
    . $p('If you are registered for Your Portal, you can also view appointment details and access video links for scheduled appointments there.');

$privacy = ''
    . $p('When you are attending remote services, we recommend that you find a quiet space where you can take part in private and without disturbance. It can be helpful to let your family and/or the people you live with know of your appointment times, so that they know to give you the space and privacy you need.')
    . $p('We also note that, to best protect everyone’s confidentiality, the recording of remote appointments or sessions is not allowed.');

$technical = ''
    . $p('If you experience technical issues in attending remote appointments, you can ' . $a('tel:012493629', 'call SUITS on 01 249 3629') . ', or email your query to ' . $a('mailto:suits@stpatricks.ie', 'suits@stpatricks.ie') . '. SUITS is available from 9am to 5pm, Monday to Friday. Any contact outside of these hours will be checked the next working day.');

$portal_section = ''
    . $p('You can ' . $a($portal_register_url, 'register for Your Portal') . ' at any time during your care. It is free to use. When you register, you can:')
    . '<ul>'
    . '<li>view your appointment details</li>'
    . '<li>access video links for your scheduled appointments</li>'
    . '<li>receive notifications from your care team</li>'
    . '<li>complete forms</li>'
    . '<li>access other helpful resources for your recovery and wellness</li>'
    . '</ul>'
    . $p('and more. You can invite a carer — such as a ' . $a($carers_url, 'family member, advocate or friend') . ' — to use the portal with you, or invite a healthcare provider outside of SPMHS to have access to Your Portal.')
    . $p('SUITS can help you to register for Your Portal, deal with technical queries you have about using Your Portal, or troubleshoot problems if you experience issues while using the portal.')
    . $p('We have a number of guides on using Your Portal. You can ' . $a($portal_url, 'see our portal guides here') . '.');

$glossary_intro = $p('When you are accessing remote care, you might come across some language and terminology about the technologies you are using, which may be new to you. We’ve gathered a short list below of some terms you might hear or use in relation to remote care.');

$glossary_items = [
    'Android and iOS' => $p('Android and iOS are types of operating systems used in mobile technology, like smartphones or tablets. Operating systems are types of software which allow devices to run different programmes and services. iOS is provided by Apple, and so is found on devices like Apple iPads and iPhones. Android is made available by Google, and is used on a wide range of devices from different providers.'),
    'App' => $p('“App” is short for “application software”. An app is a piece of software with specific functions for a specific purpose. You usually have to download an app to a device, like a smartphone, to use it. For example, you might use an app to play games or listen to music on your phone. You might also use apps for calling or messaging other people, or joining online events.'),
    'Browser' => $p('A browser is an online programme that provides a way to find, view and interact with information on the Internet. Browsers you might be familiar with include Google Chrome, Safari, Firefox, and Microsoft Edge.'),
    'Device' => $p('When we talk about “devices” in relation to remote care, we are talking about the electronic equipment which you use to access different aspects of your remote care. These can include desktop computers, laptops, tablets, smartphones or mobile phones.'),
    'Email' => $p('Emails are types of communications sent between people using electronic devices. Your email address is the address used to send emails to you. At SPMHS, we may send you emails relating to your care and treatment, such as reminders about remote appointments, or about the different activities you can join. You may also receive email notifications about updates in Your Portal if you are registered to this.'),
    'Links and URLs' => $p('The terms “links” and “URLs” can be used to describe web addresses. For example, if a staff member in SPMHS says they’ll send you a “link” to attend a meeting, this might mean they are sending you the address needed to join an online videocall. If someone is going to send you a “URL” to some information, this means they are sending you the address to connect to something published on the Internet, like a webpage or video. You can click on the link or URL you receive to open the location. In a web browser like Google Chrome, Safari, Firefox or Microsoft Edge, you can also copy the URL or link into the address bar at the top to go to that location.'),
    'Microsoft Teams or Teams' => $p('Microsoft Teams is an online application that allows people to have videocalls or meetings. At SPMHS, we use Teams for video appointments, group programmes and other activities, such as evening activities. You may hear it referred to as “MS Teams” or even just “Teams”. Teams can be installed for free on a computer, laptop, or smartphone.'),
    'Mute and unmute' => $p('When you are in an online videocall appointment, you might be asked to “mute” your microphone. This means that you will hear the other person or people speaking, but they won’t hear you. There will be a button on your screen where you can do this. If you are asked to “unmute” your microphone, this means that other people can hear you. The same button on the screen will let you do this. The reason “mute” and “unmute” are used is to make it as easy as possible to hear everyone clearly during an appointment. You can ' . $a('https://support.microsoft.com/en-gb/office/muting-and-unmuting-your-mic-in-microsoft-teams-17886394-9a9a-4f04-b4cc-e46589408b28', 'see here how to mute and unmute your microphone') . ' in Teams.'),
    'SMS' => $p('SMS is short for “short message service”. It usually means a text message sent by mobile phone. At SPMHS, we may send you reminders of some remote appointments by SMS, for example.'),
    'QR code' => $p('QR is short for “quick response”. A QR code is a square black and white icon which you can see on posters, printed documents, websites and more. The code can be scanned by apps or cameras on smartphones. When they are scanned, the code usually links through to a chosen web page or performs an online action. We provide QR codes on some communications in SPMHS as one way of sharing links to information.'),
    'Zoom' => $p('Zoom is an online communications platform that can be used to have video calls with other people or to attend online events. At SPMHS, remote video appointments take place on Teams only. However, some of our events take place on Zoom, and you may also attend events or activities from other organisations which take place on Zoom.'),
];

$remote_flexi = [
    matrix_orlaith_hero_row(
        $remote_title,
        $p('Find important practical information to support you in receiving remote care.'),
        0
    ),
    matrix_orlaith_content_row('', $remote_intro, 'white'),
    matrix_orlaith_video_row(
        'Introduction to remote care',
        $p('Watch our short introduction to remote care.'),
        [['url' => 'https://www.youtube.com/embed/ixPyuHeybWE', 'title' => 'Introduction to remote care']]
    ),
    matrix_orlaith_content_row('Devices and channels used for remote services', $devices, 'cream'),
    matrix_orlaith_content_row('Appointment notifications', $appointments, 'white'),
    matrix_orlaith_content_row('Privacy', $privacy, 'cream'),
    matrix_orlaith_content_row('Technical difficulties', $technical, 'white'),
    matrix_orlaith_content_row('Your Portal', $portal_section, 'cream'),
    matrix_orlaith_content_row('Language and terminology', $glossary_intro, 'white'),
    matrix_orlaith_accordion_row($glossary_items, 'default', ''),
    matrix_orlaith_useful_links_row([
        'Service User IT Support (SUITS)' => $suits_url,
        'About Your Portal' => $portal_url,
        'Register for Your Portal' => $portal_register_url,
        'Remote Services' => $remote_services_url,
        'St Patrick’s at Home' => $home . '/service-users-and-visitors/about-our-st-patricks-at-home-service/',
    ]),
];

update_field('flexible_content_blocks', $remote_flexi, $remote_id);
WP_CLI::success('Remote practical page ' . $remote_id . ' → ' . $remote_url);

// ---------------------------------------------------------------------------
// 3) Remap broken links on About St Patrick's at Home (266)
// ---------------------------------------------------------------------------
$at_home_id = 266;
$at_home = get_field('flexible_content_blocks', $at_home_id);
if (! is_array($at_home)) {
    WP_CLI::warning('Page 266 has no flexible content; skipping link remap');
} else {
    $replacements = [
        $home . '/service-user-it-support/' => $suits_url,
        // Wrong remap from earlier rebuild: practical remote info had been pointed at SUITS.
        // Leave SUITS URLs alone; only rewrite when context is the old live practical URL if still present.
        'https://www.stpatricks.ie/care-treatment/our-services/remote-services/practical-information-remote-services' => $remote_url,
        'http://www.stpatricks.ie/care-treatment/our-services/remote-services/practical-information-remote-services' => $remote_url,
        'https://www.stpatricks.ie/media-centre/news/2022/may/new-appointment-notifications' => $remote_url,
        'http://www.stpatricks.ie/media-centre/news/2022/may/new-appointment-notifications' => $remote_url,
    ];

    $json = wp_json_encode($at_home);
    $changed = false;

    // Heuristic: if a link label mentions remote / practical / appointment notification
    // and currently points at SUITS or About Your Portal, point at the new remote page.
    $encoded = $json;
    $patterns = [
        '#https?://(?:www\.)?stpatricks\.ie/care-treatment/our-services/remote-services/practical-information-remote-services/?#i' => $remote_url,
        '#https?://(?:www\.)?stpatricks\.ie/media-centre/news/2022/may/new-appointment-notifications/?#i' => $remote_url,
        '#' . preg_quote($home . '/service-user-it-support-2', '#') . '/?#i' => $suits_url,
    ];
    foreach ($patterns as $pattern => $to) {
        $next = preg_replace($pattern, $to, $encoded);
        if (is_string($next) && $next !== $encoded) {
            $encoded = $next;
            $changed = true;
        }
    }

    // Fix specific known wrong internal targets by scanning decoded structure for link text.
    $walk = static function (&$node) use (&$walk, $remote_url, $suits_url, $home): void {
        if (is_array($node)) {
            foreach ($node as $k => &$v) {
                $walk($v);
            }
            unset($v);

            return;
        }
        if (! is_string($node) || $node === '' || ! str_contains($node, '<a ')) {
            return;
        }
        $node = preg_replace_callback(
            '/<a\s+([^>]*?)href="([^"]+)"([^>]*)>(.*?)<\/a>/is',
            static function (array $m) use ($remote_url, $suits_url, $home): string {
                $href = $m[2];
                $label = trim(wp_strip_all_tags($m[4]));
                $label_l = strtolower($label);
                $new = $href;

                if (
                    str_contains($label_l, 'practical')
                    || str_contains($label_l, 'remote care')
                    || str_contains($label_l, 'remote service')
                    || str_contains($label_l, 'appointment notification')
                    || str_contains($label_l, 'notifications')
                ) {
                    // Don't steal genuine SUITS links.
                    if (! str_contains($label_l, 'suits') && ! str_contains($label_l, 'it support')) {
                        $new = $remote_url;
                    }
                }

                if (str_contains($label_l, 'suits') || str_contains($label_l, 'service user it support')) {
                    $new = $suits_url;
                }

                if ($new === $href) {
                    return $m[0];
                }

                return '<a ' . $m[1] . 'href="' . esc_url($new) . '"' . $m[3] . '>' . $m[4] . '</a>';
            },
            $node
        ) ?? $node;
    };

    $decoded = json_decode($encoded, true);
    if (is_array($decoded)) {
        $walk($decoded);
        update_field('flexible_content_blocks', $decoded, $at_home_id);
        $changed = true;
    }

    WP_CLI::success('Remapped links on page 266' . ($changed ? '' : ' (no structural change detected)'));
}

WP_CLI::log('');
WP_CLI::log('SUITS:  ' . $suits_url);
WP_CLI::log('Remote: ' . $remote_url);
WP_CLI::log('At-home: ' . get_permalink($at_home_id));
