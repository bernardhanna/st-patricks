<?php

/**
 * Rebuild remaining Orlaith August pages from Word drafts and comments.
 */

require_once __DIR__ . '/orlaith-page-helpers.php';
require_once __DIR__ . '/orlaith-psychosis-layout.php';

function matrix_orlaith_rebuild_suan_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $hero = matrix_orlaith_find_image(3934, 'SUAN.png');
    $what_image = matrix_orlaith_find_image(2632, 'service-user-engagement-featured-image.png');
    $join_image = matrix_orlaith_find_image(1401, 'online-peer-support-featured-image-1.png');
    $privacy = matrix_orlaith_permalink('data-protection-policy');
    $suas = matrix_orlaith_permalink('service-users-and-visitors/service-user-participation/service-user-and-supporters-council');

    $what = '<p>We believe that the people who use our services have direct experience in mental health recovery and can bring a unique perspective to the development of mental healthcare.</p>'
        . '<p>We set up SUAN to enable us to engage and consult with as many of our service users as possible. SUAN is a network of current and past service users who are kept informed of opportunities to share their views and perspectives about various aspects of our work. This helps us not only to gather but to act on service user feedback.</p>'
        . '<p>Members of SUAN are kept informed by email about focus groups and consultation forums that they can attend. These groups and forums provide the opportunity for us to present ideas to the members of SUAN about changes being considered in our services. They also give members the chance to share their ideas and opinions to make sure that the views of people who use our services are heard and included.</p>'
        . '<p>SUAN members are also invited to join working groups and steering committees to help develop new projects. Some examples of projects include:</p>'
        . '<ul><li>an interactive mental health education centre</li>'
        . '<li>modernised ' . matrix_orlaith_a('inpatient-care', 'inpatient facilities') . '</li>'
        . '<li>our Academic Institute, which focuses on mental health research</li>'
        . '<li>technological innovations</li>'
        . '<li>expansions of our remote and ' . matrix_orlaith_a('what-we-offer/st-patricks-at-home', 'homecare services') . '.</li></ul>';

    $how = '<p>Membership of SUAN is open to current and former service users, aged over 18.</p>'
        . '<p>To join SUAN, complete the registration details below, or email Siobhan Fitzharris, Service User Engagement Lead, at <a href="mailto:sfitzharris@stpatricks.ie">sfitzharris@stpatricks.ie</a>.</p>'
        . '<p>Once you have registered, you will be added to the SUAN mailing list, where you will receive regular updates by email about opportunities to take part in various groups or projects. You will also be kept informed about events and initiatives that may be of interest to you.</p>'
        . '<p>There is no obligation to take part in the different groups and projects, and you can opt out of the network and mailing list at any time.</p>'
        . '<p>Please note that many projects SUAN consults on can be sensitive. Because of this, you will be asked to sign a confidentiality agreement when taking part in certain forums or committees.</p>'
        . '<p>If you are interested in becoming more involved and taking on a more formal representative role, you may be interested in joining our Service User and Supporters Council (SUAS). SUAS is a formal council that meets monthly and works directly with management, clinicians and the Board of Governors to help shape and improve our services.</p>';

    $form_intro = '<p>The Service User Advisory Network is an opportunity for our former and current service users to provide input and involvement into our strategic development. You will receive emails regarding updates from the network.</p>';

    $rows = [
        matrix_orlaith_hero_row('Service User Advisory Network', '<p>Our Service User Advisory Network (SUAN) is a network of current and former service users who share their expertise, ideas, and opinions to help shape the future of St Patrick\'s Mental Health Services (SPMHS).</p>', $hero),
        matrix_orlaith_content_row('What is SUAN?', $what, 'white', $what_image, 'image_left'),
        matrix_orlaith_content_row('How do I join SUAN?', $how, 'cream', $join_image, 'image_right', [
            'primary_button' => matrix_orlaith_button('Find out more about SUAS', $suas),
            'primary_button_variant' => 'filled',
        ]),
        matrix_orlaith_contact_form_row('Join SUAN', $form_intro, [
            'form_name' => 'SUAN registration',
            'email_subject' => 'SUAN – new registration request',
            'privacy_policy_link' => [
                'title' => 'Privacy Notice',
                'url' => $privacy,
                'target' => '_blank',
            ],
        ]),
        matrix_orlaith_stories_row(),
    ];

    matrix_orlaith_save_page($post_id, $rows, true, $hero);
    matrix_orlaith_set_seo($post_id, 'Service User Advisory Network | St Patrick\'s Mental Health Services', 'Find out more about our Service User Advisory Network at St Patrick\'s Mental Health Services, including what it does and how to join.');
}

function matrix_orlaith_rebuild_fcs_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $hero = matrix_orlaith_find_image(3936, 'FCS Advisory Network.png');
    $what_image = matrix_orlaith_find_image(3930, 'Carers and supporters.png');
    $join_image = matrix_orlaith_find_image(1247, 'family-carers-advisory-network.png');
    $privacy = matrix_orlaith_permalink('data-protection-policy');
    $suas = matrix_orlaith_permalink('service-users-and-visitors/service-user-participation/service-user-and-supporters-council');
    $carers = matrix_orlaith_permalink('service-users-and-visitors/carers-and-supporters');

    $what = '<p>We believe that ' . matrix_orlaith_a('service-users-and-visitors/carers-and-supporters', 'those who support people going through mental health recovery') . ' have unique perspectives and experience which are invaluable in shaping the future of mental health services.</p>'
        . '<p>The FCS Advisory Network is an advisory group set up to ensure that the people supporting our service users can help to shape and monitor new developments here in St Patrick\'s Mental Health Services (SPMHS).</p>'
        . '<p>FCS Advisory Network members are kept informed about focus groups and consultation forums which they can attend. These groups provide the opportunity for us to present ideas to the network, and for members to share their ideas and opinions on these.</p>'
        . '<p>Members are also invited to join working groups and steering committees for new projects. They can join our Service User and Supporters Council (SUAS) too.</p>'
        . '<p>Membership is open to people aged over 18 who are family members, carers or supporters of our current or past service users. You are a carer or supporter if you spend a lot of time supporting a family member or friend who needs help in dealing with a mental health difficulty, and if you are an important part of their support system.</p>'
        . '<p>If you would like more information about the FCS Advisory Network, please contact our Service User Engagement Lead, Siobhan Fitzharris, by <a href="tel:012493390">calling 01 249 3390</a> or <a href="mailto:sfitzharris@stpatricks.ie">emailing sfitzharris@stpatricks.ie</a>.</p>';

    $join = '<p>To join the FCS Advisory Network, complete the registration form below. By doing so, you will be added to the FCS Advisory Network mailing list, where you will receive regular updates about opportunities to take part in various groups or projects. You will also be kept informed about events and initiatives that may be of interest to you.</p>'
        . '<p>There is no obligation to take part in the different groups and projects, and you can opt out of the network and mailing list at any time.</p>';

    $rows = [
        matrix_orlaith_hero_row('Family, Carers and Supporters Advisory Network', '<p>Our Family, Carers and Supporters (FCS) Advisory Network enables us to engage and consult with the people who support our service users, and to ensure their views are included as we develop our services.</p>', $hero),
        matrix_orlaith_content_row('What is the FCS Advisory Network?', $what, 'white', $what_image, 'image_left', [
            'primary_button' => matrix_orlaith_button('Join our Service User and Supporters Council', $suas),
            'primary_button_variant' => 'filled',
            'secondary_button' => matrix_orlaith_button('Carers and Supporters', $carers),
            'secondary_button_variant' => 'outline',
        ]),
        matrix_orlaith_content_row('Join the FCS Advisory Network', $join, 'cream', $join_image, 'image_right', [
            'primary_button' => ['title' => '', 'url' => '', 'target' => ''],
            'secondary_button' => ['title' => '', 'url' => '', 'target' => ''],
        ]),
        matrix_orlaith_contact_form_row('', '', [
            'background_type' => 'white',
            'background_color' => '#FFFFFF',
            'vertical_padding' => 'compact',
            'form_name' => 'FCS Advisory Network registration',
            'email_subject' => 'FCS Advisory Network – new registration request',
            'show_role_field' => 1,
            'privacy_policy_label' => 'Read our Privacy Notice here',
            'privacy_policy_link' => [
                'title' => 'Privacy Notice',
                'url' => $privacy,
                'target' => '_blank',
            ],
            'consent_items' => [
                [
                    'title' => 'I agree to sign up to the FCS mailing list.',
                    'description' => 'Please confirm if you would like to subscribe to the FCS mailing list to receive opportunities to take part in groups, projects and consultations to improve mental health services and to stay informed on relevant events and initiatives.',
                    'required' => 1,
                ],
                [
                    'title' => 'By submitting this form, you agree to the terms of our',
                    'description' => '',
                    'required' => 1,
                ],
            ],
        ]),
        matrix_orlaith_stories_row(),
    ];

    matrix_orlaith_save_page($post_id, $rows, true, $hero);
    matrix_orlaith_set_seo($post_id, 'Family, Carers and Supporters Advisory Network | St Patrick\'s', 'Find out more about our Family, Carers and Supporters Advisory Network at St Patrick\'s Mental Health Services, including what it does and how to join.');
}

function matrix_orlaith_rebuild_survey_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $hero = matrix_orlaith_find_image(3938, 'Service user experience surveys.png');
    $image = matrix_orlaith_find_image(1407, 'experience-survey-pod-image.png');
    $inpatient_image = matrix_orlaith_find_image(3917, 'Homecare.png');
    $day_image = matrix_orlaith_find_image(3924, 'Day programme.png');
    $dean_image = matrix_orlaith_find_image(636, '');

    $understanding = '<p>Our Service User Experience Surveys are designed to give us a better understanding of how you view the care and treatment you have received during your engagement with SPMHS.</p>'
        . '<p>We would be grateful if you could take a couple of minutes to complete the relevant survey.</p>'
        . '<p>The survey does not identify you and all information provided will be treated as confidential. As such, please do not record your name. The information in this survey will be reviewed by '
        . matrix_orlaith_a('about-us/our-team', 'our senior management')
        . ' and will assist in improving and developing the '
        . matrix_orlaith_a('what-we-offer', 'services that we offer')
        . '.</p>';

    $rows = [
        matrix_orlaith_hero_row('Service User Experience Surveys', '<p>At St Patrick\'s Mental Health Services (SPMHS), we aim to provide the highest quality of mental healthcare, promote mental health and advocate for the rights of people who experience mental health difficulties.</p>', $hero),
        matrix_orlaith_content_row('Understanding your experience', $understanding, 'white', $image, 'image_left', [
            'image_height_mode' => 'fixed_min',
        ]),
        matrix_orlaith_about_links_grid_row('Take a survey', [
            [
                'icon' => '',
                'image_url' => matrix_orlaith_attachment_url($inpatient_image),
                'title' => 'Inpatient and homecare surveys',
                'description' => 'If you recently received care as an inpatient, through homecare, or with a combination of both, please complete the survey below.',
                'link' => matrix_orlaith_button('Take our inpatient and homecare survey', 'https://www.surveymonkey.com/r/SUES2026Web', '_blank'),
                'card_tone' => 'bg1',
            ],
            [
                'icon' => '',
                'image_url' => matrix_orlaith_attachment_url($day_image),
                'title' => 'Day service survey',
                'description' => 'If you have completed one of our day programmes, please use the survey below to give your feedback.',
                'link' => matrix_orlaith_button('Take the day service survey', 'https://www.surveymonkey.com/r/DayProgs2026Web', '_blank'),
                'card_tone' => 'bg2',
            ],
            [
                'icon' => '',
                'image_url' => matrix_orlaith_attachment_url($dean_image),
                'title' => 'Dean Clinic survey',
                'description' => 'If you attended Dean Clinic appointments, either in person or online, please complete the survey below.',
                'link' => matrix_orlaith_button('Take the Dean Clinic survey', 'https://www.surveymonkey.com/r/DeanClinic2026Web', '_blank'),
                'card_tone' => 'bg3',
            ],
        ]),
        matrix_orlaith_video_row('Video resources', '', [['url' => 'https://www.youtube.com/watch?v=QQDB6ZyvjBk']]),
        matrix_orlaith_useful_links_row([
            'Feedback and comments' => 'service-users-and-visitors/feedback-and-comments',
            'Service User Participation' => 'service-users-and-visitors/service-user-participation',
        ]),
    ];

    matrix_orlaith_save_page($post_id, $rows, true, $hero);
}

function matrix_orlaith_rebuild_willow_parent_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $hero = matrix_orlaith_find_image(3927, 'Willow Grove.png');
    $image = matrix_orlaith_find_image(624, '');
    $inpatient = matrix_orlaith_permalink('service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent');
    $homecare = matrix_orlaith_permalink('service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent');
    $family = matrix_orlaith_permalink('service-users-and-visitors/your-care-with-willow-grove/information-for-your-family');

    $expect = '<p>At Willow Grove, we aim to provide you with high quality, nurturing care on your journey to mental health recovery. If you are admitted to our services, you will work closely with a skilled mental health team on your own personalised care plan, and your parents will be involved.</p>'
        . '<p>You can see some Frequently Asked Questions (FAQs) about Willow Grove below.</p>';

    $accordion = matrix_orlaith_accordion_row([
        'How can I be referred to Willow Grove?' => '<p>You can be referred to SPMHS by a GP, a psychiatrist, the Health Service Executive (HSE), Child and Adolescent Mental Health Services (CAMHS) and other healthcare professionals. Your referral will be carefully reviewed internally, and our referrals team will contact your parents by phone to discuss the service identified to meet your needs. Your parents will also be asked to provide your health insurer membership details (if applicable) and any relevant reports. If you are linked in with CAMHS but your referral came from another healthcare professional, we will get in touch with your CAMHS team to see if they support the referral and can offer support after your discharge.</p>',
        'What is admission to Willow Grove like?' => '<p>If your admission is confirmed, the referrals team for our adolescent services will get in touch with your parents and make arrangements. You can see more on the inpatient and homecare pages linked below.</p>',
        'Who is my care team?' => '<p>You will be supported by a Willow Grove multidisciplinary team (MDT). An MDT is made up of a number of mental health and healthcare professionals with different areas of expertise. During your care, you might engage with adolescent psychiatrists and psychologists, psychiatric nurses, occupational therapists, a social worker, a family therapist, a Cognitive Behavioural Therapy (CBT) therapist, a teacher and/or a dietician and more, depending on what best suits your recovery.</p><p>You will also be assigned a key worker, who will keep in regular contact with you. They are the health professional acting as the link between you and your care team.</p>',
        'What is a care plan?' => '<p>You are actively involved in all aspects of your care. On the day you are admitted to Willow Grove, our team will begin putting together a personalised care plan with you.</p><p>A care plan is a set of goals and targets for your mental health recovery. It is reviewed regularly throughout your care.</p>',
        'Who is my key worker?' => '<p>Your key worker is the health professional responsible for making sure that you and your MDT are working together to progress your care plan. Your key worker can be any member of your MDT. There will also be an associate key worker who you can get in touch with if your key worker is not available.</p>',
        'What about my privacy?' => '<p>Because the staff at Willow Grove all work as part of an MDT, your information will be shared between all team members. We do this to ensure that you receive the best possible care and treatment.</p><p>Sharing information with a person outside the MDT will be discussed with you and your parents if needed. It is done in line with our Child Protection Policy. To protect everyone\'s privacy, please do not take photographs or videos of other service users or members of staff, and please do not record your appointments.</p>',
        'Is my family involved in my care?' => '<p>At all times, our staff aim to work in partnership with you and your parents, whether you are receiving care as an inpatient or through homecare.</p><p>Throughout admission, our team will hold a number of meetings with you, some of which may involve your parents or guardians.</p>',
        'Can I make suggestions or complaints?' => '<p>Absolutely. Our Willow Grove team aims to provide care and treatment to the highest standard. This means that it\'s really important to us that we have feedback from young people about what\'s helpful and what\'s not.</p><p>Your comments on both the positive and negative aspects of our service are always very welcome. Nursing staff can provide you with guidance about how to give feedback.</p>',
    ], 'default', 'FAQs');

    $rows = [
        matrix_orlaith_hero_row('Attending Willow Grove', '<p>Willow Grove is the dedicated adolescent unit in St Patrick\'s Mental Health Services (SPMHS), where we provide mental healthcare and treatment to young people aged 12 to 17. Here, we go through some practical information about what to expect from being referred to and receiving inpatient or homecare services from the Willow Grove team.</p>', $hero),
        matrix_orlaith_content_row('What to expect', $expect, 'white', $image, 'image_left', [
            'primary_button' => matrix_orlaith_button('Your stay as an inpatient', $inpatient),
            'primary_button_variant' => 'filled',
            'secondary_button' => matrix_orlaith_button('Your time in homecare', $homecare),
            'secondary_button_variant' => 'outline',
        ]),
        $accordion,
        matrix_orlaith_video_row('Video resources', '', [
            ['url' => 'https://www.youtube.com/watch?v=Eh22napqjOI'],
            ['url' => 'https://www.youtube.com/watch?v=hH_kUAQZiKs'],
        ]),
        matrix_orlaith_useful_links_row([
            'Your stay in hospital as an adolescent' => $inpatient,
            'Your time in homecare as an adolescent' => $homecare,
            'Information for your family' => $family,
        ]),
    ];

    matrix_orlaith_save_page($post_id, $rows, false, $hero);
}

function matrix_orlaith_rebuild_adolescent_stay_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $hero = matrix_orlaith_find_image(3940, 'Willow Grove bedroom.png');
    $image = matrix_orlaith_find_image(774, 'st-patricks-mental-health-services-group-room.jpg');

    $expect = '<p>If you are an inpatient in Willow Grove, this means you will be staying on the ward here for a period of time. You will work closely with a skilled mental health team on your own personalised care plan: '
        . matrix_orlaith_a('service-users-and-visitors/your-care-with-willow-grove', 'learn more about the team and your care plan here')
        . '.</p>'
        . '<p>There are 14 beds in Willow Grove, so you will be here with other young people. You will have your own bedroom and ensuite, and there are lots of facilities you can use or activities you can take part in during your stay.</p>';

    $list = static function (array $items): string {
        $html = '<ul>';
        foreach ($items as $item) {
            $html .= '<li>' . $item . '</li>';
        }

        return $html . '</ul>';
    };

    $accordion = matrix_orlaith_accordion_row([
        'What is a typical day in Willow Grove like?' => '<p>Your stay will involve attending a variety of activities through our Young Person\'s Group Programme. This includes school, therapy groups, and recreational sessions. You may attend family meetings, individual sessions and ward rounds during the day. There will also be free time for activities.</p>'
            . '<p>Community meetings take place twice a day. These meetings involve catching up with staff to check how things are going for you, discussing your plan for the day, identifying goals, and raising any concerns.</p>'
            . '<p>We also run a youth advocacy programme where, every second week, you can meet with an independent mental health advocate who can help you to ensure your voice is heard in all aspects of your care.</p>'
            . '<p>During the week, morning wake up calls usually start after 8am, with breakfast being served at 8.30am. At night, bedtime is at 10.30pm, with lights out at 11pm.</p>'
            . '<p>You will spend your first weekend on the ward and the Willow Grove staff will work with you to develop a plan about how to spend your days. Generally, at the weekends, the routine is much more relaxed. Bedtimes are more flexible, as we know young people like to get up late and go to bed later.</p>',
        'Are there social and leisure activities?' => '<p>There are a number of recreational areas you can use in Willow Grove. Different activities take place in Willow Grove each day too. We believe joining these activities, along with the other groups and therapy sessions you take part in, leads to recovery and wellbeing.</p>'
            . '<p>Examples of activities you might take part in include:</p>'
            . $list([
                'Arts and crafts',
                'Football',
                'Baking or cooking',
                'Table quizzes',
                'Karaoke',
                'Table tennis',
                'Board games',
                'Basketball',
                'Gardening',
                'Playing games or Nintendo Wii',
                'Watching films or TV.',
            ]),
        'What should I bring?' => '<p>Some items that will be helpful for you to bring include:</p>'
            . $list([
                'Appropriate clothes for day and night, including a jacket for outdoor activities',
                'Toiletries, including roll-on deodorant (sprays are not allowed)',
                'Books or magazines you enjoy reading',
                'Some items to personalise your bedroom',
                'A small amount of pocket money (maximum €20).',
            ])
            . '<p>You will have a locker which you can use to store items safely. You are responsible for your own property.</p>'
            . '<p>Please note that, when you are being admitted, a member of the nursing team will go through your belongings with you and take a list of what you have brought with you. This is to make sure that any potentially harmful or dangerous items are either sent home with your parents or carers or taken and stored safely.</p>'
            . '<p>There are a number of items you cannot bring with you to Willow Grove. This is with your own and everyone else\'s safety and comfort in mind.</p>'
            . '<p>Please do not bring any:</p>'
            . $list([
                'laptops',
                'tablets',
                'cameras',
                'Wi-Fi/Bluetooth-enabled devices',
                'televisions',
                'DVD players.',
            ])
            . '<p>You will be assigned a tablet when you are admitted, which you can use for educational purposes or to make video calls to family or friends. There is also a computer in the lounge area of Willow Grove which is available for going online.</p>'
            . '<p>You are also not allowed to bring any:</p>'
            . $list([
                'inappropriate viewing material (magazines, computer games, CDs, DVDs for over 16s, and so on)',
                'sharp items (like knives or scissors)',
                'items of high value',
                'belts or cords',
                'aerosols or sprays',
                'stimulant drinks or beverages in cans (bottles only please)',
                'alcohol',
                'illegal substances or &ldquo;Headshop&rdquo; products.',
            ]),
        'Can I bring or use devices?' => '<p>Personal mobile phones are not allowed in Willow Grove. Instead, you will be provided with a mobile phone without a camera during your time in Willow Grove. You can insert your own SIM card into this phone in order to make calls, send messages, and so on.</p>'
            . '<p>Mobile phone use is restricted to certain times throughout the day in order to avoid disruption to the group programme.</p>'
            . '<p>You can use a music device in between the times you attend the group programme and again throughout the evening. However, iPods or music devices with access to the Internet or a camera facility are not allowed.</p>',
        'Can I have visitors?' => '<p>Yes, you can have visitors when you are in Willow Grove. Two people can visit you at one time. Visiting can be arranged through the Willow Grove team.</p>'
            . '<p>You will be asked to provide a list of agreed visitors. All visitors must be pre-approved by your parents. Any visitor under the age of 18 must be accompanied by an adult already named on your visitor list.</p>'
            . '<p>In the first week, visits take place on the ward only; after this, they may take place in other places on the campus, based on your physical and mental health.</p>',
        'Can I leave or spend time away from Willow Grove?' => '<p>Arrangements for leave or spending time away from Willow Grove will depend on your individual needs and care plan. This will be discussed and decided on alongside your parents or guardians.</p>',
        'Will I be safe?' => '<p>The team works very hard to ensure that Willow Grove remains a safe space at all times.</p>'
            . '<p>Nursing staff routinely carry out checks throughout the day. These checks are to confirm that all young people are present and okay, while also ensuring that no items that could potentially be unsafe have entered Willow Grove.</p>'
            . '<p>Nursing staff will go through any items brought into Willow Grove after admission. You can help us with this by remembering to go through any new items you bring into Willow Grove with a staff member before returning to your room, and also making sure that your visitors do the same.</p>'
            . '<p>If, for any reason, you feel unsafe in Willow Grove, please talk to any member of the Willow Grove team as soon as possible.</p>',
    ]);

    $rows = [
        matrix_orlaith_hero_row('Attending Willow Grove', '<p>Willow Grove Adolescent Unit is our specialised inpatient facility for adolescents, where we provide mental healthcare and treatment to young people aged 12 to 17. Here, we go through some practical information about what to expect from your stay in Willow Grove.</p>', $hero),
        matrix_orlaith_content_row('What to expect', $expect, 'white', $image, 'image_left'),
        matrix_orlaith_video_row('Video resources', '', [
            ['url' => 'https://www.youtube.com/watch?v=Eh22napqjOI'],
            ['url' => 'https://www.youtube.com/watch?v=DTq80NIFp9E', 'title' => 'Adolescent mental health'],
        ]),
        matrix_orlaith_content_row('Your stay in Willow Grove', '<p>You can find out more below on how to get ready for your stay, what happens when you arrive, and what care and supports are available to you while you are in Willow Grove.</p>', 'cream'),
        $accordion,
        matrix_orlaith_useful_links_row([
            'Your care with Willow Grove' => 'service-users-and-visitors/your-care-with-willow-grove',
            'Your time in homecare as an adolescent' => 'service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent',
            'Information for your family' => 'service-users-and-visitors/your-care-with-willow-grove/information-for-your-family',
        ]),
    ];

    matrix_orlaith_save_page($post_id, $rows, true, $hero);
}

function matrix_orlaith_rebuild_adolescent_homecare_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $hero = matrix_orlaith_find_image(3942, 'Adolescent homecare.png');
    $image = matrix_orlaith_find_image(1401, 'online-peer-support-featured-image-1.png');

    $expect = '<p>St Patrick\'s at Home, or homecare, offers you the high level of mental health support typically found in inpatient care, but delivered to you in the comfort of your own home. The service is designed and run by our Willow Grove team, who specialise in adolescent mental health.</p>'
        . '<p>You can find out more below on what to expect from your time in homecare, starting from your referral and admission right through to your discharge.</p>';

    $accordion = matrix_orlaith_accordion_row([
        'How admission works' => '<p>If your admission is confirmed, the referrals team for our adolescent services will get in touch with your parents and make arrangements for your admission; this will include an admission assessment. Your admission takes place online through videocall. On the day of your admission, the team will make contact as arranged and complete a full mental health assessment.</p>',
        'Contact with our team' => '<p>You will receive high-quality care while you are in homecare, with a range of mental health professionals working with you to support your recovery. Please be assured that you have 24-hour access to support throughout your time in homecare, so you or your parents can get in touch by phone at any time if you need support.</p>',
        'Your schedule' => '<p>A timetable will be sent by email to you and your parents so that you know your schedule each week. You will need to be available for all your scheduled appointments. It is very important that you or your parents let us know if you need to make a change or if you cannot attend an appointment.</p>',
        'Your location' => '<p>It is best to find a quiet, private space at home where you can talk to your team and take part in appointments in a private, safe, and relaxed way. Please be aware that you and one of your parents must be in the Republic of Ireland while you are in homecare.</p>',
        'Technical support' => '<p>Homecare is delivered remotely, which means through phone, video and online technologies. Our Service User IT Support (SUITS) team can help you with technical queries around accessing homecare.</p>',
        'Typical day' => '<p>In general, from Monday to Friday, you can expect to have a daily phone call with your key worker, take part in a weekly review with your consultant, have weekly appointments with other team members, and attend school if this is part of your care plan. At the weekends, you will continue to receive a daily phone call from your key worker. You have 24-hour support available, seven days a week.</p>',
        'Education' => '<p>Your educational needs will be discussed when you begin in homecare. You might attend school for some time during the day, depending on your individual care plan.</p>',
        'Medication' => '<p>If you are prescribed any new medication as part of your care, the doctor prescribing this will discuss it with you and your parents. While you are in homecare, we make arrangements with local pharmacies to dispense the medication prescribed to you at no charge to your parents. You can ' . matrix_orlaith_a('care-treatment/medication', 'learn more about medication here') . '.</p>',
        'Leaving homecare' => '<p>The length of time you are in homecare will depend on how much support and care you need. The decision on when you are ready for discharge will be made by you, your parents and your MDT together. Follow-up care will be discussed around the time you are preparing for discharge.</p>',
    ]);

    $rows = [
        matrix_orlaith_hero_row('Your time in St Patrick\'s at Home', '<p>Through our St Patrick\'s at Home service, young people aged 12 to 17 can receive high-quality mental healthcare at home. Here, we go through some useful information for young people being admitted to homecare about how admission works, what a typical day is like, and what practical information you need to know.</p>', $hero),
        matrix_orlaith_content_row('What to expect from homecare', $expect, 'white', $image, 'image_left'),
        matrix_orlaith_video_row('Video resources', '', [
            ['url' => 'https://www.youtube.com/watch?v=gsGM112v3bw'],
            ['url' => 'https://www.youtube.com/watch?v=hH_kUAQZiKs'],
            ['url' => 'https://www.youtube.com/watch?v=DTq80NIFp9E', 'title' => 'Adolescent mental health'],
            ['url' => 'https://www.youtube.com/watch?v=ixPyuHeybWE'],
        ]),
        matrix_orlaith_content_row('Getting started in homecare', '<p>Use the sections below to see what happens when you are admitted to homecare with the Willow Grove team.</p>', 'cream'),
        $accordion,
        matrix_orlaith_useful_links_row([
            'Your care with Willow Grove' => 'service-users-and-visitors/your-care-with-willow-grove',
            'Your stay in hospital as an adolescent' => 'service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent',
            'Information for your family' => 'service-users-and-visitors/your-care-with-willow-grove/information-for-your-family',
        ]),
    ];

    matrix_orlaith_save_page($post_id, $rows, true, $hero);
    matrix_orlaith_set_seo($post_id, 'What to expect from St Patrick\'s at Home for adolescents', 'See what to expect from St Patrick\'s at Home service for adolescents, offering you high-quality mental healthcare directly in your own home.');
}

function matrix_orlaith_rebuild_family_info_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $hero = matrix_orlaith_find_image(3944, 'Willow Grove family support.png');
    $image = matrix_orlaith_find_image(3930, 'Carers and supporters.png');
    $inpatient = matrix_orlaith_permalink('service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent');
    $homecare = matrix_orlaith_permalink('service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent');
    $willow = matrix_orlaith_permalink('service-users-and-visitors/your-care-with-willow-grove');

    $intro = '<p>We know it can be a difficult time for families if your child is receiving care with us here in Willow Grove, either as an inpatient or through homecare. Please be assured that we offer a safe, supportive environment where we work with you and your child to move them toward recovery and develop skills to improve and maintain their mental health.</p>'
        . '<p>Your child will have an individual care plan and work with our highly skilled team: find out more about the Willow Grove team '
        . matrix_orlaith_a('service-users-and-visitors/your-care-with-willow-grove', 'here')
        . ', or see Frequently Asked Questions (FAQs) for parents and carers below.</p>';

    $accordion = matrix_orlaith_accordion_row([
        'Are family members involved in my child\'s treatment?' => '<p>As a parent or carer, please be assured that you will be involved in and kept informed of your child\'s care. Our team aims to work in partnership with each young person in Willow Grove and their family, whether they are admitted as an inpatient or through homecare.</p>'
            . '<p>During your child\'s admission, our team will hold several meetings with the young person, and some of these will involve parents or guardians. The goal of these meetings is to help families discover their own strengths and resources, while also identifying appropriate ways of managing your child\'s difficulties.</p>',
        'What will my child\'s day be like?' => '<p>The daily activities your child will be involved in will depend on their needs and personal care plan, and whether your child is admitted to our inpatient unit or to our homecare service. See more about a typical day in inpatient '
            . matrix_orlaith_a('service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent', 'here')
            . ' or a typical day in homecare '
            . matrix_orlaith_a('service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent', 'here')
            . '.</p>',
        'Will my child be taking medication?' => '<p>If our team identifies that medication would be appropriate for your child, the consultant psychiatrist will discuss '
            . matrix_orlaith_a('service-users-and-visitors/medication', 'medication options')
            . ' with you.</p>'
            . '<p>No psychiatric medication will be started without your consent, except in emergency situations. Information about the effects and '
            . matrix_orlaith_a('what-to-expect-from-mental-health-medication', 'potential side effects')
            . ' of any new medication prescribed for your child will be discussed with you prior to beginning the new medication.</p>'
            . '<p>If your child is receiving inpatient care, medication will be dispensed to your child by nursing staff.</p>'
            . '<p>If your child is in homecare and prescribed new medication to support their recovery, we can liaise with local pharmacies to arrange for the prescribed medication to be provided to you at no extra charge.</p>',
        'What about my child\'s education?' => '<p>Young people receiving inpatient care in Willow Grove have regular access to a dedicated classroom space and teaching staff throughout the school year. These staff facilitate learning both on an individual and group basis. If permitted to do so, our team can also liaise with your child\'s school throughout their admission to ensure continuity.</p>'
            . '<p>If your child is in homecare, education is tailored to their individual needs. For example, some young people attend school in the mornings, and avail of homecare supports in the afternoon.</p>'
            . '<p>Planning for a transition back to school may also take place for young people not attending school at the time of admission.</p>',
        'When can I visit my child in hospital?' => '<p>If your child is receiving inpatient care, visiting times fall between 6pm and 8pm from Monday to Friday. At the weekends, visiting tends to take place between 2pm to 5pm and 6pm to 8pm.</p>'
            . '<p>Visiting guidelines are in place to ensure the safety and privacy of all young people in our care. All visitors must attend at the arranged time as this prevents disruption to the therapeutic programme.</p>'
            . '<p>On admission, a nurse will draw up a list of visitors in agreement with you and your child.</p>',
        'Can my child take a break from the hospital or homecare?' => '<p>If your child is receiving inpatient care in Willow Grove, any leave arrangements will be discussed with you as part of their care plan. Willow Grove is based on the main campus of St Patrick\'s University Hospital (SPUH): the team will let you know when leave is permitted on the SPUH campus grounds; this is usually after a week on the Willow Grove unit, where appropriate.</p>'
            . '<p>If your child is in homecare, please discuss with us in advance if you need to change availability or are planning any holidays. You and your child must be in the Republic of Ireland while your child is in homecare so that we can safely provide any appropriate supports needed if your child\'s mental health was to worsen.</p>',
        'How long will my child need care for?' => '<p>The length of admission to Willow Grove, either as an inpatient or in homecare, varies from young person to young person. Each young person\'s time in Willow Grove depends on the level of care needed, their personalised care plan, and their progress toward achieving their treatment goals.</p>',
        'What happens around the time of discharge?' => '<p>In general, our Willow Grove team will decide together with you and your child on when they are ready for discharge from either inpatient care or homecare.</p>'
            . '<p>Arrangements for follow-up care will be discussed with you and your child at a discharge planning meeting with the team before they are discharged. The team will provide you with the details of follow-up appointments, if available, and, if needed, a prescription for medication needed after discharge.</p>',
        'Who should I ask if I have questions?' => '<p>Your child will be assigned a key worker, who acts as a link between you, your child, and their care team.</p>'
            . '<p>We recommend that you link in with the key worker about any questions or concerns you may have in relation to your child\'s care.</p>',
    ]);

    $rows = [
        matrix_orlaith_hero_row('What to expect from your child\'s time in Willow Grove', '<p>Willow Grove is our specialised facility for adolescents here in St Patrick\'s Mental Health Services (SPMHS). The Willow Grove team provides mental healthcare and treatment to young people aged 12 to 17 through both inpatient and homecare services. Here, you\'ll find useful information if your child is being admitted to Willow Grove.</p>', $hero),
        matrix_orlaith_content_row('Personalised mental healthcare for your child', $intro, 'white', $image, 'image_left', [
            'primary_button' => matrix_orlaith_button('Typical day in hospital', $inpatient),
            'primary_button_variant' => 'filled',
            'secondary_button' => matrix_orlaith_button('Typical day in homecare', $homecare),
            'secondary_button_variant' => 'outline',
        ]),
        matrix_orlaith_content_row('FAQs', '<p>Find out more about your child\'s care below.</p>', 'cream'),
        $accordion,
        matrix_orlaith_video_row('Video resources', '', [
            ['url' => 'https://www.youtube.com/watch?v=Rghf8x0TfT4'],
            ['url' => 'https://www.youtube.com/watch?v=gsGM112v3bw'],
            ['url' => 'https://www.youtube.com/watch?v=DTq80NIFp9E', 'title' => 'Adolescent mental health'],
            ['url' => 'https://www.youtube.com/watch?v=JKX7A0FE1LI', 'title' => 'Information and advocacy for family recovery'],
        ]),
        matrix_orlaith_useful_links_row([
            'Your care with Willow Grove' => $willow,
            'Your stay in hospital as an adolescent' => $inpatient,
            'Your time in homecare as an adolescent' => $homecare,
        ]),
    ];

    matrix_orlaith_save_page($post_id, $rows, true, $hero);
}

function matrix_orlaith_rebuild_involuntary_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $what = '<p>An involuntary admission is when a person is admitted to hospital for mental health treatment against their own wishes.</p>'
        . '<p>Involuntary admissions are covered under the Mental Health Act 2001. Under this law, there are very strict conditions that must apply before a person can be involuntarily admitted to hospital.</p>'
        . '<p>You can <a href="https://www.citizensinformation.ie/en/health/health-services/mental-health/admission-to-a-psychiatric-hospital/" target="_blank" rel="noopener">find out more about the laws and conditions for involuntary admissions here</a>.</p>'
        . '<p>As an approved centre regulated by the Mental Health Commission (MHC), mental healthcare and treatment can be provided for involuntary adult service users in St Patrick\'s University Hospital (SPUH).</p>'
        . '<p>Involuntary admissions take place with the person\'s best interests at heart, and care is delivered in a way that respects the service user\'s dignity, ability to consent, and right to freedom, and is fully compliant with the legal and procedural requirements set out in law.</p>';

    $how = '<p>Certain people, as set out in law, can apply to a GP for another person to be medically assessed for involuntary admission. They <strong>must have seen the person within 48 hours</strong> before making the application.</p>'
        . '<p>The applicant must have completed either <a href="https://www.mhcirl.ie/what-we-do/mental-health-tribunals/statutory-forms" target="_blank" rel="noopener">Form 1, 2, 3 or 4 ("Application to a Registered Medical Practitioner") from the MHC</a>. The form the applicant completes depends on their relationship to the person.</p>'
        . '<p>As a GP, when you get a completed application form, you must see and <strong>examine the person within 24 hours</strong> of receiving the form.</p>'
        . '<p>If you feel the person requires mental health treatment and wish to refer them to SPUH, please make sure you have access to Form 5 ("Recommendation by a Registered Medical Practitioner") from the MHC and the SPMHS adult referral form, then take the steps below.</p>';

    $form5 = function_exists('matrix_migrate_live_url')
        ? matrix_migrate_live_url('/media/1711/avoiding_pitfalls_in_filling_form_5_of_the_mental_health_act_2001_updated_december_2011-1.pdf')
        : 'https://www.stpatricks.ie/media/1711/avoiding_pitfalls_in_filling_form_5_of_the_mental_health_act_2001_updated_december_2011-1.pdf';

    $accordion = matrix_orlaith_accordion_row([
        'Contact SPUH' => '<p>You can contact our Referral and Assessment Service by phoning 01 249 3635 or 01 249 3640 between 9am and 5pm, Monday to Friday. Outside of these hours, you can call 01 249 3200 and you will be put in touch with the Assistant Director of Nursing (ADON). The ADON may put you in touch with the on-call registrar if needed.</p>',
        'Check all forms' => '<p>You must ensure that the relevant application form (Form 1, 2, 3 or 4), recommendation form (Form 5) and SPMHS referral form are completed fully and correctly. If forms are not completed correctly, it can mean that we are not able to process the application and admit the person, or can lead to the person\'s unlawful detention in hospital.</p><p>The Irish College of General Practitioners (ICGP) also provides a guidance document on avoiding common errors when using Form 5.</p>',
        'Send all forms' => '<p>You will need to send the completed application form, recommendation form, and SPMHS referral form to SPUH. The forms should only be sent by secure email by using Healthmail; the email address to contact through Healthmail is <a href="mailto:referrals@stpatricks.ie">referrals@stpatricks.ie</a>.</p><p>Please note that, in the person\'s best interest, it is vital that forms are sent before you advise your patient to come to SPUH. There is a <strong>seven-day limit on Form 5</strong>.</p>',
        'Support your patient\'s admission to hospital' => '<p>You can request that SPUH arranges for assistance in bringing your patient to hospital: if this is needed, please let us know through Healthmail.</p><p>You must ensure that original copies of the correctly completed MHC forms accompany your patient to SPUH. Without these forms, we are unable to process the application and admit the person.</p>',
    ]);

    $next = '<p>When a person is admitted to SPUH under an involuntary admission, a consultant psychiatrist will examine your patient within 24 hours of their admission. If they feel your patient requires care in hospital but your patient is not willing for this, your patient will be admitted against their will as set out in law.</p>'
        . '<p>Please do not hesitate to contact us if you have any queries about the involuntary admissions process at any stage.</p>';

    $hcp_term = get_term_by('slug', 'hp-referrals', 'faq_category');
    $faq_cat = $hcp_term instanceof WP_Term ? (int) $hcp_term->term_id : 0;

    $rows = [
        matrix_orlaith_hero_row('Involuntary admissions', '<p>Here, we provide information and guidance to support GPs who need to refer a patient for an involuntary admission so that the process is as smooth and timely as it can be and the person can get the care they need as soon as possible.</p>', 0),
        matrix_orlaith_content_row('What is an involuntary admission?', $what, 'white'),
        matrix_orlaith_content_row('How are referrals for involuntary admissions made?', $how, 'cream', 0, 'image_left', [
            'primary_button' => matrix_orlaith_button('MHC statutory forms', 'https://www.mhcirl.ie/what-we-do/mental-health-tribunals/statutory-forms', '_blank'),
            'primary_button_variant' => 'filled',
            'secondary_button' => matrix_orlaith_button('Form 5 guidance (PDF)', $form5, '_blank'),
            'secondary_button_variant' => 'outline',
        ]),
        $accordion,
        matrix_orlaith_content_row('Next steps and queries', $next, 'white'),
        [
            'acf_fc_layout' => 'faqs',
            'heading' => 'FAQs for healthcare professionals',
            'heading_tag' => 'h2',
            'show_heading' => 1,
            'layout_style' => 'default',
            'source_mode' => $faq_cat > 0 ? 'category' : 'all',
            'selected_faqs' => [],
            'selected_faq_categories' => $faq_cat > 0 ? [$faq_cat] : [],
            'empty_state_message' => 'No FAQs are available right now.',
            'section_background' => '#FBFAF7',
            'heading_color' => '#1E244B',
            'underline_color' => '#6FC9C0',
            'item_background' => '#FFFFFF',
            'open_item_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
            'question_color' => '#1E244B',
            'answer_color' => '#08284B',
        ],
    ];

    matrix_orlaith_save_page($post_id, $rows, true, 0);
}

function matrix_orlaith_rebuild_safeguarding_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $hero = matrix_orlaith_find_image(3947, 'Safeguarding.png');
    $pdf = static function (int $id): string {
        return $id > 0 ? (string) wp_get_attachment_url($id) : '';
    };

    $protection = '<p>It is the policy of SPMHS to safeguard the welfare of all children by protecting them from physical, sexual and emotional harm and from neglect. The welfare of children is of paramount concern.</p>'
        . '<p>SPMHS takes all possible care in its recruitment processes to employ people who will not abuse or neglect users of our services. SPMHS provides ongoing training to staff and volunteers so that they are aware of signs and risks of abuse and neglect of children and so that they know the reporting structure of SPMHS.</p>'
        . '<p>When reports of concerns or allegations of abuse or neglect, past or present, are made to staff, these will be followed up and, if reasonable grounds for concern are established, these will be reported to Tusla Child and Family Services and/or to An Garda Síochána for assessment and investigation.</p>'
        . '<p>Any form of behaviour that harms a child such as neglect, physical, sexual and/or emotional abuse is unacceptable. All concerns will be followed up in a fair and impartial manner and in accordance with the principles of natural justice.</p>'
        . '<p>Children have the right to be protected, treated with respect, listened to and have their views taken into consideration, regardless of all other considerations.</p>';

    $statement = '<p>SPMHS provides mental healthcare to young people between 12 and 17 years of age on an '
        . matrix_orlaith_a('service-users-and-visitors/your-care-with-willow-grove', 'inpatient')
        . ' basis (Willow Grove Adolescent Unit) and outpatient basis (Willow Grove Adolescent Service; '
        . matrix_orlaith_a('what-we-offer/outpatient-care-dean-clinics', 'Dean Clinics')
        . ' in Lucan and Cork).</p>'
        . '<p>In accordance with the requirement of the Children\'s Act 2015, <em>Children\'s First: National Guidance for the Protection and Welfare of Children 2017</em>, SPMHS has developed this Child Safeguarding Statement.</p>';

    $pdf_link = static function (int $id, string $label) use ($pdf): string {
        $url = $pdf($id);
        if ($url === '') {
            return '';
        }

        return '<p><a href="' . esc_url($url) . '" target="_blank" rel="noopener">' . esc_html($label) . '</a></p>';
    };

    $accordion = matrix_orlaith_accordion_row([
        'Guiding principles' => '<p>SPMHS recognises that the welfare and protection of children is of paramount importance, regardless of all other considerations.</p>'
            . '<p>SPMHS commits to the following:</p>'
            . '<ul>'
            . '<li>Fully comply with statutory obligations under the Children First Act 2015</li>'
            . '<li>Fully cooperate with the relevant authorities in relation to child protection and welfare</li>'
            . '<li>Adopt safe practices to minimise the possibility of harm or accidents happening to children</li>'
            . '<li>Fully respect confidentiality requirements in dealing with child protection matters.</li>'
            . '</ul>',
        'Designated Liaison Person' => '<p>A Designated Liaison Person (DLP) is a resource person for any staff member or volunteer who has child protection concerns. The DLP facilitates liaison with outside agencies. They are responsible for ensuring that reporting procedures within SPMHS are followed. They record all concerns brought to their attention and the actions taken in relation to a concern.</p>'
            . '<p>The DLP in SPMHS is Sheila O\'Connor (<a href="mailto:soconnor@stpatricks.ie">soconnor@stpatricks.ie</a>).</p>'
            . '<p>The Deputy DLP in SPMHS is Elaine Donnelly (Head of Social Work (CORU 003072); <a href="mailto:edonnelly@stpatricks.ie">edonnelly@stpatricks.ie</a>).</p>'
            . '<p>SPMHS has undertaken a risk assessment to identify any potential harm to a child while availing of our services. This is available in the SPMHS Child Protection Policy.</p>',
        'Procedures' => '<p>SPMHS has a variety of policies and protocols in place to ensure the safeguarding of children and adolescents to whom we provide services (listed in the Child Protection Policy).</p>'
            . '<p>On admission to Willow Grove Adolescent Unit, parents and young people receive an information booklet which provides an overview of the service and expectations of service users, their families and staff as part of the service user\'s care and treatment.</p>'
            . '<p>SPMHS operates a robust incident reporting system which ensures issues identified are addressed promptly and changes are implemented when required.</p>'
            . '<p>All procedures and policies are available on request.</p>',
        'Implementation' => '<p>We recognise that implementation is an ongoing process. SPMHS is committed to the implementation of this Child Safeguarding Statement and the procedures that support our intention to keep children safe from harm while availing of our service.</p>'
            . '<p>The below Child Safeguarding Statements were previously reviewed on 21 January 2025. They have been reviewed most recently on 17 September 2025. They will next be reviewed on 17 September 2027, or as soon as practicable after there has been a material change in any matter to which the statement refers.</p>'
            . $pdf_link(873, 'SPMHS | Child Safeguarding Statement')
            . $pdf_link(914, 'Willow Grove | Child Safeguarding Statement')
            . $pdf_link(909, 'Dean Clinics Dublin | Child Safeguarding Statement')
            . $pdf_link(916, 'Dean Clinic Cork | Child Safeguarding Statement'),
    ]);

    $rows = [
        matrix_orlaith_hero_row('Safeguarding', '<p>Here at St Patrick\'s Mental Health Services (SPMHS), it is our policy to safeguard child welfare. See our Child Protection and Welfare Statement and our Child Safeguarding Statements here.</p>', $hero),
        matrix_orlaith_content_row('Child Protection and Welfare Statement', $protection, 'white'),
        matrix_orlaith_content_row('Child Safeguarding Statement', $statement, 'cream'),
        $accordion,
        matrix_orlaith_useful_links_row([
            'Policies and Publications' => 'about-us/policies-and-publications',
            'Your care with Willow Grove' => 'service-users-and-visitors/your-care-with-willow-grove',
        ]),
    ];

    matrix_orlaith_save_page($post_id, $rows, true, $hero);
}

function matrix_orlaith_rebuild_payment_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $hero = matrix_orlaith_find_image(3949, 'Cover for our services.png');
    $insurance = matrix_orlaith_permalink('getting-help/insurance-information/health-insurance-plans');

    $how = '<p>We offer a number of care pathways, including '
        . matrix_orlaith_a('inpatient-care', 'inpatient care') . ', '
        . matrix_orlaith_a('what-we-offer/st-patricks-at-home', 'homecare services') . ', '
        . matrix_orlaith_a('what-we-offer/outpatient-care-dean-clinics', 'outpatient care') . ', and '
        . matrix_orlaith_a('what-we-offer/day-programmes', 'day programmes')
        . '. We are an independent, not-for-profit organisation, and we do not receive government funding.</p>'
        . '<p>' . matrix_orlaith_a('getting-help', 'Talking to your GP') . ' is the best first step to accessing our mental healthcare and treatment.</p>'
        . '<p>Our services can be covered in three key ways: private health insurance, self-funding, and the Health Service Executive (HSE) in certain circumstances.</p>';

    $accordion = matrix_orlaith_accordion_row([
        'Health insurance cover' => '<p>We are covered by all health insurance companies in Ireland (Irish Life Health, Laya or VHI). Where it is appropriate, we ' . matrix_orlaith_a('about-us/advocacy', 'advocate on behalf of our service users') . ' for mental healthcare benefits provided by insurance companies and other related issues in the sector.</p><p>If you have been referred to our services, your health insurer will be best placed to confirm your level of cover. You will need the name of the policy holder, policy number and your date of birth before you call your health insurer.</p><p>Irish Life: 01 562 5100<br>LAYA: 021 202 2000<br>VHI: 056 444 4444</p><p>If you would like to ask any additional questions specific to insurance cover for your SPMHS referral, please <a href="tel:012493533">call our Finance Department on 01 249 3533</a>.</p>',
        'Self-funding' => '<p>If you do not have private health insurance, our services can be accessed through self-funding, which means you cover the costs of your care and treatment directly. You can <a href="tel:012493533">phone our Finance team on 01 249 3533</a>.</p>',
        'HSE referrals' => '<p>We accept referrals from the HSE. If you are under the care of a HSE service, in certain circumstances, your HSE clinical team may find it is appropriate to refer you to our services.</p><p>Your GP can refer you to HSE services. You can <a href="https://www2.hse.ie/services/mental-health/" target="_blank" rel="noopener">find out more about HSE services</a> or call the YourMentalHealth information line any time on 1800 111 888.</p>',
    ]);

    $rows = [
        matrix_orlaith_hero_row('Payment for our services', '<p>At St Patrick\'s Mental Health Services (SPMHS), we aim to give you as much information as we can to make it as easy as possible for you to avail of our mental health services.</p>', $hero),
        matrix_orlaith_content_row('How our services are covered', $how, 'white', 0, 'image_left', [
            'primary_button' => matrix_orlaith_button('See our guide to insurance plans', $insurance),
            'primary_button_variant' => 'filled',
            'secondary_button' => matrix_orlaith_button('Call Finance', 'tel:012493533'),
            'secondary_button_variant' => 'outline',
        ]),
        $accordion,
    ];

    matrix_orlaith_save_page($post_id, $rows, true, $hero);
}

function matrix_orlaith_rebuild_schizophrenia_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $programme = matrix_orlaith_permalink('care-treatment/psychosis-recovery-programme');
    $hero_image = matrix_orlaith_find_image(1060, 'psychosis.jpg');
    $existing = get_field('flexible_content_blocks', $post_id);
    $useful = null;
    if (is_array($existing)) {
        foreach ($existing as $row) {
            if (($row['acf_fc_layout'] ?? '') === 'useful_links') {
                $useful = $row;
                $useful['heading'] = 'Useful links';
                break;
            }
        }
    }

    $what = '<p>Schizophrenia is a long-term, serious mental health difficulty where you may not be able to tell the difference between your own thoughts or beliefs and reality. It has a number of symptoms which affect your thinking, feelings and actions, and can impact how you are able to function in day-to-day life.</p>'
        . '<p>Schizophrenia is often described as a type of psychosis. This means that sometimes you can struggle with what is real and what is not.</p>'
        . '<p>Schizophrenia is an enduring mental health difficulty, which can need lifelong treatment. Please know that schizophrenia can be managed well with the right treatment, especially when it is started early.</p>';

    $symptoms_intro = '<p>There are a number of symptoms that can be part of schizophrenia. Not everyone will experience all these symptoms. You may have times where your symptoms are severe, and other periods with few or no symptoms.</p>';

    $symptoms = matrix_orlaith_accordion_row([
        'Hallucinations' => '<p>Hallucinations are where you hear, see, feel or touch something that does not exist in reality, but that is very real to you. The most common type of hallucination is hearing voices.</p>',
        'Delusions' => '<p>Delusions are unusual beliefs. This could be believing something that isn\'t true or that\'s based on a mistaken or strange point of view. Delusions can start very suddenly, or may develop over time.</p>',
        'Disorganised thinking' => '<p>Disorganised thinking is where your thoughts are very confused or jumbled. It can be difficult for you to follow your thoughts and to focus or concentrate. This affects the way you communicate.</p>',
        'Changed behaviour' => '<p>You may act differently to how you normally behave when you are having symptoms of schizophrenia. This can take a number of forms, such as becoming more agitated, acting more unpredictably, or finding it hard to do everyday things.</p>',
        'Negative symptoms' => '<p>Negative symptoms are when you are not able to act or function in the way you normally do. You may have a lack of energy or motivation, and may lose interest in the activities and relationships you usually enjoy.</p>',
    ]);

    $treatment = '<p>Care for schizophrenia usually involves a range of treatments. These depend on your experience of schizophrenia, how severe your symptoms are, how long they last for and other factors.</p>'
        . '<p>Treatment may include inpatient hospital care, but can also be delivered through day patient or outpatient services. It often involves a mix of medication; psychological or talk therapy; family and social supports; occupational therapy; and physical health monitoring.</p>'
        . '<p>Here at St Patrick\'s Mental Health Services, we provide care for schizophrenia through inpatient and homecare services. Our Psychosis Recovery Programme provides care at inpatient, outpatient and day patient level, along with aftercare services.</p>';

    $faqs = matrix_orlaith_accordion_row([
        'Who does schizophrenia affect?' => '<p>Schizophrenia can develop from adolescence or early adulthood. Symptoms of schizophrenia can begin earlier for men, with symptoms usually beginning in the late 20s or early 30s for women. Some people may not be diagnosed with schizophrenia until later in life.</p>',
        'What causes schizophrenia?' => '<p>There is no single cause of schizophrenia identified. Research suggests it may be caused by a mix of genetics and environmental factors. Some things can lead to a higher risk of schizophrenia, such as heavy drug use or a major life stress.</p>',
        'Is schizophrenia the same as psychosis?' => '<p>Schizophrenia and psychosis are related, but different. Psychosis is a symptom or experience, and can have many different causes. Schizophrenia is a long-term mental health difficulty which can have a number of symptoms, including psychosis. However, not everyone who has schizophrenia will experience psychosis.</p>',
        'When should you seek help for schizophrenia?' => '<p>Getting support for schizophrenia as early as possible is important. If you are worried you are developing or experiencing symptoms of schizophrenia, talk to a family member or friend, and visit your GP as soon as you can. If you need urgent help, you can call emergency services at 999 or 112, or visit your nearest Accident and Emergency (A&amp;E) hospital department.</p>',
        'What should I do if I am worried about someone with schizophrenia?' => '<p>If you have concerns that a loved one or friend is experiencing schizophrenia, you could contact their GP or, if they are already receiving care, their mental health worker or care team. If you feel they need urgent help or are worried about their safety, please call emergency services on 999 or 112, or go with them to your nearest A&amp;E Department.</p>',
    ]);

    $rows = [
        matrix_orlaith_hero_row('Schizophrenia', '<p>Schizophrenia is a complex mental health difficulty which can affect your connection with reality and how you think, feel and behave.</p>', $hero_image),
        matrix_orlaith_content_row('What is schizophrenia?', $what, 'white'),
        matrix_orlaith_content_row('What are the symptoms of schizophrenia?', $symptoms_intro, 'cream'),
        $symptoms,
        matrix_orlaith_content_row('How is schizophrenia treated?', $treatment, 'white', 0, 'image_left', [
            'primary_button' => matrix_orlaith_button('Psychosis Recovery Programme', $programme),
            'primary_button_variant' => 'filled',
        ]),
        matrix_orlaith_content_row('Frequently asked questions about schizophrenia', '', 'cream'),
        $faqs,
    ];
    if (is_array($useful)) {
        $rows[] = $useful;
    }

    matrix_orlaith_save_page($post_id, $rows, true, $hero_image);
    matrix_orlaith_set_seo($post_id, 'Schizophrenia | St Patrick\'s Mental Health Services', 'Learn more about schizophrenia, including symptoms and treatment options.');
}

function matrix_orlaith_rebuild_psychosis_rename_cleanup(int $post_id): void
{
    if ($post_id <= 0) {
        return;
    }

    if (function_exists('matrix_orlaith_rebuild_psychosis_layout')) {
        matrix_orlaith_rebuild_psychosis_layout($post_id);
    }

    wp_update_post([
        'ID' => $post_id,
        'post_title' => 'Psychosis',
    ]);
}

function matrix_orlaith_rebuild_all_remaining_august_pages(): void
{
    $map = [
        'service-users-and-visitors/service-user-participation/service-user-advisory-network' => 'matrix_orlaith_rebuild_suan_layout',
        'service-users-and-visitors/service-user-participation/family-carers-and-supporters-advisory-network' => 'matrix_orlaith_rebuild_fcs_layout',
        'service-users-and-visitors/feedback-and-comments/service-user-experience-survey' => 'matrix_orlaith_rebuild_survey_layout',
        'service-users-and-visitors/your-care-with-willow-grove' => 'matrix_orlaith_rebuild_willow_parent_layout',
        'service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent' => 'matrix_orlaith_rebuild_adolescent_stay_layout',
        'service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent' => 'matrix_orlaith_rebuild_adolescent_homecare_layout',
        'service-users-and-visitors/your-care-with-willow-grove/information-for-your-family' => 'matrix_orlaith_rebuild_family_info_layout',
        'healthcare-professionals/involuntary-admissions' => 'matrix_orlaith_rebuild_involuntary_layout',
        'about-us/policies-and-publications/safeguarding' => 'matrix_orlaith_rebuild_safeguarding_layout',
        'about-us/payment' => 'matrix_orlaith_rebuild_payment_layout',
    ];

    foreach ($map as $path => $fn) {
        $page = get_page_by_path($path);
        if ($page instanceof WP_Post) {
            $fn((int) $page->ID);
        }
    }

    $schizophrenia = get_posts([
        'post_type' => 'mental_health',
        'name' => 'schizophrenia',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    if ($schizophrenia !== []) {
        matrix_orlaith_rebuild_schizophrenia_layout((int) $schizophrenia[0]->ID);
    }

    $psychosis = get_posts([
        'post_type' => 'mental_health',
        'name' => 'schizophrenia-psychosis',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    if ($psychosis !== []) {
        matrix_orlaith_rebuild_psychosis_rename_cleanup((int) $psychosis[0]->ID);
    }

    $carers = get_page_by_path('service-users-and-visitors/carers-and-supporters');
    if ($carers instanceof WP_Post && function_exists('matrix_orlaith_rebuild_carers_layout')) {
        matrix_orlaith_rebuild_carers_layout((int) $carers->ID);
    }
}
