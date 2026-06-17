<?php

/**
 * Seed Register for your portal page (Figma 3279:18223 hero + 3279:18238 form).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-register-for-your-portal.php
 */

$post_id = (int) (get_page_by_path('register-for-your-portal')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at register-for-your-portal.');
    }

    exit(1);
}

$home = home_url('/');
$portal_login_url = 'https://eu.patientsknowbest.com/?team=stpatricksmhs';
$privacy_policy_url = home_url('/cookie-privacy-policy/');

$hero_intro = 'Your Portal is a secure, online platform that you can access on your computer, smartphone or tablet to support your path to mental health recovery. Register using the form below or log-in if you already have an account.';

$section_padding = [
    ['screen_size' => 'mob', 'padding_top' => '3', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '6.25', 'padding_bottom' => '6.25'],
];

$form_padding = [
    ['screen_size' => 'mob', 'padding_top' => '0', 'padding_bottom' => '3'],
    ['screen_size' => 'lg', 'padding_top' => '0', 'padding_bottom' => '6.25'],
];

$flexi_rows = [
    [
        'acf_fc_layout' => 'hero_with_breadcrumbs',
        'layout_style' => 'register_intro',
        'show_breadcrumbs' => 0,
        'heading_tag' => 'h1',
        'heading' => 'Register for Your Portal | Online Form',
        'content' => '<p>' . esc_html($hero_intro) . '</p>',
        'aside_heading' => 'Already registered?',
        'primary_button' => [
            'title' => 'Log In',
            'url' => $portal_login_url,
            'target' => '_blank',
        ],
        'primary_button_show_icon' => 1,
        'background_color' => '#FFFFFF',
        'heading_color' => '#08284B',
        'text_color' => '#08284B',
        'padding_settings' => $section_padding,
    ],
    [
        'acf_fc_layout' => 'contact_form',
        'form_style' => 'your_portal',
        'background_color' => '#FBF8F3',
        'submit_label' => 'Submit to Register for Your Portal',
        'success_message' => 'Thanks! Your registration request has been sent.',
        'form_name' => 'Your Portal Registration',
        'email_subject' => 'Your Portal – new registration request',
        'save_to_db' => 1,
        'privacy_policy_link' => [
            'title' => 'Privacy Policy',
            'url' => $privacy_policy_url,
            'target' => '_blank',
        ],
        'padding_settings' => $form_padding,
    ],
];

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Register for your portal page (%d) with %d flexi blocks.',
            $post_id,
            $saved_count
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Updated page %d but expected %d blocks, found %d.',
            $post_id,
            count($flexi_rows),
            $saved_count
        ));
    }
}
