<?php
/**
 * Close What We Offer To-do snags:
 * - Eating Disorder Recovery Workshops for Families (row 27)
 * - Electroconvulsive Therapy (row 29)
 *
 * Browser (local/staging):
 *   /wp-content/themes/matrix-starter/scripts/fix-wwo-ed-workshops-ect.php?key=fix-wwo-ed-ect
 * Dry run:
 *   ...?key=fix-wwo-ed-ect&dry=1
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-wwo-ed-ect') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

@set_time_limit(0);
$dry = isset($_GET['dry']) || (defined('WP_CLI') && WP_CLI && in_array('--dry-run', $_SERVER['argv'] ?? [], true));
$lines = [];
$log = static function (string $msg) use (&$lines): void {
    $lines[] = $msg;
    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::log($msg);
    }
};

$programme_id = static function (string $slug): int {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'programmes_therapies',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);

    return $posts === [] ? 0 : (int) $posts[0]->ID;
};

$plain = static function (string $html): string {
    $text = html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

    return trim($text);
};

$strip_leading_paragraph_matching = static function (string $html, string $needle_plain) use ($plain): string {
    $needle = strtolower(trim($needle_plain));
    if ($needle === '' || trim($html) === '') {
        return $html;
    }

    $wrapped = preg_replace_callback(
        '#<(?:p|span)\b[^>]*(?:class=["\'][^"\']*\bintro\b[^"\']*["\'][^>]*)?>\s*.*?\s*</(?:p|span)>\s*#is',
        static function (array $m) use ($plain, $needle): string {
            $p = strtolower($plain($m[0]));
            if ($p === $needle || str_starts_with($p, $needle)) {
                return '';
            }

            return $m[0];
        },
        $html,
        1
    );
    if (is_string($wrapped) && $wrapped !== $html && ! str_starts_with(strtolower($plain($wrapped)), $needle)) {
        return ltrim($wrapped);
    }

    $p_only = preg_replace_callback(
        '#\A\s*(?:<div\b[^>]*>\s*</div>\s*|<h[1-6]\b[^>]*>\s*</h[1-6]>\s*)*<p\b[^>]*>\s*.*?\s*</p>\s*#is',
        static function (array $m) use ($plain, $needle): string {
            $p = strtolower($plain($m[0]));
            if ($p === $needle || str_starts_with($p, $needle)) {
                if (preg_match('#(<p\b[^>]*>\s*.*?\s*</p>\s*)\z#is', $m[0], $pm)) {
                    return substr($m[0], 0, -strlen($pm[1]));
                }

                return '';
            }

            return $m[0];
        },
        $html,
        1
    );
    if (is_string($p_only) && $p_only !== $html) {
        return ltrim($p_only);
    }

    return $html;
};

$rewrite_host_urls = static function (string $html): string {
    $home = untrailingslashit(home_url('/'));
    $patterns = [
        'https://www.stpatricks.ie',
        'http://www.stpatricks.ie',
        'https://stpatricks.ie',
        'http://stpatricks.ie',
        'http://localhost:10034',
        'https://localhost:10034',
        'http://st-patricks.local',
        'https://st-patricks.local',
    ];
    foreach ($patterns as $from) {
        $html = str_replace($from, $home, $html);
    }

    return $html;
};

$save_programme = static function (int $id, string $label, ?string $content = null, ?string $excerpt = null, ?string $listing = null) use ($dry, $log): void {
    if ($dry) {
        $log('DRY ' . $label . ' #' . $id);
        return;
    }
    $update = ['ID' => $id];
    if ($content !== null) {
        $update['post_content'] = $content;
    }
    if ($excerpt !== null) {
        $update['post_excerpt'] = $excerpt;
    }
    if (count($update) > 1) {
        wp_update_post($update);
    }
    if ($listing !== null && function_exists('update_field')) {
        update_field('listing_summary', $listing, $id);
    }
    clean_post_cache($id);
    $log('OK ' . $label . ' #' . $id);
};

$ed_programme_url = trailingslashit(home_url('/programmes-therapies/eating-disorder-programme'));
$mdt_url = trailingslashit(home_url('/about-us/our-team'));
$ed_programme_rel = wp_make_link_relative($ed_programme_url);
$mdt_rel = wp_make_link_relative($mdt_url);

// ---------------------------------------------------------------------------
// 1. Eating Disorder Recovery Workshops for Families
// ---------------------------------------------------------------------------
$ed_id = $programme_id('eating-disorder-recovery-workshops-for-families');
if ($ed_id > 0) {
    $dup = 'Our Eating Disorder Service team offers a programme for families or supporters of people experiencing eating disorders.';
    $content = (string) get_post_field('post_content', $ed_id);
    $excerpt = (string) get_post_field('post_excerpt', $ed_id);
    $listing = (string) get_field('listing_summary', $ed_id);
    $next = $strip_leading_paragraph_matching($content, $dup);

    // Also drop an isolated intro span/paragraph that matches the listing summary.
    $next = preg_replace(
        '#\s*<p>\s*<span\b[^>]*class=["\'][^"\']*\bintro\b[^"\']*["\'][^>]*>\s*' . preg_quote($dup, '#') . '\s*</span>\s*</p>\s*#iu',
        "\n",
        $next,
        1
    ) ?? $next;

    // Point "Eating Disorder Service" / "Eating Disorder Service team" at the programme page.
    $next = preg_replace(
        '#<a\b([^>]*)href=(["\'])([^"\']*)\2([^>]*)>\s*Eating Disorder Service(?:\s+team)?\s*</a>(\s*team)?#iu',
        '<a$1href="' . esc_url($ed_programme_url) . '"$4>Eating Disorder Service team</a>',
        $next
    ) ?? $next;
    // If still unlinked "Eating Disorder Service team", wrap it.
    if (! preg_match('#<a\b[^>]*>\s*Eating Disorder Service team\s*</a>#iu', $next)
        && preg_match('#Eating Disorder Service team#u', $next)
    ) {
        $next = preg_replace(
            '#(?<!>)\bEating Disorder Service team\b(?!</a>)#u',
            '<a href="' . esc_url($ed_programme_url) . '">Eating Disorder Service team</a>',
            $next,
            1
        ) ?? $next;
        $log('FIX ed-workshops: wrapped Eating Disorder Service team');
    }

    // MDT → Our team (staging destination).
    $next = preg_replace(
        '#(<a\b[^>]*href=["\'])[^"\']*multidisciplinary-teams/?(["\'][^>]*>)#iu',
        '$1' . esc_url($mdt_url) . '$2',
        $next
    ) ?? $next;
    $next = str_replace(
        [
            'https://www.stpatricks.ie/about-us/multidisciplinary-teams',
            'http://www.stpatricks.ie/about-us/multidisciplinary-teams',
            home_url('/about-us/multidisciplinary-teams/'),
            home_url('/about-us/multidisciplinary-teams'),
            '/about-us/multidisciplinary-teams/',
            '/about-us/multidisciplinary-teams',
        ],
        [
            $mdt_url,
            $mdt_url,
            $mdt_url,
            $mdt_url,
            $mdt_rel,
            $mdt_rel,
        ],
        $next
    );

    $next = $rewrite_host_urls($next);

    // Keep a single programme leaflet block (heading + first PDF link to this leaflet).
    if (preg_match_all('#<h2[^>]*>\s*See the programme leaflet\s*</h2>\s*(?:<p[^>]*>\s*)?<a\b[^>]*href=(["\'])([^"\']*eating-disorder-recovery-family-workshops\.pdf[^"\']*)\1[^>]*>.*?</a>\s*(?:</p>\s*)?#is', $next, $leaflet_blocks, PREG_SET_ORDER)
        && count($leaflet_blocks) > 1
    ) {
        $keep = $leaflet_blocks[0][0];
        $first = true;
        $next = preg_replace_callback(
            '#<h2[^>]*>\s*See the programme leaflet\s*</h2>\s*(?:<p[^>]*>\s*)?<a\b[^>]*href=(["\'])([^"\']*eating-disorder-recovery-family-workshops\.pdf[^"\']*)\1[^>]*>.*?</a>\s*(?:</p>\s*)?#is',
            static function (array $m) use (&$first, $keep): string {
                if ($first) {
                    $first = false;

                    return $keep;
                }

                return '';
            },
            $next
        ) ?? $next;
        $log('FIX ed-workshops: removed duplicate leaflet block(s)');
    } else {
        // Also collapse duplicate PDF anchors to the same leaflet file (keep first).
        if (preg_match_all('#<a\b[^>]*href=(["\'])([^"\']*eating-disorder-recovery-family-workshops\.pdf[^"\']*)\1[^>]*>.*?</a>#is', $next, $pdfs, PREG_SET_ORDER)
            && count($pdfs) > 1
        ) {
            $seen = false;
            $next = preg_replace_callback(
                '#(?:<p[^>]*>\s*)?<a\b[^>]*href=(["\'])([^"\']*eating-disorder-recovery-family-workshops\.pdf[^"\']*)\1[^>]*>.*?</a>\s*(?:</p>\s*)?#is',
                static function (array $m) use (&$seen): string {
                    if (! $seen) {
                        $seen = true;

                        return $m[0];
                    }

                    return '';
                },
                $next
            ) ?? $next;
            $log('FIX ed-workshops: removed duplicate leaflet PDF anchors');
        } else {
            $log('NOTE ed-workshops: single leaflet already present');
        }
    }

    $new_listing = $listing !== '' ? $plain($listing) : $dup;
    $new_excerpt = $excerpt !== '' ? $plain($excerpt) : $dup;

    if ($next !== $content || $new_listing !== $listing || $new_excerpt !== $excerpt) {
        $save_programme($ed_id, 'ed-workshops cleanup', $next, $new_excerpt, $new_listing);
        $log('NOTE ed-workshops service→' . $ed_programme_rel . ' mdt→' . $mdt_rel);
    } else {
        $log('SKIP ed-workshops: no change');
    }
} else {
    $log('MISS ed-workshops');
}

// ---------------------------------------------------------------------------
// 2. Electroconvulsive Therapy
// ---------------------------------------------------------------------------
$ect_id = $programme_id('electroconvulsive-therapy');
if ($ect_id > 0) {
    $dup = 'Electroconvulsive therapy (ECT) is an evidence-based treatment for some severe and persistent mental illnesses.';
    $unfinished = 'Here in St Patrick’s Mental Health Services (SPMHS), we provide ECT treatment in line with guidance from the Mental Health Commission (MHC).Organisations which';
    $unfinished_alt = 'Here in St Patrick\'s Mental Health Services (SPMHS), we provide ECT treatment in line with guidance from the Mental Health Commission (MHC).Organisations which';

    $content = (string) get_post_field('post_content', $ect_id);
    $excerpt = (string) get_post_field('post_excerpt', $ect_id);
    $listing = (string) get_field('listing_summary', $ect_id);

    $new_listing = $listing;
    $new_excerpt = $excerpt;

    // Remove unfinished header snippet from listing/excerpt if present.
    foreach ([$unfinished, $unfinished_alt] as $u) {
        if ($u !== '' && (str_contains($new_listing, $u) || str_contains($plain($new_listing), rtrim($plain($u), '….')))) {
            $new_listing = str_replace($u, '', $new_listing);
        }
        if ($u !== '' && (str_contains($new_excerpt, $u) || str_contains($plain($new_excerpt), rtrim($plain($u), '….')))) {
            $new_excerpt = str_replace($u, '', $new_excerpt);
        }
    }
    if (preg_match('/Mental Health Commission \(MHC\)\.?\s*Organisations which/iu', $new_listing)
        || preg_match('/Organisations which…/u', $new_listing)
        || preg_match('/Organisations which\.\.\./u', $new_listing)
    ) {
        $new_listing = $dup;
        $log('FIX ect: cleared unfinished Organisations snippet from listing_summary');
    }
    if (preg_match('/Mental Health Commission \(MHC\)\.?\s*Organisations which/iu', $new_excerpt)
        || preg_match('/Organisations which…/u', $new_excerpt)
        || preg_match('/Organisations which\.\.\./u', $new_excerpt)
    ) {
        $new_excerpt = $dup;
        $log('FIX ect: cleared unfinished Organisations snippet from excerpt');
    }

    // Ensure listing/excerpt are the clean evidence-based sentence (not unfinished mash).
    if (trim($plain($new_listing)) === '' || str_contains($plain($new_listing), 'Organisations which')) {
        $new_listing = $dup;
    } else {
        $new_listing = $plain($new_listing);
    }
    if (trim($plain($new_excerpt)) === '' || str_contains($plain($new_excerpt), 'Organisations which')) {
        $new_excerpt = $dup;
    } else {
        $new_excerpt = $plain($new_excerpt);
    }

    $next = $strip_leading_paragraph_matching($content, $dup);
    if ($next === $content && str_starts_with(strtolower($plain($content)), strtolower($dup))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($dup, '#') . '\s*(?:</p>)?#u',
            '',
            $content,
            1
        ) ?? $content;
    }

    // Also strip unfinished mashed sentence if it somehow lives in body as a single paragraph.
    $next = preg_replace(
        '#(?:<p\b[^>]*>\s*)?Here in St Patrick(?:\'|’)?s Mental Health Services \(SPMHS\), we provide ECT treatment in line with guidance from the Mental Health Commission \(MHC\)\.\s*Organisations which[^<\r\n]*(?:</p>)?\s*#iu',
        '',
        $next,
        1
    ) ?? $next;

    $next = $rewrite_host_urls($next);

    if ($next !== $content || $new_listing !== $listing || $new_excerpt !== $excerpt) {
        $save_programme($ect_id, 'ect cleanup', $next, $new_excerpt, $new_listing);
    } else {
        $log('SKIP ect: no change');
    }
} else {
    $log('MISS ect');
}

echo implode("\n", $lines) . "\n";
