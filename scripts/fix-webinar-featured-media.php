<?php
/**
 * Inspect webinar featured images / body length, and restore missing
 * featured images (and truncated bodies) from live-event proof data
 * or the live www.stpatricks.ie source pages.
 *
 * Does not invent client assets — only restores from proof/live-events
 * or the live SPMHS media centre URLs already recorded for each post.
 *
 * HTTP (staging/local):
 *   /wp-content/themes/matrix-starter/scripts/fix-webinar-featured-media.php?key=fix-webinar-media
 *   &apply=1  to attach missing featured images / refresh truncated bodies
 *
 * CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/fix-webinar-featured-media.php
 *   APPLY=1 wp eval-file ...
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-webinar-media') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$apply = (isset($_GET['apply']) && (string) $_GET['apply'] === '1')
    || (getenv('APPLY') === '1');

$index_path = get_template_directory() . '/proof/live-events/index.json';
$image_dir = get_template_directory() . '/proof/live-events/images';
$html_dir = get_template_directory() . '/proof/live-events/html';

$index = is_file($index_path) ? json_decode((string) file_get_contents($index_path), true) : null;
$events = is_array($index['events'] ?? null) ? $index['events'] : [];
$by_slug = [];
foreach ($events as $event) {
    $slug = sanitize_title((string) ($event['slug'] ?? ''));
    if ($slug !== '') {
        $by_slug[$slug] = $event;
    }
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$posts = get_posts([
    'post_type' => 'webinars',
    'post_status' => ['publish', 'draft', 'future', 'private'],
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
]);

$stats = [
    'total' => count($posts),
    'with_thumb' => 0,
    'without_thumb' => 0,
    'empty_body' => 0,
    'short_body' => 0,
    'attached' => 0,
    'body_refreshed' => 0,
    'no_source_image' => 0,
];

$log = static function (string $line): void {
    echo $line . "\n";
};

$extract_body_from_html = static function (string $html): string {
    $body = '';
    if (preg_match('/<div class="rte-styles">(.*?)<div class="share-options">/s', $html, $match)) {
        $body = $match[1];
        $body = preg_replace('/<figure\b[^>]*>.*?<\/figure>/s', '', $body) ?? $body;
    }
    $body = preg_replace('/\sdata-udi="[^"]*"/', '', $body) ?? $body;
    $body = preg_replace_callback('/href="(\/[^"]*)"/', static function ($m) {
        return 'href="' . esc_url('https://www.stpatricks.ie' . $m[1]) . '"';
    }, $body) ?? $body;

    return wp_kses_post($body);
};

$download_to_temp = static function (string $url, string $filename) {
    $response = wp_remote_get($url, [
        'timeout' => 60,
        'redirection' => 5,
        'user-agent' => 'StPatricksStagingRestore/1.0',
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $bytes = (string) wp_remote_retrieve_body($response);

    if ($code < 200 || $code >= 300 || $bytes === '') {
        return new WP_Error('download_failed', 'HTTP ' . $code . ' for ' . $url);
    }

    $tmp = wp_tempnam($filename);
    if (! is_string($tmp) || $tmp === '') {
        return new WP_Error('temp_failed', 'Could not create temp file');
    }

    file_put_contents($tmp, $bytes);

    return $tmp;
};

$log('Webinar featured media inspect' . ($apply ? ' + APPLY' : ' (dry-run)'));
$log('---');

foreach ($posts as $post) {
    if (! $post instanceof WP_Post) {
        continue;
    }

    $post_id = (int) $post->ID;
    $thumb_id = (int) get_post_thumbnail_id($post_id);
    $body_len = strlen(trim(wp_strip_all_tags((string) $post->post_content)));
    $event = $by_slug[$post->post_name] ?? null;
    $proof_image = is_array($event) ? basename((string) ($event['image'] ?? '')) : '';
    $proof_path = is_array($event) ? (string) ($event['image'] ?? '') : '';
    $proof_file = $proof_image !== '' ? $image_dir . '/' . $proof_image : '';
    $proof_exists = $proof_file !== '' && is_file($proof_file);
    $live_url = (string) get_post_meta($post_id, '_spmhs_live_event_url', true);
    if ($live_url === '' && is_array($event)) {
        $live_url = (string) ($event['url'] ?? '');
    }

    if ($thumb_id > 0) {
        $stats['with_thumb']++;
    } else {
        $stats['without_thumb']++;
    }
    if ($body_len === 0) {
        $stats['empty_body']++;
    } elseif ($body_len < 120) {
        $stats['short_body']++;
    }

    $log(sprintf(
        '#%d %s [%s] thumb=%s body_chars=%d proof_img=%s live=%s',
        $post_id,
        $post->post_name,
        $post->post_status,
        $thumb_id > 0 ? (string) $thumb_id : 'NONE',
        $body_len,
        $proof_exists ? $proof_image : ($proof_image !== '' ? $proof_image . ' (remote-ok)' : 'n/a'),
        $live_url !== '' ? 'yes' : 'no'
    ));

    if (! $apply) {
        continue;
    }

    if ($thumb_id < 1) {
        $tmp = null;
        $filename = $proof_image !== '' ? $proof_image : ($post->post_name . '-featured.jpg');

        if ($proof_exists) {
            $tmp = wp_tempnam($filename);
            copy($proof_file, $tmp);
        } elseif ($proof_path !== '' && str_starts_with($proof_path, '/media/')) {
            $remote = 'https://www.stpatricks.ie' . $proof_path;
            $tmp = $download_to_temp($remote, $filename);
            if (is_wp_error($tmp)) {
                $log('  FAIL download image: ' . $tmp->get_error_message());
                $tmp = null;
            }
        } else {
            $stats['no_source_image']++;
            $log('  SKIP no image source');
        }

        if (is_string($tmp) && $tmp !== '') {
            $attachment_id = media_handle_sideload([
                'name' => sanitize_file_name($filename),
                'tmp_name' => $tmp,
            ], $post_id, $post->post_title);

            if (is_wp_error($attachment_id)) {
                $log('  FAIL attach: ' . $attachment_id->get_error_message());
            } else {
                set_post_thumbnail($post_id, (int) $attachment_id);
                $stats['attached']++;
                $log('  OK attached #' . (int) $attachment_id);
            }
        }
    }

    if ($body_len < 200) {
        $html = '';
        $html_path = $html_dir . '/' . $post->post_name . '.html';
        if (is_file($html_path)) {
            $html = (string) file_get_contents($html_path);
        } elseif ($live_url !== '') {
            $response = wp_remote_get($live_url, [
                'timeout' => 60,
                'redirection' => 5,
                'user-agent' => 'StPatricksStagingRestore/1.0',
            ]);
            if (! is_wp_error($response) && (int) wp_remote_retrieve_response_code($response) === 200) {
                $html = (string) wp_remote_retrieve_body($response);
            } else {
                $log('  FAIL fetch body HTML from live');
            }
        }

        if ($html !== '') {
            $body = $extract_body_from_html($html);
            $new_len = strlen(trim(wp_strip_all_tags($body)));
            if ($new_len > $body_len + 40) {
                wp_update_post([
                    'ID' => $post_id,
                    'post_content' => $body,
                ]);
                $stats['body_refreshed']++;
                $log(sprintf('  OK body refreshed %d -> %d chars', $body_len, $new_len));
            }
        }
    }
}

$log('---');
$log(wp_json_encode($stats, JSON_PRETTY_PRINT));
