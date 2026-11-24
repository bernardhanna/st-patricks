<?php
/**
 * Surgical replace for contact-numbers + attending-day-programmes.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-contact-suits.php?key=fix-review2c
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-review2c') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

global $wpdb;
$log = [];

$replace_everywhere = static function (string $needle, string $replacement) use ($wpdb, &$log): void {
    $like = '%' . $wpdb->esc_like($needle) . '%';
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, meta_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE %s LIMIT 50",
        $like
    ));
    $log[] = 'meta matches for [' . substr($needle, 0, 40) . '…] = ' . count($rows);
    foreach ($rows as $row) {
        $val = $row->meta_value;
        if (! is_string($val) || ! str_contains($val, $needle)) {
            // serialized may contain escaped quotes — try unserialize path
            $un = maybe_unserialize($val);
            if ($un === $val) {
                continue;
            }
            $json = wp_json_encode($un);
            if (! is_string($json) || ! str_contains($json, $needle)) {
                $log[] = 'skip meta_id ' . $row->meta_id . ' key=' . $row->meta_key;
                continue;
            }
            $walk = static function ($node) use (&$walk, $needle, $replacement) {
                if (is_string($node)) {
                    return str_replace($needle, $replacement, $node);
                }
                if (is_array($node)) {
                    foreach ($node as $k => $v) {
                        $node[$k] = $walk($v);
                    }
                }
                return $node;
            };
            $next = $walk($un);
            update_post_meta((int) $row->post_id, $row->meta_key, $next);
            clean_post_cache((int) $row->post_id);
            $log[] = 'OK unserialize replace post=' . $row->post_id . ' key=' . $row->meta_key;
            continue;
        }
        $next = str_replace($needle, $replacement, $val);
        if ($next !== $val) {
            $wpdb->update($wpdb->postmeta, ['meta_value' => $next], ['meta_id' => (int) $row->meta_id]);
            clean_post_cache((int) $row->post_id);
            $log[] = 'OK raw replace post=' . $row->post_id . ' key=' . $row->meta_key;
        }
    }

    $posts = $wpdb->get_results($wpdb->prepare(
        "SELECT ID, post_content FROM {$wpdb->posts} WHERE post_content LIKE %s LIMIT 20",
        $like
    ));
    foreach ($posts as $p) {
        $next = str_replace($needle, $replacement, (string) $p->post_content);
        if ($next !== $p->post_content) {
            wp_update_post(['ID' => (int) $p->ID, 'post_content' => $next]);
            $log[] = 'OK post_content ' . $p->ID;
        }
    }
};

$old = 'The Clinical Governance Office manages feedback and complaints. Healthcare professionals can contact the office directly at <a href="mailto:clinicalgovernance@stpatricks.ie">clinicalgovernance@stpatricks.ie</a>.';
$new = 'The Clinical Governance Office manages feedback and complaints. Healthcare professionals and service users can contact the office directly at <a href="mailto:clinicalgovernance@stpatricks.ie">clinicalgovernance@stpatricks.ie</a>.';
$replace_everywhere($old, $new);

// Also plain-text variant without exact anchor markup
$replace_everywhere(
    'Healthcare professionals can contact the office directly at',
    'Healthcare professionals and service users can contact the office directly at'
);

// suits mailto — only add if not already mailto
$like = '%suits@stpatricks.ie%';
$rows = $wpdb->get_results($wpdb->prepare(
    "SELECT post_id, meta_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_value LIKE %s LIMIT 50",
    $like
));
$log[] = 'suits meta matches=' . count($rows);
foreach ($rows as $row) {
    $val = $row->meta_value;
    if (! is_string($val) || str_contains($val, 'mailto:suits@stpatricks.ie')) {
        continue;
    }
    if (! str_contains($val, 'suits@stpatricks.ie')) {
        $un = maybe_unserialize($val);
        if (! is_array($un)) {
            continue;
        }
        $hits = 0;
        $walk = static function ($node) use (&$walk, &$hits) {
            if (is_string($node)) {
                if (str_contains($node, 'suits@stpatricks.ie') && ! str_contains($node, 'mailto:suits@stpatricks.ie')) {
                    $hits++;
                    return preg_replace(
                        '#(?<!mailto:|">|\'>)suits@stpatricks\.ie#i',
                        '<a href="mailto:suits@stpatricks.ie">suits@stpatricks.ie</a>',
                        $node
                    ) ?? $node;
                }
                return $node;
            }
            if (is_array($node)) {
                foreach ($node as $k => $v) {
                    $node[$k] = $walk($v);
                }
            }
            return $node;
        };
        $next = $walk($un);
        if ($hits > 0) {
            update_post_meta((int) $row->post_id, $row->meta_key, $next);
            clean_post_cache((int) $row->post_id);
            $log[] = 'OK suits unserialize post=' . $row->post_id . ' hits=' . $hits;
        }
        continue;
    }
    $next = preg_replace(
        '#(?<!mailto:|">|\'>)suits@stpatricks\.ie#i',
        '<a href="mailto:suits@stpatricks.ie">suits@stpatricks.ie</a>',
        $val
    ) ?? $val;
    if ($next !== $val) {
        // If serialized, prefer unserialize path above; raw replace can break serialization.
        if (is_serialized($val)) {
            $log[] = 'SKIP serialized raw suits meta_id=' . $row->meta_id;
            continue;
        }
        $wpdb->update($wpdb->postmeta, ['meta_value' => $next], ['meta_id' => (int) $row->meta_id]);
        clean_post_cache((int) $row->post_id);
        $log[] = 'OK suits raw post=' . $row->post_id . ' key=' . $row->meta_key;
    }
}

echo implode("\n", $log) . "\nDONE\n";
