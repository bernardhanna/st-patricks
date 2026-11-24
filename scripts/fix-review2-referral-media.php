<?php
/**
 * Referral pages: remove placeholder testimonials; set Dean/outpatient YouTube.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-referral-media.php?key=fix-ref1
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-ref1') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$log = [];

$pages = [
    'healthcare-professionals/refer-an-adult-for-inpatient-care' => [
        'videos' => [
            'https://www.youtube.com/watch?v=mQmYjdN9f6E',
            'https://www.youtube.com/watch?v=_Qz7oaebdO8',
        ],
    ],
    'healthcare-professionals/refer-to-a-day-programme' => [
        'videos' => [],
        'remove_testimonials' => true,
    ],
    'healthcare-professionals/refer-for-outpatient-care' => [
        'videos' => [
            'https://www.youtube.com/watch?v=mN0Qyhix71E',
        ],
        'remove_testimonials' => true,
    ],
];

foreach ($pages as $path => $cfg) {
    $page = get_page_by_path($path);
    if (! $page) {
        $log[] = "MISSING {$path}";
        continue;
    }
    $id = (int) $page->ID;
    $log[] = "page {$id} {$path}";

    if (! function_exists('get_field') || ! function_exists('update_field')) {
        $log[] = 'no ACF';
        continue;
    }

    $rows = get_field('flexible_content_blocks', $id);
    if (! is_array($rows)) {
        $log[] = 'no flexible blocks';
        continue;
    }

    $changed = false;
    $videos_set = 0;
    $testimonials_removed = 0;
    $new_rows = [];

    foreach ($rows as $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '');

        if (! empty($cfg['remove_testimonials']) && in_array($layout, ['testimonials', 'testimonial_slider', 'testimonial_block', 'featured_testimonials'], true)) {
            $testimonials_removed++;
            $changed = true;
            continue;
        }

        // Also clear selected testimonials arrays inside other layouts
        foreach (['testimonials', 'selected_testimonials', 'testimonial_items'] as $k) {
            if (! empty($row[$k]) && ! empty($cfg['remove_testimonials'])) {
                $row[$k] = [];
                $testimonials_removed++;
                $changed = true;
            }
        }

        // Video blocks — set URLs if empty/broken
        if (! empty($cfg['videos']) && in_array($layout, ['video', 'videos', 'video_embed', 'embedded_video', 'media_video'], true)) {
            $urls = $cfg['videos'];
            // Single video field shapes
            if (isset($row['video_url']) && is_string($row['video_url'])) {
                if ($row['video_url'] === '' || ! preg_match('/mQmYjdN9f6E|_Qz7oaebdO8|mN0Qyhix71E/', $row['video_url'])) {
                    $row['video_url'] = $urls[0];
                    $videos_set++;
                    $changed = true;
                }
            }
            if (isset($row['youtube_url']) && is_string($row['youtube_url'])) {
                if ($row['youtube_url'] === '' || ! preg_match('/mQmYjdN9f6E|_Qz7oaebdO8|mN0Qyhix71E/', $row['youtube_url'])) {
                    $row['youtube_url'] = $urls[0];
                    $videos_set++;
                    $changed = true;
                }
            }
        }

        // video_with_content / multi video rows
        if (! empty($cfg['videos']) && isset($row['videos']) && is_array($row['videos'])) {
            foreach ($row['videos'] as $vi => $vid) {
                if (! is_array($vid)) {
                    continue;
                }
                $target = $cfg['videos'][$vi] ?? $cfg['videos'][0];
                foreach (['video_url', 'youtube_url', 'url'] as $vk) {
                    if (isset($vid[$vk]) && is_string($vid[$vk])) {
                        if ($vid[$vk] === '' || ! preg_match('/mQmYjdN9f6E|_Qz7oaebdO8|mN0Qyhix71E/', $vid[$vk])) {
                            $row['videos'][$vi][$vk] = $target;
                            $videos_set++;
                            $changed = true;
                        }
                    }
                }
            }
        }

        $new_rows[] = $row;
        $log[] = "  keep layout={$layout}";
    }

    if ($changed) {
        update_field('flexible_content_blocks', $new_rows, $id);
        clean_post_cache($id);
    }
    $log[] = "  videos_set={$videos_set} testimonials_removed={$testimonials_removed} changed=" . ($changed ? 'yes' : 'no');
}

echo implode("\n", $log) . "\nDONE\n";
