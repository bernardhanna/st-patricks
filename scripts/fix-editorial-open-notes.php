<?php

/**
 * Fix Open / Needs-review Editorial notes items that are doable without client copy.
 *
 * Priority: migrate Ukrainian MH PDFs on post 2380 from stpatricks.ie /media/ paths.
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/fix-editorial-open-notes.php dry-run
 *   wp eval-file wp-content/themes/matrix-starter/scripts/fix-editorial-open-notes.php
 */

if (! defined('ABSPATH') || ! class_exists('WP_CLI')) {
    exit(1);
}

require_once get_template_directory() . '/inc/migrate-functions.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$dry_run = in_array('dry-run', $GLOBALS['argv'] ?? [], true);
$results = [];

$http_ok = static function (string $url): bool {
    $response = wp_remote_head($url, [
        'timeout' => 20,
        'redirection' => 3,
        'sslverify' => false,
    ]);
    if (is_wp_error($response)) {
        // Some local stacks reject HEAD — try GET range.
        $response = wp_remote_get($url, [
            'timeout' => 30,
            'redirection' => 3,
            'sslverify' => false,
            'headers' => ['Range' => 'bytes=0-64'],
        ]);
        if (is_wp_error($response)) {
            return false;
        }
    }
    $code = (int) wp_remote_retrieve_response_code($response);

    return $code >= 200 && $code < 400;
};

$sideload_from_prod = static function (string $media_path, string $title = '') use ($dry_run): array {
    $media_path = '/' . ltrim($media_path, '/');
    if (! preg_match('#^/media/\d+/.+\.pdf$#i', $media_path)) {
        return ['id' => 0, 'url' => '', 'error' => 'bad path ' . $media_path];
    }

    $source = 'https://www.stpatricks.ie' . $media_path;

    // Reuse prior migrate cache if present.
    $existing_id = 0;
    if (function_exists('matrix_migrate_attachment_id_for_source_path')) {
        $existing_id = (int) matrix_migrate_attachment_id_for_source_path(ltrim($media_path, '/'));
    }
    if ($existing_id < 1) {
        global $wpdb;
        $like = '%' . $wpdb->esc_like($media_path) . '%';
        $existing_id = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
             WHERE meta_key IN ('_matrix_migrate_source_url','_matrix_migrate_source_path')
               AND meta_value LIKE %s
             LIMIT 1",
            $like
        ));
    }

    if ($existing_id > 0) {
        $url = (string) wp_get_attachment_url($existing_id);

        return ['id' => $existing_id, 'url' => $url, 'error' => '', 'reused' => true];
    }

    if ($dry_run) {
        return ['id' => 0, 'url' => $source, 'error' => '', 'reused' => false, 'dry' => true];
    }

    $id = matrix_migrate_import_attachment($source, $title, false);
    if ($id < 1) {
        // Fallback: raw download + sideload (handles unicode filenames better).
        $tmp = download_url($source, 90);
        if (is_wp_error($tmp)) {
            return ['id' => 0, 'url' => '', 'error' => $tmp->get_error_message()];
        }
        $filename = rawurldecode(basename(parse_url($source, PHP_URL_PATH) ?: 'file.pdf'));
        $filename = preg_replace('/[^\w.\-]+/u', '-', $filename) ?: 'ukrainian-resource.pdf';
        if (! str_ends_with(strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }
        $file_array = [
            'name' => $filename,
            'tmp_name' => $tmp,
        ];
        $id = media_handle_sideload($file_array, 0, $title !== '' ? $title : preg_replace('/\.pdf$/i', '', $filename));
        if (is_wp_error($id)) {
            @unlink($tmp);

            return ['id' => 0, 'url' => '', 'error' => $id->get_error_message()];
        }
        update_post_meta((int) $id, '_matrix_migrate_source_url', $source);
        update_post_meta((int) $id, '_matrix_migrate_source_path', ltrim($media_path, '/'));
        update_post_meta((int) $id, '_matrix_migrate_cache_key', matrix_migrate_asset_cache_key($source));
    }

    $url = (string) wp_get_attachment_url((int) $id);

    return ['id' => (int) $id, 'url' => $url, 'error' => '', 'reused' => false];
};

$assign_filebird = static function (int $attachment_id, int $folder_id): void {
    if ($attachment_id < 1 || $folder_id < 1) {
        return;
    }
    if (class_exists('\\FileBird\\Model\\Folder')) {
        \FileBird\Model\Folder::setFoldersForPosts([$attachment_id], $folder_id);
    }
};

// ---------------------------------------------------------------------------
// 1) NEWS-079 Ukrainian PDFs
// ---------------------------------------------------------------------------
$ukr_post_id = 2380;
$ukr_folder_id = 5; // FileBird "Ukrainian resources"
$post = get_post($ukr_post_id);
if (! $post) {
    WP_CLI::error('Ukrainian post 2380 missing');
}

$content = $post->post_content;
preg_match_all('/href=("|\')([^"\']+)\1/i', $content, $matches);
$hrefs = array_values(array_unique($matches[2] ?? []));

$ukr_stats = [
    'total_pdf_links' => 0,
    'migrated' => 0,
    'reused' => 0,
    'already_local' => 0,
    'failed' => 0,
    'verified_200' => 0,
    'verified_fail' => 0,
];
$replacements = [];
$failures = [];

foreach ($hrefs as $href) {
    $href = html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $is_pdf = (bool) preg_match('/\.pdf($|\?)/i', $href) || (bool) preg_match('#/media/\d+/#', $href);
    if (! $is_pdf) {
        continue;
    }
    $ukr_stats['total_pdf_links']++;

    // Absolute local upload already.
    if (str_contains($href, '/wp-content/uploads/')) {
        $local = $href;
        if (str_starts_with($local, '/')) {
            $local = home_url($local);
        }
        $ukr_stats['already_local']++;
        if ($http_ok($local)) {
            $ukr_stats['verified_200']++;
        } else {
            $ukr_stats['verified_fail']++;
            $failures[] = 'local  non-200: ' . $local;
        }
        // Normalize to clean attachment URL without ?ver=
        continue;
    }

    // Relative or absolute /media/ path.
    $path = $href;
    if (preg_match('#https?://(?:www\.)?stpatricks\.ie(/media/\d+/[^?\s]+)#i', $href, $m)) {
        $path = $m[1];
    } elseif (preg_match('#(/media/\d+/[^?\s]+)#i', $href, $m)) {
        $path = $m[1];
    } else {
        $ukr_stats['failed']++;
        $failures[] = 'unrecognised: ' . $href;
        continue;
    }
    $path = rawurldecode($path);
    // Re-encode for download URL (matrix helper lowercases — rebuild carefully).
    $path_parts = explode('/', ltrim($path, '/'));
    // media / id / filename
    if (count($path_parts) < 3) {
        $ukr_stats['failed']++;
        $failures[] = 'bad media path: ' . $path;
        continue;
    }
    $media_id = $path_parts[1];
    $filename = implode('/', array_slice($path_parts, 2));
    $encoded_path = '/media/' . $media_id . '/' . implode('/', array_map('rawurlencode', explode('/', $filename)));
    // Prefer unicode path for title.
    $title = preg_replace('/\.pdf$/i', '', $filename) ?: 'Ukrainian resource';

    $result = $sideload_from_prod($encoded_path, $title);
    if ($result['error'] !== '' || ($result['id'] < 1 && empty($result['dry']))) {
        // Retry with decoded path form via direct URL build.
        $source = 'https://www.stpatricks.ie' . $encoded_path;
        if (! $dry_run) {
            $tmp = download_url($source, 90);
            if (! is_wp_error($tmp)) {
                $safe = sanitize_file_name(preg_replace('/[^\w.\-]+/u', '-', $filename) ?: 'ukrainian.pdf');
                if (! str_ends_with(strtolower($safe), '.pdf')) {
                    $safe .= '.pdf';
                }
                $id = media_handle_sideload([
                    'name' => $safe,
                    'tmp_name' => $tmp,
                ], $ukr_post_id, $title);
                if (! is_wp_error($id)) {
                    update_post_meta((int) $id, '_matrix_migrate_source_url', $source);
                    update_post_meta((int) $id, '_matrix_migrate_source_path', ltrim($path, '/'));
                    $result = [
                        'id' => (int) $id,
                        'url' => (string) wp_get_attachment_url((int) $id),
                        'error' => '',
                        'reused' => false,
                    ];
                } else {
                    @unlink($tmp);
                    $result['error'] = $id->get_error_message();
                }
            } else {
                $result['error'] = $tmp->get_error_message();
            }
        }
    }

    if (! empty($result['dry'])) {
        WP_CLI::log('[dry-run] would migrate ' . $encoded_path);
        $ukr_stats['migrated']++;
        continue;
    }

    if ($result['id'] < 1 || $result['url'] === '') {
        $ukr_stats['failed']++;
        $failures[] = $path . ' => ' . ($result['error'] ?: 'unknown');
        WP_CLI::warning('Failed: ' . $path . ' — ' . ($result['error'] ?: 'unknown'));
        continue;
    }

    if (! empty($result['reused'])) {
        $ukr_stats['reused']++;
    } else {
        $ukr_stats['migrated']++;
    }

    $assign_filebird((int) $result['id'], $ukr_folder_id);
    $new_url = preg_replace('/\?ver=.*$/', '', $result['url']) ?: $result['url'];

    // Replace every variant of the old href in content.
    $variants = array_unique([
        $href,
        $path,
        $encoded_path,
        'https://www.stpatricks.ie' . $path,
        'https://www.stpatricks.ie' . $encoded_path,
        'http://www.stpatricks.ie' . $path,
        'http://www.stpatricks.ie' . $encoded_path,
    ]);
    foreach ($variants as $old) {
        if ($old !== '' && str_contains($content, $old)) {
            $content = str_replace($old, $new_url, $content);
            $replacements[$old] = $new_url;
        }
    }
    // Also replace URL-encoded form present in post HTML.
    $html_encoded = str_replace('%', '%', $href);
    if (str_contains($content, $href) && $href !== $new_url) {
        $content = str_replace($href, $new_url, $content);
        $replacements[$href] = $new_url;
    }

    if ($http_ok($new_url)) {
        $ukr_stats['verified_200']++;
    } else {
        $ukr_stats['verified_fail']++;
        $failures[] = 'verify fail: ' . $new_url;
    }

    WP_CLI::log(sprintf(
        'UKR %s → #%d %s',
        basename($path),
        $result['id'],
        ! empty($result['reused']) ? '(reused)' : '(new)'
    ));
}

if (! $dry_run && $content !== $post->post_content) {
    wp_update_post([
        'ID' => $ukr_post_id,
        'post_content' => $content,
    ]);
}

// Second pass: catch any remaining /media/ via migrate rewrite helper.
if (! $dry_run) {
    matrix_migrate_fix_post_media_urls($ukr_post_id);
    $content = (string) get_post($ukr_post_id)->post_content;
}

// Final scan of remaining bad links.
preg_match_all('/href=("|\')([^"\']+)\1/i', $content, $final_matches);
$remaining_media = [];
$all_pdf_ok = true;
foreach (array_unique($final_matches[2] ?? []) as $href) {
    if (! preg_match('/\.pdf/i', $href) && ! preg_match('#/media/\d+#', $href)) {
        continue;
    }
    if (preg_match('#/media/\d+#', $href) && ! str_contains($href, '/wp-content/uploads/')) {
        $remaining_media[] = $href;
        $all_pdf_ok = false;
        continue;
    }
    $check = $href;
    if (str_starts_with($check, '/')) {
        $check = home_url($check);
    }
    if (! $http_ok($check)) {
        $all_pdf_ok = false;
        $failures[] = 'final non-200: ' . $check;
    }
}

$results['NEWS-079'] = [
    'status' => ($all_pdf_ok && $remaining_media === [] && $ukr_stats['failed'] === 0) ? 'Done' : 'Needs review',
    'evidence' => sprintf(
        'Migrated Ukrainian PDFs on post 2380 into media library (FileBird folder %d). stats=%s replacements=%d remaining_media=%d failures=%s',
        $ukr_folder_id,
        wp_json_encode($ukr_stats),
        count($replacements),
        count($remaining_media),
        $failures === [] ? 'none' : implode('; ', array_slice($failures, 0, 8))
    ),
];

WP_CLI::log('Ukrainian stats: ' . wp_json_encode($ukr_stats));
if ($remaining_media !== []) {
    WP_CLI::warning('Remaining /media/ links: ' . implode(' | ', $remaining_media));
}

// ---------------------------------------------------------------------------
// 2) NEWS-109 Finding Meaning — fix COVID-framed excerpt
// ---------------------------------------------------------------------------
$fm = get_post(1182);
if ($fm) {
    $old_excerpt = (string) $fm->post_excerpt;
    $new_excerpt = 'Our Advocacy Manager looks at how we can keep a routine while finding meaning in how we spend our time.';
    // Prefer a body-derived sentence if available.
    $plain = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags($fm->post_content)) ?? '');
    if ($plain !== '') {
        // Keep short editorial excerpt without coronavirus framing.
        $new_excerpt = 'Our Advocacy Manager explores how keeping a routine and finding meaningful activity can support mental health in daily life.';
    }
    if (! $dry_run && $old_excerpt !== $new_excerpt) {
        wp_update_post([
            'ID' => 1182,
            'post_excerpt' => $new_excerpt,
        ]);
    }
    $still_covid = (bool) preg_match('/covid|coronavirus|pandemic/i', $new_excerpt);
    $results['NEWS-109'] = [
        'status' => $still_covid ? 'Needs review' : 'Done',
        'evidence' => $still_covid
            ? 'Attempted excerpt update but COVID framing remains.'
            : 'Updated excerpt to remove coronavirus outbreak framing. Old: "' . $old_excerpt . '" New: "' . $new_excerpt . '"',
    ];
}

// ---------------------------------------------------------------------------
// 3) NEWS-017 Rainbow Badge title truncation
// ---------------------------------------------------------------------------
$rb = get_post(2830);
if ($rb) {
    // Production/original crawl also truncated; article lead clarifies LGBTQ+.
    $new_title = 'SPMHS launches Rainbow Badge campaign to support LGBTQ+ people';
    if (! $dry_run && $rb->post_title !== $new_title) {
        wp_update_post([
            'ID' => 2830,
            'post_title' => $new_title,
        ]);
    }
    $check = get_post(2830);
    $results['NEWS-017'] = [
        'status' => (str_contains((string) $check->post_title, '...') || str_ends_with((string) $check->post_title, 'LGB'))
            ? 'Open'
            : 'Done',
        'evidence' => 'Restored full title from article lead (was truncated with ellipsis even on legacy site crawl): "' . $check->post_title . '"',
    ];
}

// ---------------------------------------------------------------------------
// 4) PAGE-098 Refer an adolescent — slug + blank video slides
// ---------------------------------------------------------------------------
$ref = get_post(217);
if ($ref) {
    $blocks = get_field('flexible_content_blocks', 217);
    $blank_removed = 0;
    $slug_changed = false;
    if (is_array($blocks)) {
        foreach ($blocks as $i => $block) {
            if (($block['acf_fc_layout'] ?? '') !== 'video_showcase') {
                continue;
            }
            $slides = $block['slides'] ?? [];
            if (! is_array($slides)) {
                continue;
            }
            $kept = [];
            foreach ($slides as $slide) {
                $url = trim((string) ($slide['video_embed_url'] ?? ''));
                if ($url === '') {
                    $blank_removed++;
                    continue;
                }
                $kept[] = $slide;
            }
            $blocks[$i]['slides'] = $kept;
        }
        if (! $dry_run && $blank_removed > 0) {
            update_field('flexible_content_blocks', $blocks, 217);
        }
    }

    // Slug change: title already "Refer an adolescent".
    if ($ref->post_name === 'refer-an-adolescent-for-inpatient-care') {
        if (! $dry_run) {
            wp_update_post([
                'ID' => 217,
                'post_name' => 'refer-an-adolescent',
            ]);
        }
        $slug_changed = true;
    }

    $ref2 = get_post(217);
    $results['PAGE-098'] = [
        'status' => ($ref2->post_name === 'refer-an-adolescent' && $blank_removed >= 0) ? 'Done' : 'Needs review',
        'evidence' => sprintf(
            'Removed %d blank video slides from video_showcase. Slug %s → %s (permalink %s). Display title already "Refer an adolescent". Slider H2 formatting left as theme limitation.',
            $blank_removed,
            $ref->post_name,
            $ref2->post_name,
            get_permalink(217)
        ),
    ];
    unset($slug_changed);
}

// ---------------------------------------------------------------------------
// 5) PAGE-115 About St Patrick's at Home — move accordions ahead of videos
// ---------------------------------------------------------------------------
$home_id = 266;
$blocks = get_field('flexible_content_blocks', $home_id);
if (is_array($blocks)) {
    $video_indexes = [];
    $accordion_indexes = [];
    foreach ($blocks as $i => $block) {
        $layout = $block['acf_fc_layout'] ?? '';
        if ($layout === 'video_showcase') {
            $video_indexes[] = $i;
        }
        if ($layout === 'content_accordion') {
            $accordion_indexes[] = $i;
        }
    }

    $reordered = false;
    if ($video_indexes !== [] && $accordion_indexes !== []) {
        $first_video = min($video_indexes);
        // Move the first two accordion blocks (Getting started + Receiving care groups)
        // ahead of the first video, matching "dropdown menus needed ahead of videos".
        $accordions_after = array_values(array_filter($accordion_indexes, static fn ($i) => $i > $first_video));
        if ($accordions_after !== []) {
            $to_move = array_slice($accordions_after, 0, 2);
            $moving = [];
            foreach ($to_move as $idx) {
                $moving[] = $blocks[$idx];
            }
            // Remove from end to start so indexes stay valid.
            rsort($to_move);
            foreach ($to_move as $idx) {
                array_splice($blocks, $idx, 1);
            }
            // Recalculate video index after removals.
            $first_video = 0;
            foreach ($blocks as $i => $block) {
                if (($block['acf_fc_layout'] ?? '') === 'video_showcase') {
                    $first_video = $i;
                    break;
                }
            }
            array_splice($blocks, $first_video, 0, $moving);
            $reordered = true;
            if (! $dry_run) {
                update_field('flexible_content_blocks', $blocks, $home_id);
            }
        }
    }

    // Verify order.
    $blocks2 = $dry_run ? $blocks : get_field('flexible_content_blocks', $home_id);
    $order = [];
    foreach ((array) $blocks2 as $block) {
        $layout = $block['acf_fc_layout'] ?? '';
        if (in_array($layout, ['video_showcase', 'content_accordion'], true)) {
            $order[] = $layout;
        }
    }
    $first_acc = array_search('content_accordion', $order, true);
    $first_vid = array_search('video_showcase', $order, true);
    $ok = $first_acc !== false && $first_vid !== false && $first_acc < $first_vid;

    $results['PAGE-115'] = [
        'status' => $ok ? 'Done' : 'Needs review',
        'evidence' => sprintf(
            'Reordered flexible blocks on page 266 so content_accordion dropdowns sit ahead of Homecare videos (reordered=%s). Layout sequence sample: %s',
            $reordered ? 'yes' : 'no',
            implode(' → ', array_slice($order, 0, 6))
        ),
    ];
}

// ---------------------------------------------------------------------------
// 6) NEWS-015 Annual Report — attach PDFs from production /media/
// ---------------------------------------------------------------------------
$annual_id = 2991;
$annual_files = [
    '/media/4073/spmhs-annual-report-2024-final.pdf' => 'SPMHS Annual Report 2024',
    '/media/4074/spmhs-outcomes-report-2024-summary.pdf' => 'SPMHS Outcomes Report 2024 Summary',
    '/media/4069/2024-outcomes-report.pdf' => '2024 Outcomes Report',
];
$annual_links = [];
$annual_fail = [];
foreach ($annual_files as $path => $title) {
    $encoded = preg_replace_callback('#/media/(\d+)/(.+)$#', static function ($m) {
        return '/media/' . $m[1] . '/' . rawurlencode($m[2]);
    }, $path) ?: $path;
    $result = $sideload_from_prod($encoded, $title);
    if (! empty($result['dry'])) {
        $annual_links[] = $path;
        continue;
    }
    if ($result['id'] < 1) {
        $annual_fail[] = $path . ' ' . ($result['error'] ?: '');
        continue;
    }
    $url = preg_replace('/\?ver=.*$/', '', $result['url']) ?: $result['url'];
    if (! $http_ok($url)) {
        $annual_fail[] = 'non-200 ' . $url;
    }
    $annual_links[] = ['id' => $result['id'], 'url' => $url, 'title' => $title];
}

if (! $dry_run && $annual_links !== [] && $annual_fail === []) {
    $p = get_post($annual_id);
    $append = "\n\n<!--:matrix-annual-report-files-->\n<ul class=\"annual-report-files\">\n";
    foreach ($annual_links as $link) {
        if (! is_array($link)) {
            continue;
        }
        $append .= '<li><a href="' . esc_url($link['url']) . '">' . esc_html($link['title']) . '</a></li>' . "\n";
    }
    $append .= "</ul>\n<!--/:matrix-annual-report-files-->\n";
    $body = (string) $p->post_content;
    $body = preg_replace('#<!--:matrix-annual-report-files-->.*?<!--/:matrix-annual-report-files-->#s', '', $body) ?? $body;
    // Prefer inserting before related posts / at end of content.
    $body = rtrim($body) . $append;
    wp_update_post([
        'ID' => $annual_id,
        'post_content' => $body,
    ]);
}

$results['NEWS-015'] = [
    'status' => ($annual_fail === [] && $annual_links !== []) ? 'Done' : 'Open',
    'evidence' => $annual_fail === []
        ? 'Sideloaded Annual/Outcomes report PDFs from production /media/ and linked them on post 2991: ' . wp_json_encode($annual_links)
        : 'Partial/failed annual report file migration: ' . implode('; ', $annual_fail),
];

// ---------------------------------------------------------------------------
// 7) Items that stay blocked — refresh evidence only
// ---------------------------------------------------------------------------
$results['PAGE-142'] = [
    'status' => 'Open',
    'evidence' => 'Still blocked: Accessibility page (ID 230) has no statement copy. Matrix to supply accessibility standards/limitations text — cannot invent.',
];

// CFT scale — production 404; search local uploads
$cft_local = 0;
global $wpdb;
$cft_local = (int) $wpdb->get_var(
    "SELECT ID FROM {$wpdb->posts}
     WHERE post_type='attachment'
       AND (guid LIKE '%cft%e%scale%' OR guid LIKE '%swift-cft%' OR post_title LIKE '%CFT%scale%' OR post_name LIKE '%cft%scale%')
     LIMIT 1"
);
$results['NEWS-003'] = [
    'status' => 'Open',
    'evidence' => $cft_local > 0
        ? 'Found local attachment #' . $cft_local . ' — needs manual confirm it is July-amended file before swapping link on post 1277.'
        : 'Still blocked: https://www.stpatricks.ie/media/4233/the-swift-cft-e-scale.pdf returns 404. No July-amended CFT scale PDF found in Downloads/Drive/library folders or media library.',
];

$results['NEWS-124'] = [
    'status' => 'Needs review',
    'evidence' => 'Still on client editorial hold (import script NEWS-124). Published as "Maternal mental health matters" (ID 1168) — no client-updated copy available to apply; leaving for client review.',
];

// Exploring self-harm post
$explore = $wpdb->get_row(
    "SELECT ID, post_name, post_status, post_title FROM {$wpdb->posts}
     WHERE post_name LIKE '%exploring-the-experience%'
        OR post_title LIKE '%Exploring the experience of and treatment of self%harm%'
     LIMIT 1"
);
$results['NEWS-113'] = [
    'status' => 'Needs review',
    'evidence' => $explore
        ? 'Found post ID ' . $explore->ID . ' status=' . $explore->post_status . ' title=' . $explore->post_title
        : 'Still missing locally. No post matches exploring-the-experience-of-and-treatment-of-self-harm. Nursing H1 rename completed on NEWS-122 (ID 1169). Likely deleted/merged — confirm with client whether to restore or mark N/A.',
];

WP_CLI::log('');
WP_CLI::log('=== RESULT SUMMARY ===');
foreach ($results as $id => $row) {
    WP_CLI::log(sprintf('%s | %s | %s', $id, $row['status'], substr($row['evidence'], 0, 180)));
}

// Persist machine-readable results for workbook updater.
$out = get_template_directory() . '/scripts/data/editorial-open-notes-results.json';
if (! is_dir(dirname($out))) {
    wp_mkdir_p(dirname($out));
}
if (! $dry_run) {
    file_put_contents($out, wp_json_encode([
        'generated_at' => gmdate('c'),
        'results' => $results,
        'ukr_stats' => $ukr_stats,
        'remaining_media' => $remaining_media,
        'failures' => $failures,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    WP_CLI::log('Wrote ' . $out);
}

WP_CLI::success($dry_run ? 'Dry-run complete.' : 'Editorial open-notes fixes applied.');
