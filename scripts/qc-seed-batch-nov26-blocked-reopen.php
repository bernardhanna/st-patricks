<?php
/**
 * Reopen previously “blocked” snags that are actually actionable:
 * - Support groups: remove duplicate ACA H2 inside content
 * - Training Centre: keep standalone About Us page (stop folding redirect to National Centre)
 *
 * WP-CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/qc-seed-batch-nov26-blocked-reopen.php
 *
 * Staging:
 *   .../scripts/qc-seed-batch-nov26-blocked-reopen.php?key=qc-batch-nov26-reopen
 */
if (! defined('ABSPATH')) {
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);

    if (! $allowed || ($_GET['key'] ?? '') !== 'qc-batch-nov26-reopen') {
        http_response_code(403);
        exit;
    }

    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

@set_time_limit(0);
$log = [];

// ---------------------------------------------------------------------------
// 1) Support groups — drop duplicate ACA <h2> (section heading already exists)
// ---------------------------------------------------------------------------
$support = get_page_by_path('what-we-offer/support-groups-and-meetings')
    ?: get_page_by_path('support-groups-and-meetings');

if (! $support) {
    $log[] = 'MISS support-groups-and-meetings';
} else {
    $flex = get_field('flexible_content_blocks', $support->ID);
    if (! is_array($flex)) {
        $log[] = "MISS flexi on support #{$support->ID}";
    } else {
        $changed = false;
        foreach ($flex as $i => &$row) {
            if (($row['acf_fc_layout'] ?? '') !== 'content') {
                continue;
            }
            $heading = (string) ($row['heading'] ?? '');
            $content = (string) ($row['content'] ?? '');
            if (stripos($heading, 'Adult Children of Alcoholics') === false) {
                continue;
            }
            $next = preg_replace(
                '/^\s*<h2[^>]*>\s*Adult Children of Alcoholics\s*&amp;\s*Dysfunctional Families\s*\(ACA\)\s*<\/h2>\s*/i',
                '',
                $content,
                1,
                $count
            );
            if ($count < 1) {
                $next = preg_replace(
                    '/^\s*<h2[^>]*>\s*Adult Children of Alcoholics[^<]*<\/h2>\s*/i',
                    '',
                    $content,
                    1,
                    $count
                );
            }
            if ($count > 0 && is_string($next)) {
                $row['content'] = $next;
                $changed = true;
                $log[] = "DONE support #{$support->ID} removed dup ACA H2 (row {$i})";
            } else {
                $log[] = "SKIP support #{$support->ID} ACA H2 already clean";
            }
        }
        unset($row);

        if ($changed) {
            update_field('flexible_content_blocks', $flex, $support->ID);
        }
    }
}

// ---------------------------------------------------------------------------
// 2) Training Centre — restore standalone About Us page
//    QC: “the training centre should remain and not be deleted.”
// ---------------------------------------------------------------------------
$training = get_page_by_path('about-us/our-present-and-future/training-centre');
$national = get_page_by_path('about-us/our-present-and-future/national-centre');

if (! $training) {
    $log[] = 'MISS about-us/.../training-centre page';
} else {
    // Ensure published.
    if ($training->post_status !== 'publish') {
        wp_update_post(['ID' => $training->ID, 'post_status' => 'publish']);
        $log[] = "PUBLISHED training-centre #{$training->ID}";
    }

    // If body is thin, copy Training Centre block from National Centre.
    $tflex = get_field('flexible_content_blocks', $training->ID);
    $has_body = false;
    if (is_array($tflex)) {
        foreach ($tflex as $row) {
            if (($row['acf_fc_layout'] ?? '') === 'content' && trim(wp_strip_all_tags((string) ($row['content'] ?? ''))) !== '') {
                $has_body = true;
                break;
            }
        }
    }

    $training_html = '';
    if (! $has_body && $national) {
        $nflex = get_field('flexible_content_blocks', $national->ID);
        if (is_array($nflex)) {
            foreach ($nflex as $row) {
                if (($row['acf_fc_layout'] ?? '') !== 'content') {
                    continue;
                }
                if (strcasecmp(trim((string) ($row['heading'] ?? '')), 'Training Centre') !== 0) {
                    continue;
                }
                $training_html = (string) ($row['content'] ?? '');
                break;
            }
        }
    }

    // Fallback when National Centre no longer has a separate Training Centre block.
    if (! $has_body && $training_html === '') {
        $training_html = '<p>St Patrick’s Mental Health Services is establishing a dedicated Training Centre to advance the skills and competencies of those working in the mental healthcare sector.</p>'
            . '<p>We will expand our training for mental health professionals through the Training Centre. We will also further develop comprehensive continuing professional development (CPD) programmes for people working in mental healthcare and the organisations providing mental health services.</p>'
            . '<p>Our Training Centre will strengthen our existing training partnerships, while also creating new opportunities for the skills development of mental health professionals throughout Ireland.</p>';
        $log[] = 'NOTE using fallback Training Centre body copy';
    }

    if (! $has_body && $training_html !== '') {
        if (! is_array($tflex)) {
            $tflex = [];
        }
        $tflex[] = [
            'acf_fc_layout' => 'content',
            'heading' => '',
            'heading_tag' => 'h2',
            'accent_position' => 'below_heading',
            'intro_text' => '',
            'content' => $training_html,
        ];
        update_field('flexible_content_blocks', $tflex, $training->ID);
        $log[] = "DONE training-centre #{$training->ID} body written";
    } elseif ($has_body) {
        $log[] = "OK training-centre #{$training->ID} already has body";
    }
}

// Theme redirect map: stop folding About Us Training Centre into National Centre.
// Keep typo slug → correct training-centre URL.
$map_file = get_template_directory() . '/inc/data/path-redirect-map.json';
if (is_readable($map_file)) {
    $map = json_decode((string) file_get_contents($map_file), true);
    if (is_array($map)) {
        $targets = [
            'about-us/our-present-and-future/traning-centre',
            'about-us/our-present-and-future/training-centre',
            '/about-us/our-present-and-future/traning-centre',
            '/about-us/our-present-and-future/training-centre',
            'traning-centre',
        ];
        $changed_map = false;
        foreach ($targets as $key) {
            if (! isset($map[$key])) {
                continue;
            }
            $dest = (string) $map[$key];
            if (str_contains($dest, 'national-centre')) {
                // Typo → correct standalone; exact training-centre identity removed.
                if (str_contains($key, 'traning-centre') || $key === 'traning-centre') {
                    $map[$key] = '/about-us/our-present-and-future/training-centre/';
                    $changed_map = true;
                    $log[] = "MAP {$key} → /about-us/our-present-and-future/training-centre/";
                } else {
                    unset($map[$key]);
                    $changed_map = true;
                    $log[] = "MAP removed identity/fold {$key}";
                }
            }
        }
        // Also remove bare training-centre → national if present
        foreach (array_keys($map) as $key) {
            if (! str_contains($key, 'training-centre') && ! str_contains($key, 'traning-centre')) {
                continue;
            }
            // Do not touch healthcare-professionals/training-centre*
            if (str_contains($key, 'healthcare-professionals')) {
                continue;
            }
            $dest = (string) $map[$key];
            if (str_contains($dest, 'national-centre')) {
                if (preg_match('#traning-centre#', $key)) {
                    $map[$key] = '/about-us/our-present-and-future/training-centre/';
                } else {
                    unset($map[$key]);
                }
                $changed_map = true;
                $log[] = "MAP scrub {$key}";
            }
        }

        if ($changed_map) {
            // Seed running in browser cannot write theme files on staging reliably;
            // still attempt locally / when writable.
            if (is_writable($map_file)) {
                file_put_contents(
                    $map_file,
                    json_encode($map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
                );
                $log[] = 'MAP file written';
            } else {
                $log[] = 'WARN map not writable here — ship via git';
            }
        } else {
            $log[] = 'MAP already clean for About Us training-centre';
        }
    }
}

foreach ($log as $line) {
    echo $line . "\n";
}
echo "DONE qc-seed-batch-nov26-blocked-reopen\n";
