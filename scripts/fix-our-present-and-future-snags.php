<?php
/**
 * Our present and future snag close-outs:
 * - Reorder video showcase to follow accordion objectives
 * - Fix Parntership typo
 * - Ensure hero uses "Here at"
 * - Align Operational excellence body with client "We will" copy
 *
 * Browser (local/staging only):
 *   /wp-content/themes/matrix-starter/scripts/fix-our-present-and-future-snags.php?key=fix-present-future
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed_host = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed_host || ($_GET['key'] ?? '') !== 'fix-present-future') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$log = [];
$p = static fn (string $html): string => '<p>' . $html . '</p>';

/**
 * Extract a YouTube id from a slide row.
 */
$youtube_id = static function (array $slide): string {
    $candidates = [
        (string) ($slide['video_embed_url'] ?? ''),
        (string) ($slide['video_url'] ?? ''),
        (string) ($slide['url'] ?? ''),
    ];
    foreach ($candidates as $url) {
        if ($url === '') {
            continue;
        }
        if (preg_match('/(?:v=|youtu\.be\/|embed\/)([\w-]{6,15})/', $url, $m)) {
            return $m[1];
        }
    }

    return '';
};

// Preferred order = accordion objectives, then remaining strategy videos.
$preferred_ids = [
    'ZkX7DqQE_60', // Mental healthcare / Service Delivery
    'MZHEqYPaRJk', // Partnership / Service User Engagement
    'AjJQxOrmv1o', // Research + Training
    'b2bj9aNjxRE', // Human rights advocacy + Education
    'msSxhK4RJ7A', // Operational Excellence
    '-8t1qfAk0qc', // Strategy overview
];

$present_id = (int) (get_page_by_path('about-us/our-present-and-future')?->ID ?? 0);
if ($present_id <= 0) {
    echo "MISS our-present-and-future\n";
    return;
}
$log[] = 'OK present #' . $present_id;

$rows = get_field('flexible_content_blocks', $present_id);
if (! is_array($rows) || $rows === []) {
    echo "FAIL no flexi on present\n";
    return;
}

$changed = false;

foreach ($rows as $i => $row) {
    $layout = (string) ($row['acf_fc_layout'] ?? '');

    // Hero: Here in → Here at
    if ($layout === 'hero_with_breadcrumbs' || $layout === 'hero') {
        foreach (['intro_text', 'content', 'hero_intro'] as $field) {
            $html = (string) ($row[$field] ?? '');
            if ($html === '' || ! str_contains($html, 'Here in')) {
                continue;
            }
            $next = str_replace(
                [
                    'Here in St Patrick’s Mental Health Services',
                    "Here in St Patrick's Mental Health Services",
                    'Here in St Patrick&rsquo;s Mental Health Services',
                    'Here in SPMHS',
                ],
                [
                    'Here at St Patrick’s Mental Health Services',
                    "Here at St Patrick's Mental Health Services",
                    'Here at St Patrick&rsquo;s Mental Health Services',
                    'Here at SPMHS',
                ],
                $html
            );
            if ($next !== $html) {
                $rows[$i][$field] = $next;
                $changed = true;
                $log[] = 'OK hero Here at';
            }
        }
    }

    // Strategic blocks typo
    if ($layout === 'content') {
        foreach (['content', 'intro_text'] as $field) {
            $html = (string) ($row[$field] ?? '');
            if ($html === '') {
                continue;
            }
            $next = str_replace('Parntership', 'Partnership', $html);
            if ($next !== $html) {
                $rows[$i][$field] = $next;
                $changed = true;
                $log[] = 'OK Parntership → Partnership';
            }
        }
    }

    // Reorder video showcase slides
    if ($layout === 'video_showcase' && ! empty($row['slides']) && is_array($row['slides'])) {
        $by_id = [];
        foreach ($row['slides'] as $slide) {
            if (! is_array($slide)) {
                continue;
            }
            $id = $youtube_id($slide);
            if ($id !== '') {
                $by_id[$id] = $slide;
            }
        }

        $ordered = [];
        foreach ($preferred_ids as $id) {
            if (isset($by_id[$id])) {
                $ordered[] = $by_id[$id];
                unset($by_id[$id]);
            }
        }
        // Keep any unexpected slides at the end.
        foreach ($by_id as $slide) {
            $ordered[] = $slide;
        }

        if ($ordered !== [] && $ordered !== $row['slides']) {
            $rows[$i]['slides'] = $ordered;
            $changed = true;
            $log[] = 'OK videos reordered: ' . implode(', ', array_map($youtube_id, $ordered));
        } else {
            $log[] = 'OK videos already ordered';
        }
    }
}

if ($changed) {
    update_field('flexible_content_blocks', $rows, $present_id);
    clean_post_cache($present_id);
    $log[] = 'OK present flexi saved';
}

// Operational excellence body copy
$oe_id = (int) (get_page_by_path('about-us/our-present-and-future/operational-excellence')?->ID ?? 0);
if ($oe_id <= 0) {
    $log[] = 'MISS operational-excellence page';
} else {
    $oe_rows = get_field('flexible_content_blocks', $oe_id);
    if (! is_array($oe_rows)) {
        $oe_rows = [];
    }

    $body = $p('We aim to ensure the highest standards in our operations. By investing in staff, technology, sustainability and constant evaluation, we are able to set an ambitious strategic vision.')
        . $p('We will:')
        . '<ul>'
        . '<li>Enhance our Human Resources Strategy to ensure we continue to attract, recruit and retain the highest quality staff and to empower our staff to reach their full potential through learning and development</li>'
        . '<li>Continue to implement a value-driven financial model that delivers the most positive outcomes for service users in the most cost-effective ways</li>'
        . '<li>Ensure we are delivering the most efficient service that we can by continuously assessing clinical outcomes based on efficacy and cost</li>'
        . '<li>Embed best practice in sustainability and positive climate actions into our organisational ethos and strategic plans.</li>'
        . '</ul>';

    $updated_oe = false;
    foreach ($oe_rows as $i => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'content') {
            continue;
        }
        // Skip empty/heading-only rows; replace the first substantial content block.
        $existing = trim(wp_strip_all_tags((string) ($row['content'] ?? '')));
        if ($existing === '' && trim((string) ($row['heading'] ?? '')) !== '') {
            continue;
        }
        $oe_rows[$i]['heading'] = '';
        $oe_rows[$i]['content'] = $body;
        $updated_oe = true;
        break;
    }

    if (! $updated_oe) {
        array_splice($oe_rows, 1, 0, [matrix_orlaith_content_row('', $body, 'white')]);
        $updated_oe = true;
    }

    if ($updated_oe) {
        update_field('flexible_content_blocks', $oe_rows, $oe_id);
        clean_post_cache($oe_id);
        $log[] = 'OK OE body #' . $oe_id;
    }
}

echo implode("\n", $log) . "\n";
