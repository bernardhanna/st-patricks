<?php

/**
 * SUAS page layout from Orlaith's August draft.
 *
 * Image + text content blocks alternate left/right. SUAN and FCS are
 * content-block buttons. Rank Math title and description come from the draft.
 *
 * @param int $post_id page ID
 */
function matrix_orlaith_rebuild_suas_layout(int $post_id): void
{
    if ($post_id <= 0 || ! function_exists('update_field')) {
        return;
    }

    $permalink = static function (string $path): string {
        $path = trim($path, '/');
        $page = get_page_by_path($path, OBJECT, [
            'page',
            'post',
            'mental_health',
            'care_treatment',
            'get_involved',
            'locations',
        ]);

        if ($page instanceof WP_Post && $page->post_status === 'publish') {
            return (string) get_permalink($page);
        }

        $by_name = get_posts([
            'name' => basename($path),
            'post_type' => ['page', 'post', 'get_involved'],
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        if ($by_name !== []) {
            return (string) get_permalink($by_name[0]);
        }

        return home_url('/' . $path . '/');
    };

    $link = static function (string $path, string $label) use ($permalink): string {
        return '<a href="' . esc_url($permalink($path)) . '">' . esc_html($label) . '</a>';
    };

    $find_image = static function (int $preferred_id, string $filename): int {
        if ($preferred_id > 0 && get_post_type($preferred_id) === 'attachment') {
            return $preferred_id;
        }

        $found = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'title' => preg_replace('/\.[^.]+$/', '', $filename),
        ]);

        return $found !== [] ? (int) $found[0] : 0;
    };

    $hero_image = $find_image(3932, 'SUAS.png');
    $what_image = $find_image(740, 'st-patricks-mental-health-services-suas.jpg');
    $join_who_image = $find_image(1087, 'talking-to-people-with-depression.jpg');
    $involved_image = $find_image(845, 'st-patricks-mental-health-multidisciplinary-team.jpg');
    $how_image = $find_image(1099, 'ag1o1085');

    $content = static function (
        string $heading,
        string $html,
        string $background,
        int $image_id,
        string $layout_style,
        array $extra = []
    ): array {
        $has_image = $image_id > 0;
        $row = [
            'acf_fc_layout' => 'content',
            'heading' => $heading,
            'heading_tag' => 'h2',
            'accent_position' => 'below_heading',
            'intro_text' => '',
            'content' => $html,
            'background_type' => $background,
            'color_scheme' => 'default',
            'column_layout' => $has_image ? 'two_column' : 'one_column',
            'layout_style' => $layout_style,
            'image_height_mode' => 'match_text',
            'text_width' => 'full',
            'image' => $has_image ? $image_id : '',
        ];

        return array_merge($row, $extra);
    };

    $intro = '<p>Our Service User and Supporters Council (SUAS) ensures that our service users and those who support them are directly involved in all we do.</p>';

    $what_html = '<p>SUAS is a forum for service user participation, which, '
        . $link('20-years-of-service-user-engagement', 'for over 20 years')
        . ' here at St Patrick\'s Mental Health Services (SPMHS), has directly informed how we develop our services.</p>'
        . '<p>The main focus of SUAS is to help ensure that the needs of our service users and those who support them are at the centre of every aspect of the '
        . $link('what-we-offer', 'care and treatment we deliver')
        . '. Members of SUAS capture and represent the thoughts and opinions of our service users and their supporters when changes and improvements in our services are being considered.</p>'
        . '<p>SUAS works with our '
        . $link('about-us/our-team', 'management, Board of Governors, and clinicians')
        . ' to share the views and perspectives of the people using our services on a wide range of different topics. Members of SUAS are regularly asked to review and provide input on plans being considered that will impact the experience of our service users and their supporters. They also make suggestions to management for changes they feel will help improve the overall experience of care and treatment in SPMHS.</p>'
        . '<p>Some members of SUAS also take part in activities such as delivering morning lectures to current service users, writing '
        . $link('news-and-events', 'articles')
        . ' for our website and newsletters, and sharing their lived experiences in media interviews and other events.</p>';

    $who_html = '<p>Membership of SUAS is open to people aged over 18 who have previously used our services as an '
        . $link('inpatient-care', 'inpatient')
        . ' or in '
        . $link('what-we-offer/st-patricks-at-home', 'homecare')
        . ', '
        . $link('what-we-offer/outpatient-care-dean-clinics', 'outpatient')
        . ' or '
        . $link('what-we-offer/day-programmes', 'day patient')
        . ', and to those who support them.</p>'
        . '<p>As it is important to focus your energy on recovery when in hospital, you need to have been discharged from inpatient care to join; if, during your involvement in SUAS, you need to go back into hospital, we would ask that you take a break to focus on your recovery and re-join us when you feel able. You can take part in SUAS while you are attending day services or outpatient care.</p>';

    $involved_html = '<p>Members of SUAS are required to attend monthly meetings. These take place on the first Wednesday of each month and are currently held online from 5.30pm to 7pm.</p>'
        . '<p>Ahead of these meetings, members are expected to read documents that are relevant to the agenda to help inform discussion and strengthen participation at meetings.</p>'
        . '<p>SUAS consults on projects and activities that may be sensitive. Members are therefore asked to sign a confidentiality agreement when joining SUAS to keep information about the activities private.</p>';

    $how_html = '<p>There is an official application process for SUAS as it is a formal committee.</p>'
        . '<p>You can start the process by writing an application letter outlining your interest in joining SUAS. If you are a service user, we require this letter to be accompanied by a letter of support from your SPMHS consultant. This is a safeguarding measure to make sure that, in their opinion, joining SUAS will not affect your recovery.</p>'
        . '<p>When we receive your application and support letters, these are presented to our Board of Governors to be approved.</p>'
        . '<p>Following approval, you will then be formally invited to join SUAS and begin attending meetings. Before attending your first meeting, you will meet with our Service User Engagement Lead to help you become more familiar with the projects SUAS is working on.</p>'
        . '<p>If you have any questions about SUAS or you would like to register your interest, you can contact Siobhan Fitzharris, Service User Engagement Lead, by emailing <a href="mailto:sfitzharris@stpatricks.ie">sfitzharris@stpatricks.ie</a>.</p>'
        . '<p>If you would like to contribute to service development in a more flexible way, consider joining our Service User Advisory Network (SUAN) or Family, Carers and Supporters (FCS) Advisory Network. These advisory networks offer opportunities to take part in consultations, focus groups and projects as they come up, without the formal commitments of SUAS membership.</p>';

    $suan_url = $permalink('service-users-and-visitors/service-user-participation/service-user-advisory-network');
    $fcs_url = $permalink('service-users-and-visitors/service-user-participation/family-carers-and-supporters-advisory-network');

    $rows = [
        [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'layout_style' => $hero_image > 0 ? 'image_split' : 'title_accent',
            'show_breadcrumbs' => 1,
            'breadcrumb_source' => 'auto',
            'current_crumb_label' => 'Service User and Supporters Council',
            'heading_tag' => 'h1',
            'heading' => 'Service User and Supporters Council',
            'content' => $intro,
            'hero_image' => $hero_image > 0 ? $hero_image : '',
            'primary_button' => [
                'title' => '',
                'url' => '',
                'target' => '',
            ],
            'text_max_width' => 'default',
            'heading_max_width' => 'default',
            'background_color' => '#C6ECF4',
            'breadcrumb_background_color' => '#F1F8F9',
            'heading_color' => '#08284B',
            'text_color' => '#08284B',
        ],
        $content('What is SUAS?', $what_html, 'white', $what_image, 'image_left'),
        $content('Who can join SUAS?', $who_html, 'cream', $join_who_image, 'image_right'),
        $content('What\'s involved in being a member of SUAS?', $involved_html, 'white', $involved_image, 'image_left'),
        $content('How do I join SUAS?', $how_html, 'cream', $how_image, 'image_right', [
            'primary_button' => [
                'title' => 'See more on SUAN',
                'url' => $suan_url,
                'target' => '',
            ],
            'primary_button_variant' => 'filled',
            'secondary_button' => [
                'title' => 'Learn about the FCS Advisory Network',
                'url' => $fcs_url,
                'target' => '',
            ],
            'secondary_button_variant' => 'outline',
        ]),
    ];

    update_field('hero_content_blocks', [], $post_id);
    update_field('flexible_content_blocks', $rows, $post_id);
    update_post_meta($post_id, '_matrix_orlaith_suas_layout', gmdate('c'));
    update_post_meta($post_id, 'rank_math_title', 'Service User and Supporters Council | St Patrick\'s Mental Health Services');
    update_post_meta($post_id, 'rank_math_description', 'Find out more about our Service User and Supporters Council at St Patrick\'s Mental Health Services, including what it does and how to join.');
    wp_update_post([
        'ID' => $post_id,
        'post_excerpt' => 'Find out more about our Service User and Supporters Council at St Patrick\'s Mental Health Services, including what it does and how to join.',
    ]);

    if ($hero_image > 0) {
        set_post_thumbnail($post_id, $hero_image);
    }

    if (class_exists('Matrix_Flexible_Pages')) {
        Matrix_Flexible_Pages::set_flexible_page($post_id, true);
    }
}
