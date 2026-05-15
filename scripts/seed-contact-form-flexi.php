<?php

$post_id = 329;
$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows)) {
    $rows = [];
}

$rows[] = [
    'acf_fc_layout' => 'contact_form',
    'form_style' => 'your_portal',
    'background_color' => '#FBF8F3',
    'submit_label' => 'Submit to Register for Your Portal',
    'success_message' => 'Thanks! Your registration request has been sent.',
    'form_name' => 'Your Portal Registration',
    'email_subject' => 'Your Portal – new registration request',
    'save_to_db' => 1,
];

$updated = update_field('flexible_content_blocks', $rows, $post_id);

if (! $updated) {
    WP_CLI::error('Failed to update flexible content for page ' . $post_id);
}

WP_CLI::success('Added contact_form block to page ' . $post_id . '.');
