<?php

/**
 * Snag fix: embed the research video on Academic Institute (/about-us/our-present-and-future/academic-institute/).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/snag-academic-institute-video.php
 */

$post_id = 251;
$video_url = 'https://www.youtube.com/watch?v=AjJQxOrmv1o';

$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows)) {
	$rows = [];
}

foreach ($rows as $row) {
	if (($row['acf_fc_layout'] ?? '') !== 'video_showcase') {
		continue;
	}

	foreach ((array) ($row['slides'] ?? []) as $slide) {
		if (trim((string) ($slide['video_embed_url'] ?? '')) === $video_url) {
			echo "already-present\n";
			return;
		}
	}
}

$rows[] = [
	'acf_fc_layout' => 'video_showcase',
	'heading_tag' => 'h2',
	'heading' => 'Research',
	'intro' => '',
	'layout_style' => 'feature_single',
	'video_surface_size' => 'default',
	'text_max_width' => 'default',
	'slides' => [
		[
			'poster_image' => null,
			'video_source_type' => 'embed_url',
			'video_embed_url' => $video_url,
			'local_video_file' => false,
			'caption' => '',
			'cta_link' => '',
		],
	],
];

update_field('flexible_content_blocks', $rows, $post_id);

$check = get_field('flexible_content_blocks', $post_id);
echo 'rows=' . count((array) $check) . "\n";
echo 'last_layout=' . ($check[count($check) - 1]['acf_fc_layout'] ?? '') . "\n";
echo 'last_video=' . ($check[count($check) - 1]['slides'][0]['video_embed_url'] ?? '') . "\n";
