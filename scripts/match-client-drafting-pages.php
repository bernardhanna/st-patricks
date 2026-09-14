<?php
/**
 * Match client-drafting JSON against existing WP content.
 *
 * Usage: wp eval-file scripts/match-client-drafting-pages.php
 */

$json_path = get_template_directory() . '/old/content/client-drafting-rows.json';
$set_path  = get_template_directory() . '/old/content/set-pages-rows.json';

if (! is_readable($json_path)) {
    WP_CLI::error('Missing ' . $json_path);
}

$drafting = json_decode((string) file_get_contents($json_path), true);
$set_pages = is_readable($set_path) ? json_decode((string) file_get_contents($set_path), true) : [];

$norm = static function ($s) {
    $s = strtolower(trim(wp_strip_all_tags((string) $s)));
    $s = preg_replace('/[^a-z0-9]+/', ' ', $s);
    return trim(preg_replace('/\s+/', ' ', $s));
};

$by_title = [];
$by_path  = [];
$by_old   = [];

$post_types = array_values(get_post_types(['public' => true], 'names'));
$q = new WP_Query([
    'post_type'      => $post_types,
    'post_status'    => ['publish', 'draft', 'pending', 'private'],
    'posts_per_page' => -1,
    'fields'         => 'ids',
]);

foreach ($q->posts as $pid) {
    $p = get_post($pid);
    $t = $norm($p->post_title);
    $by_title[$t][] = $pid;

    foreach ([get_page_uri($pid), wp_parse_url((string) get_permalink($pid), PHP_URL_PATH)] as $path) {
        $path = trim((string) $path, '/');
        if ($path !== '') {
            $by_path[$path][] = $pid;
        }
    }

    $old = (string) get_post_meta($pid, '_matrix_migrate_old_path', true);
    if ($old !== '') {
        $by_old[trim($old, '/')][] = $pid;
    }
}

$counts = ['title' => 0, 'old' => 0, 'fuzzy' => 0, 'none' => 0, 'with_layout' => 0];
$report = [];

foreach ($drafting as $row) {
    $title = (string) ($row['title'] ?? '');
    $nt    = $norm($title);
    $hit   = 0;
    $how   = 'none';

    if ($nt !== '' && ! empty($by_title[$nt])) {
        $hit = (int) $by_title[$nt][0];
        $how = count($by_title[$nt]) > 1 ? 'title-multi' : 'title';
        $counts['title']++;
    } else {
        $old_path = '';
        if (! empty($row['old'])) {
            $old_path = trim((string) wp_parse_url((string) $row['old'], PHP_URL_PATH), '/');
        }
        if ($old_path !== '' && ! empty($by_old[$old_path])) {
            $hit = (int) $by_old[$old_path][0];
            $how = 'old-meta';
            $counts['old']++;
        } elseif ($old_path !== '' && ! empty($by_path[$old_path])) {
            $hit = (int) $by_path[$old_path][0];
            $how = 'old-path';
            $counts['old']++;
        } elseif ($nt !== '' && strlen($nt) > 5) {
            foreach ($by_title as $kt => $ids) {
                if (str_contains($kt, $nt) || str_contains($nt, $kt)) {
                    $hit = (int) $ids[0];
                    $how = 'fuzzy';
                    $counts['fuzzy']++;
                    break;
                }
            }
        }
    }

    if (! $hit) {
        $counts['none']++;
    }

    $has_flexi = false;
    $has_hero  = false;
    if ($hit && function_exists('get_field')) {
        $flexi = get_field('flexible_content_blocks', $hit);
        $hero  = get_field('hero_content_blocks', $hit);
        $has_flexi = is_array($flexi) && count($flexi) > 0;
        $has_hero  = is_array($hero) && count($hero) > 0;
        if ($has_flexi || $has_hero) {
            $counts['with_layout']++;
        }
    }

    $report[] = [
        'sheet_id'   => $row['id'] ?? '',
        'title'      => $title,
        'type'       => $row['type'] ?? '',
        'section'    => $row['section'] ?? '',
        'how'        => $how,
        'wp_id'      => $hit,
        'wp_title'   => $hit ? get_the_title($hit) : '',
        'wp_status'  => $hit ? get_post_status($hit) : '',
        'wp_url'     => $hit ? get_permalink($hit) : '',
        'has_flexi'  => $has_flexi ? 'Y' : 'N',
        'has_hero'   => $has_hero ? 'Y' : 'N',
    ];
}

// Set Pages: resolve by path from page_link
$set_report = [];
$set_matched = 0;
foreach ($set_pages as $sp) {
    $url  = (string) ($sp['page_link'] ?? '');
    $path = trim((string) wp_parse_url($url, PHP_URL_PATH), '/');
    $hit  = 0;
    if ($path === '') {
        // homepage
        $front = (int) get_option('page_on_front');
        $hit = $front ?: 0;
    } elseif (! empty($by_path[$path])) {
        $hit = (int) $by_path[$path][0];
    }
    if ($hit) {
        $set_matched++;
    }
    $set_report[] = [
        'name' => $sp['name'] ?? '',
        'page_link' => $url,
        'wp_id' => $hit,
        'wp_title' => $hit ? get_the_title($hit) : '',
        'wp_status' => $hit ? get_post_status($hit) : '',
    ];
}

WP_CLI::log(sprintf(
    'Drafting: %d | title:%d old:%d fuzzy:%d none:%d | with hero/flexi:%d',
    count($drafting),
    $counts['title'],
    $counts['old'],
    $counts['fuzzy'],
    $counts['none'],
    $counts['with_layout']
));
WP_CLI::log(sprintf('Set Pages: %d matched of %d', $set_matched, count($set_pages)));

$out = get_template_directory() . '/old/content/client-drafting-match-report.csv';
$fh = fopen($out, 'w');
fputcsv($fh, array_keys($report[0]));
foreach ($report as $r) {
    fputcsv($fh, $r);
}
fclose($fh);

$set_out = get_template_directory() . '/old/content/set-pages-match-report.csv';
$fh = fopen($set_out, 'w');
fputcsv($fh, array_keys($set_report[0]));
foreach ($set_report as $r) {
    fputcsv($fh, $r);
}
fclose($fh);

WP_CLI::success('Wrote ' . $out);
WP_CLI::success('Wrote ' . $set_out);

WP_CLI::log("\n=== UNMATCHED drafting (need new shells) ===");
foreach ($report as $r) {
    if ($r['how'] === 'none') {
        WP_CLI::log($r['sheet_id'] . ' | ' . $r['section'] . ' | ' . $r['title']);
    }
}

WP_CLI::log("\n=== MATCHED drafting WITHOUT layout (title only / empty) ===");
foreach ($report as $r) {
    if ($r['how'] === 'none') {
        continue;
    }
    if ($r['has_flexi'] === 'N' && $r['has_hero'] === 'N') {
        WP_CLI::log(sprintf('%s | %s => #%d (%s)', $r['sheet_id'], $r['title'], $r['wp_id'], $r['wp_status']));
    }
}

WP_CLI::log("\n=== Set Pages unmatched ===");
foreach ($set_report as $r) {
    if (! $r['wp_id']) {
        WP_CLI::log($r['name'] . ' | ' . $r['page_link']);
    }
}
