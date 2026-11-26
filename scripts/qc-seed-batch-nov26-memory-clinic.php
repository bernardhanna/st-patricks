<?php
/**
 * QC seed 26 Nov: Memory Clinic — remove duplicate blue header.
 *
 * Cause: single-programmes_therapies.php always renders programme-therapy-hero
 * (blue H1), and Memory Clinic also had a flexi hero_with_breadcrumbs (second H1).
 *
 * WP-CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/qc-seed-batch-nov26-memory-clinic.php
 *
 * Staging:
 *   .../scripts/qc-seed-batch-nov26-memory-clinic.php?key=qc-batch-nov26-memory
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed || ($_GET['key'] ?? '') !== 'qc-batch-nov26-memory') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

@set_time_limit(0);
$dry = isset($_GET['dry']) || (defined('WP_CLI') && WP_CLI && in_array('--dry-run', $_SERVER['argv'] ?? [], true));
$log = [];
$say = static function (string $msg) use (&$log): void {
    $log[] = $msg;
    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::log($msg);
    }
};

$posts = get_posts([
    'name' => 'memory-clinic',
    'post_type' => 'programmes_therapies',
    'post_status' => 'any',
    'posts_per_page' => 1,
]);

if ($posts === []) {
    $say('MISS memory-clinic programmes_therapies');
    echo implode("\n", $log) . "\n";
    return;
}

$id = (int) $posts[0]->ID;
$say("memory-clinic #{$id}");

$blocks = get_field('flexible_content_blocks', $id);
if (! is_array($blocks)) {
    $blocks = [];
}

$hero_intro = '';
$kept = [];
$removed_hero = false;

foreach ($blocks as $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '');
    if ($layout === 'hero_with_breadcrumbs') {
        $removed_hero = true;
        $raw = (string) ($row['content'] ?? '');
        $hero_intro = trim(html_entity_decode(wp_strip_all_tags($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $hero_intro = preg_replace('/\s+/u', ' ', $hero_intro) ?? $hero_intro;
        $say('DROP flexi hero_with_breadcrumbs');
        continue;
    }
    $kept[] = $row;
}

if (! $removed_hero) {
    $say('OK already: no flexi hero_with_breadcrumbs');
}

$listing = trim((string) get_field('listing_summary', $id));
if ($listing === '' && $hero_intro !== '') {
    $listing = $hero_intro;
    $say('SET listing_summary from flexi hero intro');
} elseif ($listing === '') {
    $listing = 'Specialist assessment and support for memory difficulties.';
    $say('SET listing_summary fallback');
} else {
    $say('KEEP listing_summary');
}

$excerpt = trim((string) get_post_field('post_excerpt', $id));
$need_excerpt = $excerpt === '' || $excerpt !== $listing;

if ($dry) {
    $say('DRY: would update listing_summary, excerpt, flexi (' . count($blocks) . '→' . count($kept) . ')');
} else {
    if (function_exists('update_field')) {
        update_field('listing_summary', $listing, $id);
        update_field('flexible_content_blocks', $kept, $id);
    }

    $update = ['ID' => $id];
    if ($need_excerpt) {
        $update['post_excerpt'] = $listing;
    }
    wp_update_post($update);
    clean_post_cache($id);
    $say('SAVED listing_summary + flexi (' . count($blocks) . '→' . count($kept) . ')' . ($need_excerpt ? ' + excerpt' : ''));
}

// Evidence
$after_blocks = get_field('flexible_content_blocks', $id) ?: [];
$layouts = array_map(static fn ($b) => (string) ($b['acf_fc_layout'] ?? ''), $after_blocks);
$say('EVIDENCE layouts=' . implode(',', $layouts));
$say('EVIDENCE listing=' . substr(wp_strip_all_tags((string) get_field('listing_summary', $id)), 0, 120));
$say('EVIDENCE has_flexi_hero=' . (in_array('hero_with_breadcrumbs', $layouts, true) ? 'yes' : 'no'));

echo implode("\n", $log) . "\n";
