<?php

$post_id = 329;
$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows)) {
    $rows = [];
}

$shared_body = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>';

$intro = '<p>Our vision is to see a society where all citizens are empowered and given the opportunity to live mentally healthy lives.</p>';

$variants = [
    [
        'acf_fc_layout' => 'content',
        'heading' => "Welcome to St Patrick's Mental Health Services",
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => $shared_body,
        'layout_style' => 'image_left',
        'background_type' => 'light_blue',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Our Vision and Mission',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $intro,
        'content' => $shared_body,
        'layout_style' => 'image_left',
        'background_type' => 'white',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Our Commitment',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $intro,
        'content' => $shared_body,
        'layout_style' => 'image_right',
        'background_type' => 'cream',
        'secondary_button' => [
            'title' => 'Support Us',
            'url' => home_url('/support-us/'),
            'target' => '',
        ],
        'secondary_button_variant' => 'outline',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'A recognised leader in Mental Health',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'intro_text' => $intro,
        'content' => $shared_body,
        'layout_style' => 'image_left',
        'background_type' => 'white',
        'secondary_button' => [
            'title' => 'Our Future',
            'url' => home_url('/our-future/'),
            'target' => '',
        ],
        'secondary_button_variant' => 'outline',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Responsible Reporting',
        'heading_tag' => 'h2',
        'accent_position' => 'above_heading',
        'content' => $shared_body,
        'layout_style' => 'image_left',
        'background_type' => 'cream',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Highlight an upcoming Webinar in this box',
        'heading_tag' => 'h2',
        'accent_position' => 'below_heading',
        'content' => $shared_body,
        'layout_style' => 'image_right',
        'background_type' => 'cream',
        'primary_button' => [
            'title' => 'Sign-up to Webinar',
            'url' => home_url('/webinars/'),
            'target' => '',
        ],
        'primary_button_variant' => 'filled',
    ],
];

foreach ($variants as $variant) {
    $rows[] = $variant;
}

$updated = update_field('flexible_content_blocks', $rows, $post_id);

if (! $updated) {
    WP_CLI::error('Failed to update flexible content for page ' . $post_id);
}

WP_CLI::success('Added ' . count($variants) . ' content section variants to page ' . $post_id . '.');
