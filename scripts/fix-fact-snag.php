<?php
/**
 * Close What We Offer To-do: Focused Acceptance and Commitment Therapy (row 32).
 *
 * 1. Unlink Mindfulness (keep plain text)
 * 2. Acceptance & Commitment Therapy → Acceptance and Commitment Therapy
 * 3. Remove duplicate eight-week intro from body (keep in listing/excerpt)
 *
 * Browser (local/staging):
 *   /wp-content/themes/matrix-starter/scripts/fix-fact-snag.php?key=fix-fact-snag
 * Dry run:
 *   ...?key=fix-fact-snag&dry=1
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-fact-snag') {
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

$dup = 'Focused Acceptance and Commitment Therapy (FACT) is an online group therapy programme that runs for eight weeks.';

$unlink_mindfulness = static function (string $html) use ($log): string {
    $next = preg_replace_callback(
        '#<a\b[^>]*>\s*(Mindfulness|mindfulness)\s*</a>#u',
        static function (array $m): string {
            return $m[1];
        },
        $html
    );
    if (! is_string($next)) {
        return $html;
    }
    if ($next !== $html) {
        $log('FIX fact: unlinked Mindfulness anchor(s)');
    }

    // Also unwrap anchors whose href clearly points at mindfulness media/PDF/image
    // even if link text differs slightly (e.g. wrapped spans).
    $next2 = preg_replace(
        '#<a\b[^>]*href=(["\'])([^"\']*(?:spmhs-mindfulness|mindfulness\.(?:pdf|jpg|jpeg|png|gif|webp)|/mindfulness/?)[^"\']*)\1[^>]*>(.*?)</a>#is',
        '$3',
        $next
    );
    if (is_string($next2) && $next2 !== $next) {
        $log('FIX fact: unlinked mindfulness href media/page anchors');
        $next = $next2;
    }

    return $next;
};

$fix_ampersand = static function (string $html) use ($log): string {
    $replacements = [
        'Acceptance &amp; Commitment Therapy' => 'Acceptance and Commitment Therapy',
        'Acceptance &#038; Commitment Therapy' => 'Acceptance and Commitment Therapy',
        'Acceptance & Commitment Therapy' => 'Acceptance and Commitment Therapy',
    ];
    $next = str_replace(array_keys($replacements), array_values($replacements), $html);
    if ($next !== $html) {
        $log('FIX fact: replaced Acceptance & with and');
    }

    return $next;
};

$strip_duplicate_intro = static function (string $html) use ($dup, $plain, $log): string {
    $needle = strtolower($dup);
    if (trim($html) === '') {
        return $html;
    }

    // Intro span (common migrated shape).
    $next = preg_replace_callback(
        '#(?:<p[^>]*>\s*)?<span\b[^>]*class=["\'][^"\']*\bintro\b[^"\']*["\'][^>]*>\s*.*?\s*</span>(?:\s*</p>)?\s*#is',
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
    if (is_string($next) && $next !== $html) {
        $log('FIX fact: removed duplicate intro span');
        $html = ltrim($next);
    }

    // Leading <p> that is only the duplicate sentence.
    $next = preg_replace_callback(
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
    if (is_string($next) && $next !== $html) {
        $log('FIX fact: removed duplicate leading paragraph');
        $html = ltrim($next);
    }

    return $html;
};

$apply_to_flexi = static function (int $id, callable $transform) use ($dry, $log): void {
    if (! function_exists('get_field') || ! function_exists('update_field')) {
        return;
    }
    $rows = get_field('flexible_content_blocks', $id);
    if (! is_array($rows)) {
        return;
    }
    $changed = false;
    foreach ($rows as $i => $row) {
        foreach (['content', 'intro_text', 'body', 'hero_intro', 'summary'] as $f) {
            $html = (string) ($row[$f] ?? '');
            if ($html === '') {
                continue;
            }
            $n = $transform($html);
            if ($n !== $html) {
                $rows[$i][$f] = $n;
                $changed = true;
            }
        }
    }
    if ($changed && ! $dry) {
        update_field('flexible_content_blocks', $rows, $id);
        $log('OK fact: updated flexible_content_blocks');
    } elseif ($changed) {
        $log('DRY fact: would update flexible_content_blocks');
    }
};

$id = $programme_id('focused-acceptance-and-commitment-therapy');
if ($id <= 0) {
    $log('MISS fact programme');
    echo implode("\n", $lines) . "\n";
    return;
}

$content = (string) get_post_field('post_content', $id);
$excerpt = (string) get_post_field('post_excerpt', $id);
$listing = function_exists('get_field') ? (string) get_field('listing_summary', $id) : '';

$next = $content;
$next = $unlink_mindfulness($next);
$next = $fix_ampersand($next);
$next = $strip_duplicate_intro($next);

$new_excerpt = $fix_ampersand($excerpt);
$new_listing = $fix_ampersand($listing);

// Keep listing/excerpt as the single copy of the eight-week sentence when empty after cleanup.
if ($plain($new_listing) === '' && $plain($new_excerpt) === '') {
    $new_listing = $dup;
    $new_excerpt = $dup;
    $log('FIX fact: restored listing/excerpt from canonical intro');
} elseif ($plain($new_listing) === '' && $plain($new_excerpt) !== '') {
    $new_listing = $plain($new_excerpt);
} elseif ($plain($new_excerpt) === '' && $plain($new_listing) !== '') {
    $new_excerpt = $plain($new_listing);
}

$flexi_transform = static function (string $html) use ($unlink_mindfulness, $fix_ampersand, $strip_duplicate_intro): string {
    $html = $unlink_mindfulness($html);
    $html = $fix_ampersand($html);
    $html = $strip_duplicate_intro($html);

    return $html;
};
$apply_to_flexi($id, $flexi_transform);

if ($dry) {
    $log('DRY fact #' . $id . ' content_changed=' . ($next !== $content ? '1' : '0'));
    $log('DRY listing_changed=' . ($new_listing !== $listing ? '1' : '0'));
    $log('DRY excerpt_changed=' . ($new_excerpt !== $excerpt ? '1' : '0'));
} elseif ($next !== $content || $new_listing !== $listing || $new_excerpt !== $excerpt) {
    wp_update_post([
        'ID' => $id,
        'post_content' => $next,
        'post_excerpt' => $new_excerpt,
    ]);
    if (function_exists('update_field')) {
        update_field('listing_summary', $new_listing, $id);
    }
    clean_post_cache($id);
    $log('OK fact #' . $id . ' saved');
} else {
    $log('SKIP fact: no post_content/listing/excerpt change');
}

// Evidence
$after = (string) get_post_field('post_content', $id);
$log('EVIDENCE mindfulness_anchor=' . (preg_match('#<a\b[^>]*>\s*mindfulness\s*</a>#iu', $after) ? 'YES' : 'NO'));
$log('EVIDENCE ampersand_act=' . (preg_match('#Acceptance\s*&(?:amp;)?\s*Commitment\s*Therapy#i', $after) ? 'YES' : 'NO'));
$body_plain = $plain($after);
$log('EVIDENCE body_has_dup_intro=' . (str_contains(strtolower($body_plain), strtolower($dup)) ? 'YES' : 'NO'));
$log('EVIDENCE listing=' . substr($plain((string) get_field('listing_summary', $id)), 0, 160));

echo implode("\n", $lines) . "\nDONE\n";
