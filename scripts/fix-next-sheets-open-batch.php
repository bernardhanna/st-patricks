<?php
/**
 * Close finishable HCP / What We Offer / Service Users snags (no client-wait).
 *
 * - Webinars and events: keep short hero; move programme sentence above archive
 * - Adolescent homecare: fix SUITS + remote practical URLs
 *
 * Browser:
 *   /wp-content/themes/matrix-starter/scripts/fix-next-sheets-open-batch.php?key=fix-next-sheets
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed_host = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed_host || ($_GET['key'] ?? '') !== 'fix-next-sheets') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$log = [];

$programme_sentence = 'Our programme includes online CPD-accredited education sessions for GPs and online and in-person events for healthcare professionals and members of the public who wish to deepen their understanding of mental health topics.';
$short_hero = 'We host a range of webinars and in-person events throughout the year to support learning, professional development and public awareness of mental health.';

// --- Webinars and events ---
$webinars = get_page_by_path('healthcare-professionals/webinars-events');
if ($webinars instanceof WP_Post) {
    $id = (int) $webinars->ID;
    if ($webinars->post_title !== 'Webinars and events') {
        wp_update_post([
            'ID' => $id,
            'post_title' => 'Webinars and events',
        ]);
        $log[] = "webinars: retitled post to Webinars and events";
    }

    $rows = get_field('flexible_content_blocks', $id);
    if (! is_array($rows)) {
        $rows = [];
    }

    $hero_idx = null;
    $archive_idx = null;
    $has_programme_block = false;
    foreach ($rows as $i => $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'hero_with_breadcrumbs' && $hero_idx === null) {
            $hero_idx = $i;
        }
        if ($layout === 'webinars_archive' && $archive_idx === null) {
            $archive_idx = $i;
        }
        $content = (string) ($row['content'] ?? '');
        if (str_contains($content, 'Our programme includes online CPD-accredited')) {
            $has_programme_block = true;
        }
    }

    if ($hero_idx !== null) {
        $rows[$hero_idx]['heading'] = 'Webinars and events';
        $rows[$hero_idx]['current_crumb_label'] = 'Webinars and events';
        $rows[$hero_idx]['content'] = '<p>' . esc_html($short_hero) . '</p>';
        $log[] = 'webinars: hero kept to short intro only';
    }

    if (! $has_programme_block && $archive_idx !== null) {
        $intro_row = [
            'acf_fc_layout' => 'content',
            'heading' => '',
            'content' => '<p>' . esc_html($programme_sentence) . '</p>',
            'background_color' => '#FFFFFF',
            'text_color' => '#08284B',
        ];
        array_splice($rows, $archive_idx, 0, [$intro_row]);
        $log[] = 'webinars: programme sentence inserted above archive';
    } elseif (! $has_programme_block) {
        $rows[] = [
            'acf_fc_layout' => 'content',
            'heading' => '',
            'content' => '<p>' . esc_html($programme_sentence) . '</p>',
            'background_color' => '#FFFFFF',
            'text_color' => '#08284B',
        ];
        $log[] = 'webinars: programme sentence appended (no archive index)';
    } else {
        $log[] = 'webinars: programme sentence already present outside hero';
    }

    update_field('flexible_content_blocks', $rows, $id);
    $log[] = "webinars: saved #$id";
} else {
    $log[] = 'webinars: page missing';
}

// --- Adolescent homecare links ---
$homecare = get_page_by_path('service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent');
if ($homecare instanceof WP_Post) {
    $id = (int) $homecare->ID;
    $rows = get_field('flexible_content_blocks', $id);
    if (! is_array($rows)) {
        $log[] = "homecare: no flexi rows on #$id";
    } else {
        $suits = matrix_orlaith_permalink('service-users-and-visitors/service-user-it-support');
        $remote = matrix_orlaith_permalink('what-you-need-to-know-about-remote-care');
        $stay = matrix_orlaith_permalink('service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent');
        $changed = 0;

        $replace_href = static function (string $html, array $map) use (&$changed): string {
            foreach ($map as $from => $to) {
                if ($from === '' || $to === '' || $from === $to) {
                    continue;
                }
                $next = str_replace($from, $to, $html);
                if ($next !== $html) {
                    $changed++;
                    $html = $next;
                }
            }

            return $html;
        };

        $map = [
            home_url('/service-user-it-support') => $suits,
            home_url('/service-user-it-support/') => $suits,
            'https://st-patricks.s1.matrix-test.com/service-user-it-support' => $suits,
            'https://st-patricks.s1.matrix-test.com/service-user-it-support/' => $suits,
            '/service-user-it-support' => $suits,
            '/service-user-it-support/' => $suits,
            home_url('/practical-information-for-remote-services') => $remote,
            home_url('/practical-information-for-remote-services/') => $remote,
            '/practical-information-for-remote-services' => $remote,
            '/practical-information-for-remote-services/' => $remote,
        ];

        foreach ($rows as $i => $row) {
            foreach (['content', 'description', 'body'] as $key) {
                if (! empty($row[$key]) && is_string($row[$key])) {
                    $rows[$i][$key] = $replace_href($row[$key], $map);
                }
            }
            if (! empty($row['accordion_items']) && is_array($row['accordion_items'])) {
                foreach ($row['accordion_items'] as $ai => $item) {
                    foreach (['content', 'answer', 'body', 'description'] as $key) {
                        if (! empty($item[$key]) && is_string($item[$key])) {
                            $rows[$i]['accordion_items'][$ai][$key] = $replace_href($item[$key], $map);
                        }
                    }
                }
            }
            if (! empty($row['items']) && is_array($row['items'])) {
                foreach ($row['items'] as $ii => $item) {
                    foreach (['content', 'answer', 'body', 'description'] as $key) {
                        if (! empty($item[$key]) && is_string($item[$key])) {
                            $rows[$i]['items'][$ii][$key] = $replace_href($item[$key], $map);
                        }
                    }
                }
            }
        }

        // Ensure care-team phrasing links to adolescent stay page.
        $encoded = wp_json_encode($rows);
        if (is_string($encoded) && str_contains($encoded, 'care team') && $stay !== '') {
            // Soft check only — leave existing correct stay URL alone.
            $log[] = 'homecare: care-team link target expected → ' . $stay;
        }

        update_field('flexible_content_blocks', $rows, $id);
        $log[] = "homecare: saved #$id replacements=$changed suits=$suits remote=$remote";
    }
} else {
    $log[] = 'homecare: page missing';
}

// --- Memory clinic existence check (no invent content) ---
$memory = get_posts([
    'name' => 'memory-clinic',
    'post_type' => ['programmes_therapies', 'page'],
    'post_status' => ['publish', 'draft'],
    'posts_per_page' => 1,
]);
if ($memory !== []) {
    $m = $memory[0];
    $log[] = 'memory-clinic: exists #' . $m->ID . ' status=' . $m->post_status . ' title=' . $m->post_title;
} else {
    $log[] = 'memory-clinic: missing';
}

$psych = get_posts([
    'name' => 'psychology-skills-group-for-adolescents',
    'post_type' => ['programmes_therapies', 'page'],
    'post_status' => 'publish',
    'posts_per_page' => 1,
]);
if ($psych !== []) {
    $log[] = 'psychology-skills: live at ' . get_permalink($psych[0]);
} else {
    $log[] = 'psychology-skills: missing under that slug';
}

echo implode("\n", $log) . "\n";
if (class_exists('WP_CLI')) {
    foreach ($log as $line) {
        WP_CLI::log($line);
    }
}
