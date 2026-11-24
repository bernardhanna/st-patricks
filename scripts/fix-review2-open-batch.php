<?php
/**
 * Review 2 — close remaining Open items (At Home order, adult stay rebuild, homepage heading).
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-open-batch.php?key=fix-review2
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-review2') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$log = [];

// ---------------------------------------------------------------------------
// 1. Homepage hero — restore line breaks in not-for-profit / mental heading
// ---------------------------------------------------------------------------
$home_id = (int) get_option('page_on_front');
if ($home_id <= 0) {
    $home_id = (int) (get_page_by_path('home')?->ID ?? 0);
}
if ($home_id > 0 && function_exists('get_field') && function_exists('update_field')) {
    $blocks = get_field('hero_content_blocks', $home_id);
    if (is_array($blocks)) {
        $updated = false;
        foreach ($blocks as &$row) {
            if (($row['acf_fc_layout'] ?? '') !== 'hero_slider' || ! is_array($row['slides'] ?? null)) {
                continue;
            }
            if (isset($row['slides'][0]['heading_text'])) {
                $row['slides'][0]['heading_text'] = "Ireland's largest independent\nnot-for-profit\nmental health service";
                $updated = true;
            }
        }
        unset($row);
        if ($updated) {
            update_field('hero_content_blocks', $blocks, $home_id);
            clean_post_cache($home_id);
            $log[] = 'OK homepage hero line breaks';
        } else {
            $log[] = 'SKIP homepage hero slides missing';
        }
    }
}

// ---------------------------------------------------------------------------
// 2. St Patrick's at Home — Getting started above FAQ1; What to expect above FAQ2
// ---------------------------------------------------------------------------
$athome = get_page_by_path('service-users-and-visitors/about-our-st-patricks-at-home-service');
$athome_id = $athome instanceof WP_Post ? (int) $athome->ID : 0;
if ($athome_id > 0) {
    $rows = get_field('flexible_content_blocks', $athome_id);
    if (is_array($rows) && $rows !== []) {
        $hero = null;
        $getting_content = null;
        $getting_acc = null;
        $expect_content = null;
        $expect_acc = null;
        $rest = [];
        $mode = 'before';

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $layout = (string) ($row['acf_fc_layout'] ?? '');
            $heading = trim((string) ($row['heading'] ?? ''));

            if ($layout === 'hero_with_breadcrumbs' && $hero === null) {
                $hero = $row;
                continue;
            }

            if ($layout === 'content' && $heading === 'Getting started in homecare') {
                $getting_content = $row;
                $mode = 'getting';
                continue;
            }

            if ($layout === 'content' && in_array($heading, ['What to expect from homecare', 'Receiving care'], true)) {
                // Skip early intro that sits before Getting started; keep the FAQ-paired one.
                if ($getting_content === null) {
                    continue;
                }
                $row['heading'] = 'What to expect from homecare';
                $expect_content = $row;
                $mode = 'expect';
                continue;
            }

            if ($layout === 'content_accordion') {
                if ($mode === 'getting' && $getting_acc === null) {
                    $getting_acc = $row;
                    continue;
                }
                if ($mode === 'expect' && $expect_acc === null) {
                    $expect_acc = $row;
                    continue;
                }
            }

            $rest[] = $row;
        }

        if ($hero && $getting_content && $getting_acc && $expect_content && $expect_acc) {
            $new_rows = array_merge(
                [$hero, $getting_content, $getting_acc, $expect_content, $expect_acc],
                $rest
            );
            update_field('flexible_content_blocks', $new_rows, $athome_id);
            clean_post_cache($athome_id);
            $log[] = 'OK at-home header order (Getting started → What to expect)';
        } else {
            $log[] = 'FAIL at-home reorder missing blocks'
                . ' hero=' . ($hero ? 'y' : 'n')
                . ' getC=' . ($getting_content ? 'y' : 'n')
                . ' getA=' . ($getting_acc ? 'y' : 'n')
                . ' expC=' . ($expect_content ? 'y' : 'n')
                . ' expA=' . ($expect_acc ? 'y' : 'n');
        }
    } else {
        $log[] = 'MISS at-home flexi';
    }
} else {
    $log[] = 'MISS at-home page';
}

// ---------------------------------------------------------------------------
// 3. Adult stay — rebuild flattened blob into hero + section + accordion groups
// ---------------------------------------------------------------------------
$adult = get_page_by_path('service-users-and-visitors/your-stay-in-hospital-as-an-adult');
$adult_id = $adult instanceof WP_Post ? (int) $adult->ID : 0;
$proof = get_template_directory() . '/proof/stay-adult.html';

if ($adult_id > 0 && is_readable($proof)) {
    $html = (string) file_get_contents($proof);

    // Fix known bad paths from proof
    $html = str_replace(
        [
            'href="/information-centre/"',
            'href="/inpatient-adolescent-unit/"',
            'href="/establishing-a-physical-health-monitoring-service-for-service-users-prescribed-psychotropic-medications/"',
        ],
        [
            'href="' . esc_url(home_url('/getting-help/information-centre/')) . '"',
            'href="' . esc_url(home_url('/service-users-and-visitors/your-care-with-willow-grove/your-stay-in-hospital-as-an-adolescent/')) . '"',
            'href="' . esc_url(home_url('/service-users-and-visitors/medication/')) . '"',
        ],
        $html
    );

    $parse_sections = static function (string $html): array {
        $parts = preg_split('/<h2>(.*?)<\/h2>/is', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (! is_array($parts) || count($parts) < 2) {
            return ['intro' => trim($html), 'sections' => []];
        }
        $intro = trim((string) $parts[0]);
        $sections = [];
        for ($i = 1; $i < count($parts); $i += 2) {
            $title = trim(wp_strip_all_tags((string) ($parts[$i] ?? '')));
            $body = trim((string) ($parts[$i + 1] ?? ''));
            if ($title === '' || strcasecmp($title, 'Useful links') === 0) {
                continue;
            }
            $sections[$title] = $body;
        }

        return ['intro' => $intro, 'sections' => $sections];
    };

    $parse_h3_items = static function (string $html): array {
        $intro = '';
        $items = [];
        // Prefer <li><h3>…</h3>…</li> structure from proof
        if (preg_match_all('/<li>\s*<h3>(.*?)<\/h3>(.*?)<\/li>/is', $html, $matches, PREG_SET_ORDER)) {
            $first = stripos($html, '<li>');
            if ($first !== false && $first > 0) {
                $intro = trim(substr($html, 0, $first));
                $intro = preg_replace('/<\/?ul>/i', '', $intro) ?? $intro;
            }
            foreach ($matches as $m) {
                $title = trim(wp_strip_all_tags($m[1]));
                $body = trim($m[2]);
                // Demote accidental H2s inside amenity bodies to bold labels
                $body = preg_replace('/<h2>(.*?)<\/h2>/is', '<p><strong>$1</strong></p>', $body) ?? $body;
                if ($title !== '' && $body !== '') {
                    $items[$title] = $body;
                }
            }

            return ['intro' => trim($intro), 'items' => $items];
        }

        if (preg_match_all('/<h3>(.*?)<\/h3>(.*?)(?=<h3>|$)/is', $html, $matches, PREG_SET_ORDER)) {
            $first = stripos($html, '<h3>');
            if ($first !== false && $first > 0) {
                $intro = trim(substr($html, 0, $first));
            }
            foreach ($matches as $m) {
                $title = trim(wp_strip_all_tags($m[1]));
                $body = trim($m[2]);
                if ($title !== '' && $body !== '') {
                    $items[$title] = $body;
                }
            }
        }

        return ['intro' => $intro, 'items' => $items];
    };

    $parsed = $parse_sections($html);
    $hero_intro = $parsed['intro'];
    // Strip trailing "Continue to…" junk from intro if present later sections hold it
    $hero_intro = preg_replace('/<p>\s*Continue to…\s*<\/p>.*/is', '', $hero_intro) ?? $hero_intro;

    $image_id = 0;
    if (function_exists('matrix_orlaith_find_image')) {
        $image_id = (int) matrix_orlaith_find_image(0, 'st-patricks-mental-health-services-group-room.jpg');
    }

    $rows = [
        matrix_orlaith_hero_row(
            'Your stay in hospital as an adult',
            $hero_intro !== '' ? $hero_intro : '<p>Here, we share useful information if you are receiving inpatient mental healthcare with St Patrick’s Mental Health Services (SPMHS).</p>',
            $image_id
        ),
    ];

    $bg_toggle = false;
    foreach ($parsed['sections'] as $section_title => $section_html) {
        $section_html = preg_replace('/<p>\s*Continue to…\s*<\/p>.*/is', '', $section_html) ?? $section_html;
        $bits = $parse_h3_items($section_html);
        $bg = $bg_toggle ? 'cream' : 'white';
        $bg_toggle = ! $bg_toggle;

        $intro = $bits['intro'] !== '' ? $bits['intro'] : '<p></p>';
        // If no accordion items, keep as content block only
        if ($bits['items'] === []) {
            $rows[] = matrix_orlaith_content_row($section_title, $section_html, $bg, 0, 'image_left');
            continue;
        }

        $rows[] = matrix_orlaith_content_row($section_title, $intro, $bg, 0, 'image_left', [
            'vertical_padding' => 'default',
        ]);
        $acc = matrix_orlaith_accordion_row($bits['items'], 'default', '');
        $acc['vertical_padding'] = 'bottom_only';
        if ($bg === 'cream') {
            $acc['section_background'] = '#F8F6F3';
        }
        $rows[] = $acc;
    }

    $rows[] = matrix_orlaith_useful_links_row([
        "St Patrick's University Hospital" => 'locations/st-patricks-university-hospital',
        "St Patrick's Hospital Lucan" => 'locations/st-patricks-hospital-lucan',
        'Visiting information' => 'service-users-and-visitors/directions-and-parking',
    ]);

    matrix_orlaith_save_page($adult_id, $rows, true, $image_id);
    $log[] = 'OK adult stay rebuild blocks=' . count($rows);
} else {
    $log[] = 'MISS adult stay page or proof html';
}

// ---------------------------------------------------------------------------
// 4. Contact Us — Opening hours → Visiting hours where still present
// ---------------------------------------------------------------------------
$contact = get_page_by_path('contact-us');
$contact_id = $contact instanceof WP_Post ? (int) $contact->ID : 0;
if ($contact_id > 0) {
    $rows = get_field('flexible_content_blocks', $contact_id);
    $changed = false;
    if (is_array($rows)) {
        $walk = static function (&$node) use (&$walk, &$changed): void {
            if (is_array($node)) {
                foreach ($node as $k => &$v) {
                    if (is_string($v) && str_contains($v, 'Opening hours')) {
                        $next = str_replace('Opening hours', 'Visiting hours', $v);
                        if ($next !== $v) {
                            $v = $next;
                            $changed = true;
                        }
                    } else {
                        $walk($v);
                    }
                }
                unset($v);
            }
        };
        $walk($rows);
        if ($changed) {
            update_field('flexible_content_blocks', $rows, $contact_id);
            clean_post_cache($contact_id);
            $log[] = 'OK contact Opening→Visiting hours';
        } else {
            $log[] = 'SKIP contact hours already OK or absent';
        }
    }
}

echo implode("\n", $log) . "\nDONE\n";
