<?php
/**
 * Swap Service User Participation hero image, and link CFT day-programme referral CTAs.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-participation-hero-and-cft-links.php?key=fix-part-cft
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-part-cft') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$log = [];

$participation_id = (int) (get_page_by_path('service-users-and-visitors/service-user-participation')?->ID ?? 0);
$hero_image_id = 4092; // Service-user-engagement.png 1280x720
if ($participation_id > 0 && $hero_image_id > 0 && wp_attachment_is_image($hero_image_id)) {
    $rows = get_field('flexible_content_blocks', $participation_id);
    if (is_array($rows)) {
        $updated = false;
        foreach ($rows as $i => $row) {
            $layout = (string) ($row['acf_fc_layout'] ?? '');
            if ($layout !== 'hero_with_breadcrumbs' && $layout !== 'hero') {
                continue;
            }
            $rows[$i]['hero_image'] = $hero_image_id;
            $rows[$i]['layout_style'] = 'image_split';
            $updated = true;
            break;
        }
        if ($updated) {
            update_field('flexible_content_blocks', $rows, $participation_id);
            set_post_thumbnail($participation_id, $hero_image_id);
            clean_post_cache($participation_id);
            $log[] = 'OK participation hero → #' . $hero_image_id . ' ' . basename((string) wp_get_attachment_url($hero_image_id));
        } else {
            $log[] = 'WARN participation hero row not found';
        }
    } else {
        $log[] = 'WARN participation flexi empty';
    }
} else {
    $log[] = 'MISS participation page or hero image';
}

$referral_url = home_url('/healthcare-professionals/refer-to-a-day-programme/');
$cft_slugs = [
    'cft-for-adolescents-and-families',
    'cft-for-older-adults',
];

foreach ($cft_slugs as $slug) {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'programmes_therapies',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    if ($posts === []) {
        $log[] = 'MISS ' . $slug;
        continue;
    }
    $post = $posts[0];
    $id = (int) $post->ID;
    $log[] = $slug . ' #' . $id . ' status=' . $post->post_status . ' url=' . get_permalink($id);

    $html = (string) $post->post_content;
    $rows = get_field('flexible_content_blocks', $id);
    $changed = false;

    $link_referral = static function (string $html) use ($referral_url): string {
        if (str_contains($html, $referral_url) && str_contains(strtolower($html), 'day programme')) {
            return $html;
        }

        $patterns = [
            '/Find out more about making a referral for day programmes here\\.?/i',
            '/making a referral for day programmes here\\.?/i',
        ];
        foreach ($patterns as $pattern) {
            $next = preg_replace(
                $pattern,
                '<a href="' . esc_url($referral_url) . '">Find out more about making a referral for day programmes here</a>.',
                $html,
                1,
                $count
            );
            if (is_string($next) && $count > 0) {
                return $next;
            }
        }

        return $html;
    };

    $new_html = $link_referral($html);
    if ($new_html !== $html) {
        wp_update_post([
            'ID' => $id,
            'post_content' => $new_html,
        ]);
        $changed = true;
        $log[] = 'OK ' . $slug . ' post_content referral linked';
    }

    if (is_array($rows)) {
        foreach ($rows as $i => $row) {
            foreach (['content', 'intro_text', 'description', 'body'] as $field) {
                if (empty($row[$field]) || ! is_string($row[$field])) {
                    continue;
                }
                $next = $link_referral($row[$field]);
                if ($next !== $row[$field]) {
                    $rows[$i][$field] = $next;
                    $changed = true;
                }
            }
        }
        if ($changed) {
            update_field('flexible_content_blocks', $rows, $id);
            clean_post_cache($id);
            $log[] = 'OK ' . $slug . ' flexi referral linked';
        }
    }

    if ($slug === 'cft-for-older-adults') {
        wp_set_object_terms($id, ['in-person'], 'delivery_format', false);
        $log[] = 'OK older adults delivery → in-person';
    }

    if (! $changed && $slug === 'cft-for-adolescents-and-families') {
        $log[] = 'NOTE adolescents still has Drive placeholders (xxx / xxxx / include…)';
    }
}

echo implode("\n", $log) . "\n";
