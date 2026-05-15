<?php

$post_id = 329;

$body = '<p>Visit our dedicated healthcare content - Placeholder text</p>'
    . '<p>3-4x lines max. Videos and images section as requested. Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna.</p>';

$rows = get_field('flexible_content_blocks', $post_id);
if (! is_array($rows)) {
    $rows = [];
}

$rows = array_values(array_filter($rows, static function ($row) {
    return ($row['acf_fc_layout'] ?? '') !== 'content_cta';
}));

$rows[] = [
    'acf_fc_layout' => 'content_cta',
    'heading_tag' => 'h2',
    'heading' => 'Are you a healthcare professional?',
    'body' => $body,
    'button_link' => [
        'title' => 'Healthcare professionals',
        'url' => '/',
        'target' => '_self',
    ],
    'background_type' => 'color',
    'background_color' => '#E9E2F7',
];

$updated = update_field('flexible_content_blocks', $rows, $post_id);

if (! $updated) {
    WP_CLI::error('Failed to update flexible content for page ' . $post_id);
}

WP_CLI::success('Seeded content_cta block on page ' . $post_id . ' with ' . count($rows) . ' total rows.');
