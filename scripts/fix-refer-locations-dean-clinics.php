<?php
/**
 * Remove Dean Clinic location cards (and the placeholder helper card) from
 * Healthcare Professionals refer pages — keep them only on Refer for Dean Clinics
 * (refer-for-outpatient-care).
 *
 * HTTP (staging/local):
 *   /wp-content/themes/matrix-starter/scripts/fix-refer-locations-dean-clinics.php?key=fix-refer-dean-loc
 *   Add &dry=1 to inspect without writing.
 *
 * CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/fix-refer-locations-dean-clinics.php
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-refer-dean-loc') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$dry = isset($_GET['dry']) || (defined('WP_CLI') && WP_CLI && in_array('--dry', $_SERVER['argv'] ?? [], true));

$pages = [
    'healthcare-professionals/refer-an-adult-for-inpatient-care' => 'strip',
    'healthcare-professionals/refer-an-adolescent-for-inpatient-care' => 'strip',
    'healthcare-professionals/refer-to-the-st-patricks-at-home-service' => 'strip',
    'healthcare-professionals/refer-to-a-day-programme' => 'strip',
    'healthcare-professionals/refer-for-outpatient-care' => 'keep_dean',
];

/**
 * @param array<int, mixed> $cards
 * @return array{0: array<int, mixed>, 1: list<string>}
 */
function matrix_filter_refer_location_cards(array $cards, string $mode): array
{
    $kept = [];
    $removed = [];

    foreach ($cards as $card) {
        if (! is_array($card)) {
            continue;
        }

        $title = trim(wp_strip_all_tags((string) ($card['title'] ?? '')));
        $link_title = '';
        if (is_array($card['link'] ?? null)) {
            $link_title = trim(wp_strip_all_tags((string) ($card['link']['title'] ?? '')));
        }

        $hay = $title . ' ' . $link_title;
        $is_helper = stripos($title, 'clinics are optional') !== false
            || stripos($title, 'Name of the hospital') !== false
            || stripos($title, 'no link') !== false;
        $is_dean = stripos($hay, 'Dean Clinic') !== false;

        if ($is_helper) {
            $removed[] = "helper:{$title}";
            continue;
        }

        if ($is_dean && $mode === 'strip') {
            $removed[] = "dean:{$title}";
            continue;
        }

        // On the Dean Clinics refer page, drop the Figma "(optional)" annotation from titles.
        if ($is_dean && $mode === 'keep_dean' && preg_match('/\s*\(optional\)\s*$/i', $title)) {
            $card['title'] = trim(preg_replace('/\s*\(optional\)\s*$/i', '', $title) ?? $title);
            $removed[] = "optional_label:{$title}";
        }

        $kept[] = $card;
    }

    return [$kept, $removed];
}

$report = [];

foreach ($pages as $path => $mode) {
    $post = get_page_by_path($path);

    if (! $post instanceof WP_Post) {
        $report[] = "MISS {$path}";
        continue;
    }

    $post_id = (int) $post->ID;
    $rows = get_field('flexible_content_blocks', $post_id);

    if (! is_array($rows) || $rows === []) {
        $report[] = "EMPTY flexi #{$post_id} {$path}";
        continue;
    }

    $changed = false;
    $page_notes = [];

    foreach ($rows as $index => $row) {
        if (! is_array($row) || ($row['acf_fc_layout'] ?? '') !== 'locations_grid') {
            continue;
        }

        $cards = is_array($row['cards'] ?? null) ? $row['cards'] : [];
        $before_titles = [];
        foreach ($cards as $card) {
            if (is_array($card)) {
                $before_titles[] = trim(wp_strip_all_tags((string) ($card['title'] ?? '')));
            }
        }

        [$kept, $removed] = matrix_filter_refer_location_cards($cards, $mode);

        $page_notes[] = "locations_grid[#{$index}] before=" . wp_json_encode($before_titles);
        $page_notes[] = "  mode={$mode} removed=" . wp_json_encode($removed);
        $page_notes[] = '  after=' . wp_json_encode(array_map(
            static fn ($c) => is_array($c) ? trim(wp_strip_all_tags((string) ($c['title'] ?? ''))) : '',
            $kept
        ));

        if ($removed !== []) {
            $rows[$index]['cards'] = array_values($kept);
            $changed = true;
        }
    }

    if ($page_notes === []) {
        $report[] = "NO locations_grid #{$post_id} {$path}";
        continue;
    }

    if ($changed && ! $dry) {
        update_field('flexible_content_blocks', $rows, $post_id);
        clean_post_cache($post_id);
        $page_notes[] = '  SAVED';
    } elseif ($changed) {
        $page_notes[] = '  DRY (not saved)';
    } else {
        $page_notes[] = '  unchanged';
    }

    $report[] = "#{$post_id} {$path}";
    foreach ($page_notes as $note) {
        $report[] = $note;
    }
}

echo ($dry ? "DRY RUN\n" : "APPLY\n");
echo implode("\n", $report) . "\n";
echo "DONE\n";
