<?php

$post_id = 329;
$image_milestone = 381;
$image_milestone_alt = 382;

$lorem = '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.</p>';
$intro = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique. Duis cursus, mi quis viverra ornare, eros dolor interdum nulla, ut commodo diam libero vitae erat.</p>';

$cta = [
    'title' => 'Supporting material CTA',
    'url' => '/',
    'target' => '_self',
];

$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows)) {
    $rows = [];
}

$rows = array_values(array_filter($rows, static function ($row) {
    return ($row['acf_fc_layout'] ?? '') !== 'timeline';
}));

$rows[] = [
    'acf_fc_layout' => 'timeline',
    'heading_tag' => 'h2',
    'heading' => 'Our History, timeline title - medium length',
    'intro' => $intro,
    'timeline_items' => [
        [
            'side' => 'left',
            'event_date_label' => '2.8.1746',
            'item_heading' => 'Short heading Lorem ipsum dolor',
            'item_heading_tag' => 'h3',
            'item_text' => $lorem,
        ],
        [
            'side' => 'right',
            'event_date_label' => '2.8.1946',
            'image' => $image_milestone,
            'item_heading' => 'H3. Milestone',
            'item_heading_tag' => 'h3',
            'item_text' => $lorem,
            'cta_link' => $cta,
        ],
        [
            'side' => 'left',
            'event_date_label' => '2.8.2000',
            'item_heading' => 'H3. Milestone',
            'item_heading_tag' => 'h3',
            'item_text' => $lorem,
            'cta_link' => $cta,
        ],
        [
            'side' => 'right',
            'event_date_label' => '2.8.2024',
            'item_heading' => 'H3. Milestone',
            'item_heading_tag' => 'h3',
            'item_text' => $lorem,
            'cta_link' => $cta,
        ],
        [
            'side' => 'left',
            'event_date_label' => '2.8.2024',
            'image' => $image_milestone_alt,
            'item_heading' => 'H3. Milestone',
            'item_heading_tag' => 'h3',
            'item_text' => $lorem,
            'cta_link' => $cta,
        ],
        [
            'side' => 'right',
            'event_date_label' => '2.8.2024',
            'item_heading' => 'H3. Milestone',
            'item_heading_tag' => 'h3',
            'item_text' => $lorem,
            'cta_link' => $cta,
        ],
    ],
    'footer_button_link' => [
        'title' => 'Our Present and Future',
        'url' => '/',
        'target' => '_self',
    ],
    'card_background_color' => '#E4F4D6',
    'timeline_accent_color' => '#6FC9C0',
];

$updated = update_field('flexible_content_blocks', $rows, $post_id);

if (! $updated) {
    WP_CLI::error('Failed to update flexible content for page ' . $post_id);
}

WP_CLI::success('Seeded timeline block on page ' . $post_id . ' with ' . count($rows) . ' total rows.');
