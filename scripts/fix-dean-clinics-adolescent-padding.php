<?php
/**
 * Equalise Adult / Adolescent Dean Clinics about_links_grid spacing.
 *
 * Adult keeps top-only padding so the pair stacks; Adolescent gets equal
 * top & bottom (default 100px desktop) and leftover padding_settings are
 * cleared so they cannot override the preset.
 *
 * HTTP (staging/local):
 *   /wp-content/themes/matrix-starter/scripts/fix-dean-clinics-adolescent-padding.php?key=fix-dean-adolescent-pad
 *
 * CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/fix-dean-clinics-adolescent-padding.php
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-dean-adolescent-pad') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$post = get_page_by_path('what-we-offer/outpatient-care-dean-clinics');

if (! $post instanceof WP_Post) {
    echo "FAIL page not found\n";
    exit(1);
}

$post_id = (int) $post->ID;
$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows) || $rows === []) {
    echo "FAIL no flexi rows on #{$post_id}\n";
    exit(1);
}

$updated = [];

foreach ($rows as $index => $row) {
    if (! is_array($row) || ($row['acf_fc_layout'] ?? '') !== 'about_links_grid') {
        continue;
    }

    $heading = trim(wp_strip_all_tags((string) ($row['heading_text'] ?? $row['heading'] ?? '')));

    if (strcasecmp($heading, 'Adult Dean Clinics') === 0) {
        $rows[$index]['vertical_padding'] = 'no_bottom';
        $rows[$index]['padding_settings'] = [];
        $updated[] = "Adult[#{$index}]=no_bottom";
        continue;
    }

    if (strcasecmp($heading, 'Adolescent Dean Clinics') === 0) {
        $rows[$index]['vertical_padding'] = 'default';
        $rows[$index]['padding_settings'] = [];
        $updated[] = "Adolescent[#{$index}]=default";
    }
}

if ($updated === []) {
    echo "FAIL no Adult/Adolescent about_links_grid rows on #{$post_id}\n";
    exit(1);
}

update_field('flexible_content_blocks', $rows, $post_id);
clean_post_cache($post_id);

echo 'OK dean clinics #' . $post_id . ': ' . implode(', ', $updated) . "\n";
