<?php

/**
 * Ensure adult inpatient, outpatient, and day-programme referral pages have
 * working Healthlink and adult referral form CTAs (referral_action_cards).
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/fix-referral-action-ctas.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

$healthlink = 'https://www.healthlink.ie/';
$form_url = (string) wp_get_attachment_url(3913);
if ($form_url === '') {
    WP_CLI::error('Missing adult referral form attachment 3913');
}

$cards = [
    'acf_fc_layout' => 'referral_action_cards',
    'left_title' => 'Make a Referral via Healthlink',
    'left_description' => '<p>The fastest and most efficient method is to send referrals via Healthlink or through your practice management system.</p>',
    'left_button' => [
        'title' => 'Make a Referral via Healthlink',
        'url' => $healthlink,
        'target' => '_blank',
    ],
    'left_action_icon' => 'external',
    'right_title' => 'Download our Adult Referral Form',
    'right_description' => '<p>Complete our Adult Referral form and submit via Healthmail – referrals@stpatricks.ie</p>',
    'right_button' => [
        'title' => 'Download our Adult Referral Form',
        'url' => $form_url,
        'target' => '_blank',
    ],
    'right_action_icon' => 'download',
    'left_background_color' => '#CEF2EE',
    'right_background_color' => '#E4F4D6',
];

$ids = [201, 247, 253];
foreach ($ids as $post_id) {
    $rows = get_field('flexible_content_blocks', $post_id);
    if (! is_array($rows) || $rows === []) {
        WP_CLI::warning('No flexi on ' . $post_id);
        continue;
    }

    $new = [];
    $replaced = false;
    $skip_next_download = false;
    foreach ($rows as $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        $heading = trim((string) ($row['heading'] ?? ''));
        if ($layout === 'content' && $heading === 'Make a Referral via Healthlink') {
            $new[] = $cards;
            $replaced = true;
            $skip_next_download = true;
            continue;
        }
        if ($skip_next_download && $layout === 'content' && $heading === 'Download our Adult Referral Form') {
            $skip_next_download = false;
            continue;
        }
        if ($layout === 'referral_action_cards') {
            $new[] = $cards;
            $replaced = true;
            continue;
        }
        $new[] = $row;
    }

    if (! $replaced) {
        array_splice($new, 1, 0, [$cards]);
    }

    update_field('flexible_content_blocks', $new, $post_id);
    WP_CLI::success(get_the_title($post_id) . ' #' . $post_id);
}
