<?php
/**
 * Close finishable About Us snags (no client-wait content).
 *
 * - Partnering: video heading → Watch | Learn more about service user engagement
 * - Our locations: accordion closed by default; referrals link → make-a-referral
 * - Media queries: Media 1 / Media 2 from change-requests
 * - Careers: Career 1.jpg hero
 *
 * Browser (local/staging):
 *   /wp-content/themes/matrix-starter/scripts/fix-about-us-open-batch.php?key=fix-about-batch
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed_host = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed_host || ($_GET['key'] ?? '') !== 'fix-about-batch') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

@set_time_limit(0);
$log = [];
$theme = get_template_directory();
$changes = $theme . '/new/SPMHS-Content-Gathering-Library/change-requests';

$sideload = static function (string $path, string $title, int $parent = 0): int {
    if (! is_readable($path)) {
        return 0;
    }
    $basename = basename($path);
    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'meta_query' => [[
            'key' => '_matrix_drive_source',
            'value' => $basename,
        ]],
    ]);
    if ($existing !== []) {
        return (int) $existing[0];
    }
    // Also try by filename in uploads.
    $by_name = matrix_orlaith_find_image(0, $basename);
    if ($by_name > 0) {
        return $by_name;
    }

    $tmp = wp_tempnam($basename);
    if (! $tmp || ! copy($path, $tmp)) {
        return 0;
    }
    $id = media_handle_sideload([
        'name' => $basename,
        'tmp_name' => $tmp,
    ], $parent, $title);
    if (is_wp_error($id)) {
        @unlink($tmp);

        return 0;
    }
    update_post_meta((int) $id, '_matrix_drive_source', $basename);

    return (int) $id;
};

$set_hero_image = static function (int $post_id, int $image_id) use (&$log): void {
    if ($post_id <= 0 || $image_id <= 0) {
        return;
    }
    $rows = get_field('flexible_content_blocks', $post_id);
    if (! is_array($rows)) {
        return;
    }
    foreach ($rows as $i => $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout !== 'hero_with_breadcrumbs' && $layout !== 'hero') {
            continue;
        }
        $rows[$i]['hero_image'] = $image_id;
        $rows[$i]['layout_style'] = 'image_split';
        update_field('flexible_content_blocks', $rows, $post_id);
        set_post_thumbnail($post_id, $image_id);
        clean_post_cache($post_id);
        $log[] = 'OK hero #' . $post_id . ' img #' . $image_id;

        return;
    }
};

// -------------------------------------------------------------------------
// Partnering: video heading
// -------------------------------------------------------------------------
$partner_id = (int) (get_page_by_path('about-us/our-present-and-future/partnering-with-service-users')?->ID ?? 0);
if ($partner_id > 0) {
    $rows = get_field('flexible_content_blocks', $partner_id);
    if (is_array($rows)) {
        $changed = false;
        foreach ($rows as $i => $row) {
            if (($row['acf_fc_layout'] ?? '') !== 'video_showcase') {
                continue;
            }
            $heading = trim((string) ($row['heading'] ?? ''));
            if ($heading !== 'Watch | Learn more about service user engagement') {
                $rows[$i]['heading'] = 'Watch | Learn more about service user engagement';
                $changed = true;
            }
        }
        if ($changed) {
            update_field('flexible_content_blocks', $rows, $partner_id);
            clean_post_cache($partner_id);
            $log[] = 'OK partnering video heading';
        } else {
            $log[] = 'OK partnering video heading already set';
        }
    }
} else {
    $log[] = 'MISS partnering';
}

// -------------------------------------------------------------------------
// Our locations: accordion closed + referrals link
// -------------------------------------------------------------------------
$loc_id = (int) (get_page_by_path('about-us/our-locations')?->ID ?? 0);
if ($loc_id > 0) {
    $rows = get_field('flexible_content_blocks', $loc_id);
    $changed = false;
    if (is_array($rows)) {
        $referrals_target = home_url('/make-a-referral/');
        // Prefer contact numbers if that page exists.
        $contact_numbers = get_page_by_path('healthcare-professionals/contact-numbers');
        if ($contact_numbers instanceof WP_Post) {
            $referrals_target = get_permalink($contact_numbers);
        }

        foreach ($rows as $i => $row) {
            $layout = (string) ($row['acf_fc_layout'] ?? '');

            if ($layout === 'content_accordion' && ! empty($row['items']) && is_array($row['items'])) {
                foreach ($row['items'] as $j => $item) {
                    if (! empty($item['starts_open'])) {
                        $rows[$i]['items'][$j]['starts_open'] = 0;
                        $changed = true;
                    }
                }
            }

            foreach (['content', 'intro_text'] as $field) {
                $html = (string) ($row[$field] ?? '');
                if ($html === '' || ! str_contains($html, 'See more from our referrals team')) {
                    continue;
                }
                $next = preg_replace(
                    '#href=(["\'])[^"\']*\1(?=[^>]*>See more from our referrals team)#i',
                    'href=$1' . esc_url($referrals_target) . '$1',
                    $html
                );
                // Also replace bare healthcare-professionals hub links in that sentence.
                $next = str_replace(
                    [
                        home_url('/healthcare-professionals/'),
                        '/healthcare-professionals/',
                    ],
                    [
                        $referrals_target,
                        wp_make_link_relative($referrals_target),
                    ],
                    (string) $next
                );
                // Only if the referrals-team anchor is present — re-apply precise href.
                if (preg_match('#<a[^>]*>See more from our referrals team</a>#i', (string) $next, $m)) {
                    $next = preg_replace(
                        '#<a[^>]*>See more from our referrals team</a>#i',
                        '<a href="' . esc_url($referrals_target) . '">See more from our referrals team</a>',
                        (string) $next,
                        1
                    );
                }
                if ($next !== $html) {
                    $rows[$i][$field] = $next;
                    $changed = true;
                }
            }
        }

        if ($changed) {
            update_field('flexible_content_blocks', $rows, $loc_id);
            clean_post_cache($loc_id);
            $log[] = 'OK locations accordion + referrals → ' . $referrals_target;
        } else {
            $log[] = 'OK locations already fixed';
        }
    }
} else {
    $log[] = 'MISS locations';
}

// -------------------------------------------------------------------------
// Media queries: Media 1 (hero) + Media 2 (body image)
// -------------------------------------------------------------------------
$media_id = (int) (get_page_by_path('about-us/media-queries')?->ID ?? 0);
if ($media_id > 0) {
    $media1 = $sideload($changes . '/Media 1.png', 'Media 1', $media_id);
    $media2 = $sideload($changes . '/Media 2.png', 'Media 2', $media_id);
    if ($media1 > 0) {
        $set_hero_image($media_id, $media1);
    } else {
        $log[] = 'WARN Media 1 missing';
    }

    if ($media2 > 0) {
        $rows = get_field('flexible_content_blocks', $media_id);
        if (is_array($rows)) {
            $swapped = false;
            foreach ($rows as $i => $row) {
                // Prefer content/image split blocks mentioning Responsible reporting.
                $heading = strtolower((string) ($row['heading'] ?? ''));
                $layout = (string) ($row['acf_fc_layout'] ?? '');
                if (str_contains($heading, 'responsible reporting')
                    || (in_array($layout, ['content', 'content_cta', 'image_text'], true)
                        && ! empty($row['image'])
                        && $i > 0)
                ) {
                    if (str_contains($heading, 'responsible') || (! $swapped && $i > 0 && ! empty($row['image']))) {
                        $rows[$i]['image'] = $media2;
                        $swapped = true;
                        if (str_contains($heading, 'responsible')) {
                            break;
                        }
                    }
                }
            }
            // Fallback: second image-bearing row after hero.
            if (! $swapped) {
                $seen_hero = false;
                foreach ($rows as $i => $row) {
                    $layout = (string) ($row['acf_fc_layout'] ?? '');
                    if (in_array($layout, ['hero_with_breadcrumbs', 'hero'], true)) {
                        $seen_hero = true;
                        continue;
                    }
                    if ($seen_hero && array_key_exists('image', $row)) {
                        $rows[$i]['image'] = $media2;
                        $swapped = true;
                        break;
                    }
                }
            }
            if ($swapped) {
                update_field('flexible_content_blocks', $rows, $media_id);
                clean_post_cache($media_id);
                $log[] = 'OK media queries Media 2 #' . $media2;
            } else {
                $log[] = 'WARN media queries Media 2 slot not found';
            }
        }
    } else {
        $log[] = 'WARN Media 2 missing';
    }
} else {
    $log[] = 'MISS media-queries';
}

// -------------------------------------------------------------------------
// Careers: Career 1 hero
// -------------------------------------------------------------------------
$careers_id = (int) (get_page_by_path('about-us/careers')?->ID ?? 0);
if ($careers_id > 0) {
    $career1 = $sideload($changes . '/Career 1.jpg', 'Career 1', $careers_id);
    if ($career1 > 0) {
        $set_hero_image($careers_id, $career1);
    } else {
        $log[] = 'WARN Career 1 missing';
    }
} else {
    $log[] = 'MISS careers';
}

echo implode("\n", $log) . "\n";
