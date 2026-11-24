<?php
/**
 * Bust caches and force FAQ 4130 + Dean directions FAQ wording.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-faq-4130-bust.php?key=fix-faq4130b
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-faq4130b') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

global $wpdb;

$desired = '<p>You will get reminders of your appointments. How you receive your reminders depends on whether you are attending in-person or remotely, and whether you use Your Portal or not. You can find out more about the reminders you will receive <a href="https://st-patricks.s1.matrix-test.com/your-portal/about-your-portal/">here</a>.</p>';

$wpdb->update($wpdb->posts, [
    'post_content' => $desired,
    'post_modified' => current_time('mysql'),
    'post_modified_gmt' => current_time('mysql', true),
], ['ID' => 4130]);

clean_post_cache(4130);
wp_cache_delete(4130, 'posts');
wp_cache_flush();

if (function_exists('rocket_clean_post')) {
    rocket_clean_post(4130);
}
if (function_exists('rocket_clean_domain')) {
    rocket_clean_domain();
}
if (class_exists('LiteSpeed_Cache_API') && method_exists('LiteSpeed_Cache_API', 'purge_post')) {
    LiteSpeed_Cache_API::purge_post(4130);
}

// Also patch any FAQ that still has About Your Portal as the reminders link text
$ids = $wpdb->get_col(
    "SELECT ID FROM {$wpdb->posts}
     WHERE post_type IN ('faqs','page') AND post_status='publish'
       AND post_content LIKE '%reminders you will receive%>%About Your Portal</a>%'
     LIMIT 50"
);
echo 'still About Your Portal count=' . count($ids) . ' ids=' . implode(',', $ids) . "\n";
foreach ($ids as $id) {
    $raw = (string) $wpdb->get_var($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE ID=%d", $id));
    $next = preg_replace(
        '#(reminders you will receive)\\s*<a([^>]*)>\\s*About Your Portal\\s*</a>#i',
        '$1 <a$2>here</a>',
        $raw
    ) ?? $raw;
    if ($next !== $raw) {
        $wpdb->update($wpdb->posts, ['post_content' => $next], ['ID' => (int) $id]);
        clean_post_cache((int) $id);
        echo "patched {$id}\n";
    }
}

// Directions sentence — search broader
$rows = $wpdb->get_results(
    "SELECT ID, post_type, post_name, post_content FROM {$wpdb->posts}
     WHERE post_status='publish'
       AND post_content LIKE '%addresses and directions for our Dean Clinics%'
     LIMIT 20"
);
echo 'directions posts=' . count($rows) . "\n";
foreach ($rows as $row) {
    $val = (string) $row->post_content;
    echo "  {$row->ID} {$row->post_type} {$row->post_name}\n";
    $n = preg_replace(
        '#(addresses and directions for our Dean Clinics)\\s*<a([^>]*)>\\s*Contact Us\\s*</a>\\.?#i',
        '$1. See <a$2>Contact Us</a>.',
        $val
    ) ?? $val;
    if ($n !== $val) {
        $wpdb->update($wpdb->posts, ['post_content' => $n], ['ID' => (int) $row->ID]);
        clean_post_cache((int) $row->ID);
        echo "  OK patched\n";
    }
}

// Meta containing Contact Us after Dean Clinics
$meta = $wpdb->get_results(
    "SELECT meta_id, post_id, meta_key, meta_value FROM {$wpdb->postmeta}
     WHERE meta_value LIKE '%addresses and directions for our Dean Clinics%'
     LIMIT 20"
);
echo 'directions meta=' . count($meta) . "\n";
foreach ($meta as $row) {
    if (is_serialized($row->meta_value)) {
        echo "  skip serialized {$row->meta_id}\n";
        continue;
    }
    $val = (string) $row->meta_value;
    $n = preg_replace(
        '#(addresses and directions for our Dean Clinics)\\s*<a([^>]*)>\\s*Contact Us\\s*</a>\\.?#i',
        '$1. See <a$2>Contact Us</a>.',
        $val
    ) ?? $val;
    if ($n !== $val) {
        $wpdb->update($wpdb->postmeta, ['meta_value' => $n], ['meta_id' => (int) $row->meta_id]);
        clean_post_cache((int) $row->post_id);
        echo "  OK meta {$row->meta_id} post {$row->post_id}\n";
    } else {
        echo "  no change meta {$row->meta_id} key={$row->meta_key}\n";
        if (preg_match('/addresses and directions for our Dean Clinics.{0,80}/', $val, $m)) {
            echo '  CTX ' . $m[0] . "\n";
        }
    }
}

$verify = (string) $wpdb->get_var("SELECT post_content FROM {$wpdb->posts} WHERE ID=4130");
echo "verify 4130 has here=" . (str_contains($verify, '>here</a>') ? 'yes' : 'no') . "\n";
echo "verify 4130 has About Your Portal link=" . (str_contains($verify, '>About Your Portal</a>') ? 'yes' : 'no') . "\n";
echo "DONE\n";
