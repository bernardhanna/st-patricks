<?php
/**
 * QC batch 10 Nov: publish safe drafts + restore empty Watch YouTube embeds.
 *
 * WP-CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/qc-seed-batch-nov10.php
 *
 * Browser (local/staging only):
 *   /wp-content/themes/matrix-starter/scripts/qc-seed-batch-nov10.php?key=qc-batch-nov10
 *
 * Pair with theme redirect-map changes that unshadow these published slugs
 * (esp. eHR press release vs new-appointment-notifications).
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed_host = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed_host || ($_GET['key'] ?? '') !== 'qc-batch-nov10') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

@set_time_limit(0);

$log = [];

/**
 * Insert a YouTube iframe after a Watch heading (bypasses wp_kses_post).
 *
 * @param list<string> $needles Exact heading HTML snippets to match.
 */
$insert_youtube = function (int $post_id, string $youtube_id, array $needles, string $fallback_regex = '') use (&$log): void {
    $post = get_post($post_id);
    if (! $post) {
        $log[] = "MISS video post #{$post_id}";
        return;
    }

    $content = (string) $post->post_content;
    if (str_contains($content, $youtube_id)) {
        $log[] = "OK video already on #{$post_id} {$post->post_name}";
        return;
    }

    $iframe = '<p><iframe width="560" height="315" src="https://www.youtube.com/embed/'
        . $youtube_id
        . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></p>';

    $updated = false;
    foreach ($needles as $needle) {
        if (! str_contains($content, $needle)) {
            continue;
        }
        $content = str_replace($needle, $needle . "\n\n" . $iframe, $content);
        $updated = true;
        break;
    }

    if (! $updated && $fallback_regex !== '' && preg_match($fallback_regex, $content)) {
        // Append once near end if heading text exists without exact markup.
        $content .= "\n\n" . $iframe;
        $updated = true;
    }

    if (! $updated) {
        $log[] = "FAIL video insert point #{$post_id} {$post->post_name}";
        return;
    }

    global $wpdb;
    $saved = $wpdb->update(
        $wpdb->posts,
        [
            'post_content' => $content,
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', true),
        ],
        ['ID' => $post_id],
        ['%s', '%s', '%s'],
        ['%d']
    );
    clean_post_cache($post_id);

    if ($saved === false || ! str_contains((string) get_post_field('post_content', $post_id), $youtube_id)) {
        $log[] = "FAIL video verify #{$post_id} {$youtube_id}";
        return;
    }

    $log[] = "DONE video {$youtube_id} on #{$post_id} {$post->post_name}";
};

/**
 * Convert a bare youtube.com/watch link into an iframe embed via $wpdb.
 */
$embed_from_watch_link = function (int $post_id, string $youtube_id) use (&$log): void {
    $post = get_post($post_id);
    if (! $post) {
        $log[] = "MISS embed-link post #{$post_id}";
        return;
    }

    $content = (string) $post->post_content;
    if (str_contains($content, 'youtube.com/embed/' . $youtube_id)) {
        $log[] = "OK embed already on #{$post_id} {$post->post_name}";
        return;
    }

    if (! preg_match('/youtube\.com\/watch\?v=' . preg_quote($youtube_id, '/') . '[^"\'<\s]*/i', $content)) {
        $log[] = "SKIP no watch link #{$post_id} {$youtube_id}";
        return;
    }

    $iframe = '<p><iframe width="560" height="315" src="https://www.youtube.com/embed/'
        . $youtube_id
        . '" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></p>';

    // Prefer inserting after "watch the video below" cue when present.
    if (preg_match('/watch the video below/i', $content) && ! str_contains($content, 'youtube.com/embed/')) {
        $content = preg_replace(
            '/(watch the video below[^.]*\.)/i',
            '$1' . "\n\n" . $iframe,
            $content,
            1
        );
    } else {
        $content .= "\n\n" . $iframe;
    }

    global $wpdb;
    $wpdb->update(
        $wpdb->posts,
        [
            'post_content' => $content,
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', true),
        ],
        ['ID' => $post_id],
        ['%s', '%s', '%s'],
        ['%d']
    );
    clean_post_cache($post_id);

    if (str_contains((string) get_post_field('post_content', $post_id), 'youtube.com/embed/' . $youtube_id)) {
        $log[] = "DONE embed-from-link {$youtube_id} on #{$post_id}";
    } else {
        $log[] = "FAIL embed-from-link #{$post_id}";
    }
};

// ---------------------------------------------------------------------------
// 1) Publish safe contentful drafts (snag-linked + previously inventoried).
// ---------------------------------------------------------------------------
$publish_ids = [
    1119, 1129, 1141, 1143, 1145, 1150, 1151, 1155, 1166, 1178, 1195, 1199, 1208,
    1216, 1222, 1231, 1235, 1244, 1246, 1248, 1252, 1258, 1265, 1288, 1295, 1302,
    2053, 2055, 2057, 2059, 2066, 2086, 2098, 2103, 2105, 2119, 2138, 2140, 2144,
    2146, 2154, 2156, 2159, 2163, 2172, 2173, 2175, 2181, 2185, 2197, 2208, 2213,
    2267, 2286, 2299, 2306, 2312, 2325, 2532, 2536, 2537, 2539, 2544, 2546, 2550,
    2551, 2552, 2554, 2558, 2577, 2581, 2591, 2595, 2607, 2611, 2615, 2618, 2622,
    2624, 2631, 2635, 2637, 2639, 2640, 2643, 2645, 2651, 2653, 2658, 2701, 2707,
    2711, 2720, 2756, 3174, 3178,
];

$published = 0;
$already = 0;
$skipped = 0;
foreach ($publish_ids as $id) {
    $post = get_post((int) $id);
    if (! $post) {
        $log[] = "MISS publish #{$id}";
        $skipped++;
        continue;
    }

    $len = strlen((string) $post->post_content);
    if ($len < 800) {
        $log[] = "SKIP thin #{$id} {$post->post_name} len={$len}";
        $skipped++;
        continue;
    }

    if ($post->post_status === 'publish') {
        $already++;
        continue;
    }

    $result = wp_update_post([
        'ID' => (int) $id,
        'post_status' => 'publish',
    ], true);

    if (is_wp_error($result)) {
        $log[] = "FAIL publish #{$id}: " . $result->get_error_message();
        $skipped++;
        continue;
    }

    $published++;
    $log[] = "PUBLISHED #{$id} {$post->post_name}";
}

$log[] = "PUBLISH summary published={$published} already={$already} skipped={$skipped}";

// ---------------------------------------------------------------------------
// 2) Restore empty Watch headings from live YouTube IDs.
// ---------------------------------------------------------------------------
$insert_youtube(
    1221,
    'BEEt6th8RbA',
    [
        '<h3 class="hide-for-main">Watch a talk on mental health medication</h3>',
        '<h2>Watch a talk on mental health medication</h2>',
    ],
    '/Watch a talk on mental health medication/i'
);

$insert_youtube(
    1224,
    'QQDB6ZyvjBk',
    [
        '<h3 class="hide-for-main">watch the video below</h3>',
        '<h2>watch the video below</h2>',
    ],
    '/watch the video below/i'
);

$insert_youtube(
    1312,
    'z7XVPinSET4',
    [
        '<p>Keep scrolling to read more or watch a talk from Debbie in the video below.</p>',
    ],
    '/watch a talk from Debbie/i'
);

$insert_youtube(
    3446,
    'na_pWyKKh0Q',
    [
        '<h2>Watch a talk on family therapy</h2>',
        '<h3 class="hide-for-main">Watch a talk on family therapy</h3>',
    ],
    '/Watch a talk on family therapy/i'
);

// Artmindfulness already has a watch?v= link — promote to embed.
$embed_from_watch_link(1187, 'wYAUm3XL7dM');

// ---------------------------------------------------------------------------
// 3) Light content link fixes on burnout article (snag called these out).
//    Targets are now published above; rewrite absolute local hosts if present.
// ---------------------------------------------------------------------------
$burnout = get_post(1312);
if ($burnout) {
    $pairs = [
        // Ensure relative/self-host agnostic paths for the three related articles.
        home_url('/stress-anxiety-disorder-genes-recovery/') => home_url('/stress-anxiety-disorder-genes-recovery/'),
        home_url('/work-related-stress-how-to-manage-it/') => home_url('/work-related-stress-how-to-manage-it/'),
        home_url('/symptoms-of-panic-attacks-and-what-to-do/') => home_url('/symptoms-of-panic-attacks-and-what-to-do/'),
    ];
    // No-op remap kept for clarity; real fix is publish + redirect unshadow.
    $log[] = 'NOTE burnout related articles rely on publish+redirect unshadow';
}

foreach ($log as $line) {
    echo $line . "\n";
}

echo "DONE qc-seed-batch-nov10\n";
