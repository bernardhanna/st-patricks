<?php
/**
 * Close What We Offer menu To-do snags (clear theme/content only).
 *
 * Browser (local/staging):
 *   /wp-content/themes/matrix-starter/scripts/fix-wwo-todos-batch.php?key=fix-wwo-todos
 * Dry run:
 *   ...?key=fix-wwo-todos&dry=1
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-wwo-todos') {
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

    // Prefer explicit intro wrappers used by migrated programme HTML.
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

    // Leading <p> only.
    $p_only = preg_replace_callback(
        '#\A\s*(?:<div\b[^>]*>\s*</div>\s*|<h[1-6]\b[^>]*>\s*</h[1-6]>\s*)*<p\b[^>]*>\s*.*?\s*</p>\s*#is',
        static function (array $m) use ($plain, $needle): string {
            $p = strtolower($plain($m[0]));
            if ($p === $needle || str_starts_with($p, $needle)) {
                // Keep empty chrome before the intro if present; drop only the intro <p>.
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

    // Bare-text first block (no wrapping tags), split on blank line.
    if (preg_match('#\A((?:<div\b[^>]*>\s*</div>\s*|<h[1-6]\b[^>]*>\s*</h[1-6]>\s*)*)([^<\r\n][^\r\n]*(?:\r?\n(?!\r?\n)[^\r\n]*)*)(\r?\n\r?\n)?#u', $html, $m)) {
        $p = strtolower($plain($m[2]));
        if ($p === $needle || str_starts_with($p, $needle)) {
            return ltrim($m[1] . substr($html, strlen($m[0])));
        }
    }

    return $html;
};

$remove_paragraphs_matching = static function (string $html, array $needles) use ($plain): string {
    if ($html === '' || $needles === []) {
        return $html;
    }

    $next = preg_replace_callback(
        '#<(?:p|span)\b[^>]*>\s*.*?\s*</(?:p|span)>#is',
        static function (array $m) use ($plain, $needles): string {
            $p = strtolower($plain($m[0]));
            foreach ($needles as $needle) {
                $n = strtolower(trim((string) $needle));
                if ($n === '') {
                    continue;
                }
                if ($p === $n || str_contains($p, $n)) {
                    return '';
                }
            }

            return $m[0];
        },
        $html
    );

    return is_string($next) ? $next : $html;
};

$fix_missing_space_after_stop = static function (string $html): string {
    return preg_replace('/\.(?=[A-ZÀ-ÖØ-Þ])/u', '. ', $html) ?? $html;
};

$rewrite_urls = static function (string $html, array $map): string {
    foreach ($map as $from => $to) {
        if ($from === '' || $to === '') {
            continue;
        }
        $html = str_replace($from, $to, $html);
    }

    return $html;
};

$mdt_url = trailingslashit(home_url('/about-us/our-team'));
$post_permalink_by_slug = static function (string $slug, string $fallback_path): string {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'fields' => 'ids',
    ]);
    if ($posts !== []) {
        $url = get_permalink((int) $posts[0]);
        if (is_string($url) && $url !== '') {
            return trailingslashit($url);
        }
    }

    return trailingslashit(home_url('/' . trim($fallback_path, '/')));
};
$psychosis_url = $post_permalink_by_slug('living-with-psychosis', 'living-with-psychosis');
$psychosis2_url = $post_permalink_by_slug('living-with-psychosis-2', 'living-with-psychosis-2');
$voices_url = $post_permalink_by_slug('voices-in-my-head-what-do-they-mean', 'voices-in-my-head-what-do-they-mean');

$anxiety_url = home_url('/programmes-therapies/anxiety-disorders-programme/');
$url_map_common = [
    'https://www.stpatricks.ie/about-us/multidisciplinary-teams' => $mdt_url,
    'http://www.stpatricks.ie/about-us/multidisciplinary-teams' => $mdt_url,
    'https://stpatricks.ie/about-us/multidisciplinary-teams' => $mdt_url,
    '/about-us/multidisciplinary-teams/' => wp_make_link_relative($mdt_url),
    '/about-us/multidisciplinary-teams' => wp_make_link_relative($mdt_url),
    home_url('/about-us/multidisciplinary-teams/') => $mdt_url,
    home_url('/about-us/multidisciplinary-teams') => $mdt_url,
    'https://www.stpatricks.ie/inpatient-care' => home_url('/inpatient-care/'),
    'https://www.stpatricks.ie/inpatient-care/' => home_url('/inpatient-care/'),
    'https://www.stpatricks.ie/what-we-offer/st-patricks-at-home' => home_url('/what-we-offer/st-patricks-at-home/'),
    'https://www.stpatricks.ie/what-we-offer/st-patricks-at-home/' => home_url('/what-we-offer/st-patricks-at-home/'),
    'programmes-therapies/anxiety-disorders-programme-2' => 'programmes-therapies/anxiety-disorders-programme',
];

$save_programme = static function (int $id, string $label, ?string $content = null, ?string $excerpt = null, ?string $listing = null) use ($dry, $log): void {
    if ($dry) {
        $log('DRY ' . $label . ' #' . $id);
        return;
    }
    // HTTP context strips iframes via kses; allow embeds for programme bodies.
    $kses_off = false;
    if ($content !== null && str_contains($content, '<iframe')) {
        kses_remove_filters();
        $kses_off = true;
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
    if ($kses_off) {
        kses_init_filters();
    }
    if ($listing !== null && function_exists('update_field')) {
        update_field('listing_summary', $listing, $id);
    }
    clean_post_cache($id);
    $log('OK ' . $label . ' #' . $id);
};

// ---------------------------------------------------------------------------
// 1. Day programmes — CTA casing, leaflet label, stray click-here
// ---------------------------------------------------------------------------
$day_id = (int) (get_page_by_path('what-we-offer/day-programmes')?->ID ?? 0);
if ($day_id > 0) {
    $rows = get_field('flexible_content_blocks', $day_id);
    $changed = false;
    if (is_array($rows)) {
        foreach ($rows as $i => $row) {
            if (($row['acf_fc_layout'] ?? '') !== 'content') {
                continue;
            }
            $btn = $row['primary_button'] ?? null;
            // Repair corrupted ACF link fields where serialized link was stuffed into url.
            if (is_array($btn) && isset($btn['url']) && is_string($btn['url']) && str_starts_with($btn['url'], 'a:')) {
                $un = maybe_unserialize($btn['url']);
                if (is_array($un) && isset($un['url'])) {
                    $rows[$i]['primary_button'] = [
                        'title' => (string) ($un['title'] ?? 'Full list of programmes'),
                        'url' => (string) $un['url'],
                        'target' => (string) ($un['target'] ?? '_self'),
                    ];
                    $btn = $rows[$i]['primary_button'];
                    $changed = true;
                    $log('FIX day: repaired corrupted Full list button serialization');
                }
            }
            if (is_array($btn)) {
                $title = trim((string) ($btn['title'] ?? ''));
                if ($title === '' && is_string($btn['url'] ?? null) && preg_match('/select-programme-or-therapy/', (string) $btn['url'])) {
                    $title = 'Full list of programmes';
                    $rows[$i]['primary_button']['title'] = $title;
                    $changed = true;
                }
                if (preg_match('/^full list of programmes$/i', $title) && $title !== 'Full list of programmes') {
                    $rows[$i]['primary_button']['title'] = 'Full list of programmes';
                    $changed = true;
                    $log('FIX day: Full list CTA → sentence case');
                }
            } elseif (is_string($btn) && str_contains($btn, 'Full')) {
                $next = preg_replace('/Full List of Programmes/i', 'Full list of programmes', $btn) ?? $btn;
                if ($next !== $btn) {
                    $rows[$i]['primary_button'] = $next;
                    $changed = true;
                    $log('FIX day: Full list CTA string normalized');
                }
            }

            $doc = $row['document_link'] ?? null;
            if (is_array($doc) && isset($doc['title'])) {
                $dt = trim((string) $doc['title']);
                if ($dt !== '' && strcasecmp($dt, 'Read the leaflet') === 0) {
                    $rows[$i]['document_link']['title'] = 'Read more about our day programmes';
                    $changed = true;
                    $log('FIX day: Read the leaflet → Read more about our day programmes');
                }
            }

            $content = (string) ($row['content'] ?? '');
            if ($content !== '' && stripos($content, 'Click here for more information about attending a programme') !== false) {
                $rows[$i]['content'] = preg_replace(
                    '#<p\b[^>]*>\s*Click here for more information about attending a programme\.?\s*</p>#i',
                    '',
                    $content
                ) ?? $content;
                $changed = true;
                $log('FIX day: removed stray Click here paragraph');
            }
        }
        if ($changed) {
            if ($dry) {
                $log('DRY day-programmes flexi #' . $day_id);
            } else {
                update_field('flexible_content_blocks', $rows, $day_id);
                // Also clear serialized row meta if ACF left the click-here string.
                $meta_content = (string) get_post_meta($day_id, 'flexible_content_blocks_2_content', true);
                if (stripos($meta_content, 'Click here for more information about attending a programme') !== false) {
                    $meta_next = preg_replace(
                        '#<p\b[^>]*>\s*Click here for more information about attending a programme\.?\s*</p>#i',
                        '',
                        $meta_content
                    ) ?? $meta_content;
                    update_post_meta($day_id, 'flexible_content_blocks_2_content', $meta_next);
                }
                $btn_meta = get_post_meta($day_id, 'flexible_content_blocks_1_primary_button', true);
                $fixed_btn = null;
                if (is_array($btn_meta) && isset($btn_meta['url']) && is_string($btn_meta['url']) && str_starts_with($btn_meta['url'], 'a:')) {
                    $un = maybe_unserialize($btn_meta['url']);
                    if (is_array($un) && ! empty($un['url'])) {
                        $fixed_btn = [
                            'title' => ((string) ($un['title'] ?? '') !== '' ? (string) $un['title'] : 'Full list of programmes'),
                            'url' => (string) $un['url'],
                            'target' => (string) ($un['target'] ?? '_self'),
                        ];
                    }
                } elseif (is_array($btn_meta)) {
                    $fixed_btn = $btn_meta;
                    if (trim((string) ($fixed_btn['title'] ?? '')) === '') {
                        $fixed_btn['title'] = 'Full list of programmes';
                    } elseif (preg_match('/^full list of programmes$/i', (string) $fixed_btn['title'])) {
                        $fixed_btn['title'] = 'Full list of programmes';
                    }
                } elseif (is_string($btn_meta) && str_starts_with($btn_meta, 'a:')) {
                    $un = maybe_unserialize($btn_meta);
                    if (is_array($un) && ! empty($un['url'])) {
                        $fixed_btn = [
                            'title' => ((string) ($un['title'] ?? '') !== '' ? (string) $un['title'] : 'Full list of programmes'),
                            'url' => (string) $un['url'],
                            'target' => (string) ($un['target'] ?? '_self'),
                        ];
                    }
                }
                if (is_array($fixed_btn)) {
                    $fixed_btn['title'] = 'Full list of programmes';
                    update_post_meta($day_id, 'flexible_content_blocks_1_primary_button', $fixed_btn);
                }
                $doc_meta = get_post_meta($day_id, 'flexible_content_blocks_2_document_link', true);
                if (is_array($doc_meta) && strcasecmp((string) ($doc_meta['title'] ?? ''), 'Read the leaflet') === 0) {
                    $doc_meta['title'] = 'Read more about our day programmes';
                    update_post_meta($day_id, 'flexible_content_blocks_2_document_link', $doc_meta);
                }
                clean_post_cache($day_id);
                $log('OK day-programmes #' . $day_id);
            }
        } else {
            $log('SKIP day-programmes: already clean or no matching fields');
        }
    } else {
        $log('MISS day-programmes flexi');
    }
} else {
    $log('MISS day-programmes page');
}

// ---------------------------------------------------------------------------
// 2. Anxiety — ensure correct slug / rewrite -2 links sitewide in content
// ---------------------------------------------------------------------------
$anxiety_id = $programme_id('anxiety-disorders-programme');
$anxiety2_id = $programme_id('anxiety-disorders-programme-2');
if ($anxiety_id > 0) {
    $log('OK anxiety canonical #' . $anxiety_id . ' ' . get_permalink($anxiety_id));
} else {
    $log('MISS anxiety-disorders-programme');
}
if ($anxiety2_id > 0 && $anxiety_id > 0 && $anxiety2_id !== $anxiety_id) {
    // Prefer redirecting the -2 slug rather than inventing content.
    if (! $dry) {
        wp_update_post([
            'ID' => $anxiety2_id,
            'post_name' => 'anxiety-disorders-programme-2-redirected',
            'post_status' => 'draft',
        ]);
    }
    $log(($dry ? 'DRY' : 'OK') . ' anxiety-2 demoted #' . $anxiety2_id);
}

// Soft global replace in programme/page content for -2 anxiety links.
global $wpdb;
$anxiety_like = '%anxiety-disorders-programme-2%';
$hit_ids = $wpdb->get_col($wpdb->prepare(
    "SELECT ID FROM {$wpdb->posts} WHERE post_status NOT IN ('trash','auto-draft') AND post_type IN ('post','page','programmes_therapies') AND post_content LIKE %s LIMIT 80",
    $anxiety_like
));
foreach ($hit_ids as $hid) {
    $hid = (int) $hid;
    $c = (string) get_post_field('post_content', $hid);
    $n = str_replace('anxiety-disorders-programme-2', 'anxiety-disorders-programme', $c);
    if ($n !== $c) {
        if (! $dry) {
            wp_update_post(['ID' => $hid, 'post_content' => $n]);
        }
        $log(($dry ? 'DRY' : 'OK') . ' rewrote anxiety-2 link in post #' . $hid);
    }
}

// ---------------------------------------------------------------------------
// 3. Art Therapy — remove duplicate intro from body
// ---------------------------------------------------------------------------
$art_id = $programme_id('art-therapy');
if ($art_id > 0) {
    $dup = 'At St Patrick’s Mental Health Services (SPMHS), we provide art therapy in support of mental health recovery.';
    $content = (string) get_post_field('post_content', $art_id);
    $next = $strip_leading_paragraph_matching($content, $dup);
    // Also handle content without <p> wrappers.
    if ($next === $content && str_starts_with($plain($content), strtolower($dup))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($dup, '#') . '\s*(?:</p>)?#u',
            '',
            $content,
            1
        ) ?? $content;
    }
    if ($next !== $content) {
        $save_programme($art_id, 'art-therapy remove duplicate intro', $next);
    } else {
        $log('SKIP art-therapy: duplicate intro not found at start');
    }
}

// ---------------------------------------------------------------------------
// 4. Binge Eating — ensure YouTube embed; remove duplicate intro; dedupe Watch heading
// ---------------------------------------------------------------------------
$binge_id = $programme_id('binge-eating-disorder-programme');
if ($binge_id > 0) {
    $dup = 'The Binge Eating Disorder Programme is designed to support people who are experiencing difficulties with binge eating symptoms and behaviours.';
    $content = (string) get_post_field('post_content', $binge_id);
    $next = $strip_leading_paragraph_matching($content, $dup);
    if ($next === $content && str_starts_with($plain($content), strtolower($dup))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($dup, '#') . '\s*(?:</p>)?#u',
            '',
            $content,
            1
        ) ?? $content;
    }
    // Collapse duplicate Watch headings.
    $next = preg_replace(
        '#(<h[2-4][^>]*>\s*Watch a talk on binge eating disorder\s*</h[2-4]>\s*){2,}#i',
        '$1',
        $next
    ) ?? $next;

    $iframe = '<iframe title="YouTube video player" src="https://www.youtube.com/embed/oGPDcc_kxU4" width="560" height="315" frameborder="0" allowfullscreen="allowfullscreen" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>';
    if (! preg_match('#(?:youtube\.com/embed/|youtu\.be/)oGPDcc_kxU4#', $next)) {
        if (preg_match('#Watch a talk on binge eating disorder#i', $next)) {
            $replaced = preg_replace(
                '#(Watch a talk on binge eating disorder\s*</h[2-4]>)#i',
                '$1' . "\n" . $iframe,
                $next,
                1,
                $count
            );
            $next = (is_string($replaced) && $count > 0) ? $replaced : ($next . "\n" . $iframe);
        } else {
            $next .= "\n<h3>Watch a talk on binge eating disorder</h3>\n" . $iframe;
        }
        $log('FIX binge: embedded oGPDcc_kxU4');
    }

    $next = $rewrite_urls($next, $url_map_common);
    if ($next !== $content) {
        $save_programme($binge_id, 'binge-eating embed/dedupe', $next);
    } else {
        $log('SKIP binge: no content change needed');
    }
}

// ---------------------------------------------------------------------------
// 5. Bipolar — remove unfinished snippet sentence; remove duplicate intro; staging links
// ---------------------------------------------------------------------------
$bipolar_id = $programme_id('bipolar-education-programme');
if ($bipolar_id > 0) {
    $unfinished_needles = [
        'Bipolar disorder is a mood disorder where a person experiences extreme changes in their mood, which can range from depressed mood to an…',
        'Bipolar disorder is a mood disorder where a person experiences extreme changes in their mood, which can range from depressed mood to an...',
    ];
    $excerpt = (string) get_post_field('post_excerpt', $bipolar_id);
    $listing = (string) get_field('listing_summary', $bipolar_id);
    $content = (string) get_post_field('post_content', $bipolar_id);
    $new_excerpt = $excerpt;
    $new_listing = $listing;
    foreach ($unfinished_needles as $u) {
        if (str_contains($new_excerpt, $u) || str_contains($plain($new_excerpt), rtrim($plain($u), '….'))) {
            $new_excerpt = str_replace($u, '', $new_excerpt);
        }
        if (str_contains($new_listing, $u) || (str_contains($new_listing, 'to an…') && stripos($new_listing, 'Bipolar disorder is a mood') !== false)) {
            $new_listing = '';
        }
    }
    // If listing/excerpt itself is the unfinished bipolar definition, clear it.
    if (preg_match('/Bipolar disorder is a mood disorder.*to an[…\.]/iu', $new_listing)) {
        $new_listing = '';
    }
    if (preg_match('/Bipolar disorder is a mood disorder.*to an[…\.]/iu', $new_excerpt)) {
        $new_excerpt = 'The Bipolar Recovery Programme is an online group programme which offers support and education for people living with bipolar disorder.';
    }

    $dup = 'The Bipolar Recovery Programme is an online group programme which offers support and education for people living with bipolar disorder.';
    $next = $strip_leading_paragraph_matching($content, $dup);
    // Per Original feedback: remove the unfinished / bipolar-definition sentence.
    $next = preg_replace(
        '#(?:<p\b[^>]*>\s*)?Bipolar disorder is a mood disorder where a person experiences extreme changes in their mood, which can range from depressed mood to an[^<\r\n]*(?:</p>)?\s*#iu',
        '',
        $next,
        1
    ) ?? $next;

    $next = $rewrite_urls($next, $url_map_common);
    if ($next !== $content || $new_excerpt !== $excerpt || $new_listing !== $listing) {
        $save_programme($bipolar_id, 'bipolar cleanup', $next, $new_excerpt, $new_listing);
    } else {
        $log('SKIP bipolar: no change');
    }
}

// ---------------------------------------------------------------------------
// 6. CBT — space after full stop in blue container fields
// ---------------------------------------------------------------------------
$cbt_id = $programme_id('cognitive-behavioural-therapy');
if ($cbt_id > 0) {
    $content = (string) get_post_field('post_content', $cbt_id);
    $excerpt = (string) get_post_field('post_excerpt', $cbt_id);
    $listing = (string) get_field('listing_summary', $cbt_id);
    $next_c = $fix_missing_space_after_stop($content);
    $next_e = $fix_missing_space_after_stop($excerpt);
    $next_l = $fix_missing_space_after_stop($listing);
    // Explicit known join.
    $next_c = str_replace('difficulties.Here', 'difficulties. Here', $next_c);
    $next_e = str_replace('difficulties.Here', 'difficulties. Here', $next_e);
    $next_l = str_replace('difficulties.Here', 'difficulties. Here', $next_l);

    $dup = 'Cognitive Behavioural Therapy (CBT) is a type of talk therapy which can be a helpful treatment for a wide range of mental health difficulties. Here in St Patrick’s Mental Health Services (SPMHS), we offer a CBT Programme for service users in inpatient and day services.';
    $next_c2 = $strip_leading_paragraph_matching($next_c, 'Cognitive Behavioural Therapy (CBT) is a type of talk therapy which can be a helpful treatment for a wide range of mental health difficulties.');
    // If first block still duplicates full blue text, strip the combined first paragraph.
    if (str_starts_with($plain($next_c2), strtolower('Cognitive Behavioural Therapy (CBT) is a type of talk therapy'))) {
        $next_c2 = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*Cognitive Behavioural Therapy \(CBT\) is a type of talk therapy which can be a helpful treatment for a wide range of mental health difficulties\.\s*Here in St Patrick’s Mental Health Services \(SPMHS\), we offer a CBT Programme for service users in inpatient and day services\.\s*(?:</p>)?#u',
            '',
            $next_c2,
            1
        ) ?? $next_c2;
    }

    if ($next_c2 !== $content || $next_e !== $excerpt || $next_l !== $listing) {
        $save_programme($cbt_id, 'cbt spacing/dedupe', $next_c2, $next_e, $next_l);
    } else {
        $log('SKIP cbt: no change');
    }
}

// ---------------------------------------------------------------------------
// 7. CFT — MDT → staging our-team; remove unfinished blue + duplicate intro
// ---------------------------------------------------------------------------
$cft_id = $programme_id('compassion-focused-therapy');
if ($cft_id > 0) {
    $content = (string) get_post_field('post_content', $cft_id);
    $excerpt = (string) get_post_field('post_excerpt', $cft_id);
    $listing = (string) get_field('listing_summary', $cft_id);

    $blue = 'Compassion-Focused Therapy (CFT) is a type of therapy which aims to support people to develop kinder, more compassionate and soothing relationships with themselves.';
    // Blue container: keep first sentence only (drop unfinished second sentence).
    $new_listing = $blue;
    $new_excerpt = $blue;

    $next = $rewrite_urls($content, $url_map_common);
    // Remove duplicate first definition paragraph from body.
    $next = $strip_leading_paragraph_matching($next, $blue);
    if (str_starts_with($plain($next), strtolower($blue))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($blue, '#') . '\s*(?:</p>)?#u',
            '',
            $next,
            1
        ) ?? $next;
    }

    if ($next !== $content || $new_excerpt !== $excerpt || $new_listing !== $listing) {
        $save_programme($cft_id, 'cft mdt/dedupe', $next, $new_excerpt, $new_listing);
    } else {
        $log('SKIP cft: no change');
    }
}

// ---------------------------------------------------------------------------
// 8. CFT Adolescents — remove placeholder family/xxxx sentences
// ---------------------------------------------------------------------------
$adol_id = $programme_id('cft-for-adolescents-and-families');
if ($adol_id > 0) {
    $content = (string) get_post_field('post_content', $adol_id);
    $next = $remove_paragraphs_matching($content, [
        'Their family members are supported through xxxx',
        'Family members who can attend the programme with the adolescent include',
        'It runs for xxx sessions over xxx weeks',
    ]);
    // Also drop heading+placeholder pairs when the heading becomes empty of substance.
    $next = preg_replace(
        '#<h2>\s*Who would benefit from the programme\?\s*</h2>\s*(?:<p\b[^>]*>\s*Family members who can attend the programme with the adolescent include[…\.]*.*?</p>\s*)?#iu',
        '',
        $next
    ) ?? $next;
    $next = preg_replace(
        '#<h2>\s*How is the programme run\?\s*</h2>\s*<p\b[^>]*>\s*The programme takes place online\.\s*It runs for xxx sessions over xxx weeks\.\s*</p>#iu',
        '<h2>How is the programme run?</h2>',
        $next
    ) ?? $next;

    if ($next !== $content) {
        $save_programme($adol_id, 'cft-adolescents placeholders', $next);
    } else {
        $log('SKIP cft-adolescents: placeholders not found');
    }
}

// ---------------------------------------------------------------------------
// 9. CFT Eating Disorders — one brochure CTA; remove duplicate intro
// ---------------------------------------------------------------------------
$cfte_id = $programme_id('compassion-focused-therapy-eating-disorders');
if ($cfte_id > 0) {
    $dup = 'The Compassion Focused Therapy Group for Eating Disorders (CFT-E) offers a comprehensive psychological therapy for people who can manage recovery from their eating disorder in the community.';
    $content = (string) get_post_field('post_content', $cfte_id);
    $next = $strip_leading_paragraph_matching($content, $dup);
    if ($next === $content && str_starts_with($plain($content), strtolower($dup))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($dup, '#') . '\s*(?:</p>)?#u',
            '',
            $content,
            1
        ) ?? $content;
    }

    $brochure_label = 'Download our Compassion-Focused Therapy for Eating Disorders brochure';
    // Normalize all brochure anchors to one preferred label; keep first, drop later duplicates to same PDF.
    if (preg_match_all('#<a\b[^>]*href=(["\'])([^"\']*compassion-focused-therapy-for-eating-disorders-brochure\.pdf[^"\']*)\1[^>]*>(.*?)</a>#is', $next, $matches, PREG_SET_ORDER)) {
        $seen = false;
        foreach ($matches as $m) {
            $full = $m[0];
            if (! $seen) {
                $replacement = '<a href="' . esc_url($m[2]) . '">' . esc_html($brochure_label) . '</a>';
                $next = preg_replace('#' . preg_quote($full, '#') . '#', $replacement, $next, 1) ?? $next;
                $seen = true;
            } else {
                // Remove duplicate link and nearby wrapper paragraph if it only contains the link.
                $next = preg_replace('#<p\b[^>]*>\s*' . preg_quote($full, '#') . '\s*</p>#i', '', $next, 1) ?? $next;
                $next = str_replace($full, '', $next);
            }
        }
        $log('FIX cft-e: brochure CTA normalized');
    }

    $next = $rewrite_urls($next, $url_map_common);
    if ($next !== $content) {
        $save_programme($cfte_id, 'cft-e brochure/dedupe', $next);
    } else {
        $log('SKIP cft-e: no change');
    }
}

// ---------------------------------------------------------------------------
// 10. CFT Older Adults — remove duplicate blue + first paragraph copy
// ---------------------------------------------------------------------------
$oa_id = $programme_id('cft-for-older-adults');
if ($oa_id > 0) {
    $content = (string) get_post_field('post_content', $oa_id);
    $listing = (string) get_field('listing_summary', $oa_id);
    $excerpt = (string) get_post_field('post_excerpt', $oa_id);

    $p1 = 'The programme supports people to develop a more compassionate relationship with themselves and to build practical skills that can support their mental health and wellbeing.';
    $p2 = 'Delivered in a supportive group setting, CFT-OA encourages service users to better understand their';

    $new_listing = '';
    // Keep a non-invented excerpt if present (Learn about…).
    $new_excerpt = $excerpt;

    $next = $content;
    $next = $strip_leading_paragraph_matching($next, $p1);
    // Remove combined opening block.
    $next = preg_replace(
        '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($p1, '#') . '\s*Delivered in a supportive group setting, CFT-OA encourages service users to better understand their[^<]*(?:</p>)?#u',
        '',
        $next,
        1
    ) ?? $next;
    $next = $remove_paragraphs_matching($next, [$p2]);

    if ($next !== $content || $new_listing !== $listing) {
        $save_programme($oa_id, 'cft-oa dedupe', $next, null, $new_listing);
    } else {
        $log('SKIP cft-oa: no change');
    }
}

// ---------------------------------------------------------------------------
// 11. CFT Psychosis — fix 404 links; remove duplicate intro
// ---------------------------------------------------------------------------
$cftp_id = $programme_id('compassion-focused-therapy-for-psychosis-2');
if ($cftp_id <= 0) {
    $cftp_id = $programme_id('compassion-focused-therapy-for-psychosis');
}
if ($cftp_id > 0) {
    $dup = 'Compassion-Focused Therapy for Psychosis (CFT-P) is a group programme for people with experiences of psychosis.';
    $content = (string) get_post_field('post_content', $cftp_id);
    $next = $strip_leading_paragraph_matching($content, $dup);
    if ($next === $content && str_starts_with($plain($content), strtolower($dup))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($dup, '#') . '\s*(?:</p>)?#u',
            '',
            $content,
            1
        ) ?? $content;
    }

    $next = $rewrite_urls($next, $url_map_common);

    // Repair prior bad rewrites first, then set canonical targets.
    $next = preg_replace('#living-with-psychosis/+-?2/?#i', 'living-with-psychosis-2/', $next) ?? $next;
    $next = preg_replace(
        '#https?://[^"\'\s]*/living-with-psychosis-2/?#i',
        esc_url($psychosis2_url),
        $next
    ) ?? $next;
    $next = preg_replace(
        '#https?://[^"\'\s]*/living-with-psychosis(?!-2)/?#i',
        esc_url($psychosis_url),
        $next
    ) ?? $next;
    $next = preg_replace(
        '#(?<=["\'])/living-with-psychosis-2/?#i',
        wp_make_link_relative($psychosis2_url),
        $next
    ) ?? $next;
    $next = preg_replace(
        '#(?<=["\'])/living-with-psychosis(?!-2)/?#i',
        wp_make_link_relative($psychosis_url),
        $next
    ) ?? $next;

    $next = preg_replace(
        '#(<a\b[^>]*href=["\'])[^"\']*(?:voices-in-my-head[^"\']*|hearing-voices[^"\']*)(["\'][^>]*>\s*hearing voices\s*</a>)#i',
        '$1' . esc_url($voices_url) . '$2',
        $next
    ) ?? $next;

    if ($next !== $content) {
        $save_programme($cftp_id, 'cft-psychosis links/dedupe', $next);
    } else {
        $log('SKIP cft-psychosis: no change');
    }
    $log('NOTE cft-psychosis permalink=' . get_permalink($cftp_id));
} else {
    $log('MISS cft-psychosis');
}

echo implode("\n", $lines) . "\n";
