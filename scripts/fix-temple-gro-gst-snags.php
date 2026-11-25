<?php
/**
 * Close What We Offer To-dos: Temple Formulation Group, Group Radical Openness, Group Schema Therapy.
 *
 * Rows 33–35:
 * - Fix Psychology / Psychologists hyperlinks to a valid staging destination, else unlink.
 * - Temple: keep only the preferred leaflet CTA; remove extra leaflet links/blocks.
 * - GRO: keep one brochure; rename CTA to Download-our style title.
 * - Remove duplicate intros from body (keep in listing/excerpt).
 *
 * Browser (local/staging):
 *   /wp-content/themes/matrix-starter/scripts/fix-temple-gro-gst-snags.php?key=fix-temple-gro-gst
 * Dry run:
 *   ...?key=fix-temple-gro-gst&dry=1
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-temple-gro-gst') {
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

$psych_page = get_page_by_path('about-us/our-team/psychologists');
$psych_url = '';
$psych_id = 0;
$psych_note = '';
if ($psych_page instanceof WP_Post && $psych_page->post_status === 'publish') {
    $psych_id = (int) $psych_page->ID;
    $psych_url = trailingslashit((string) get_permalink($psych_page));
    $log('OK psych destination: ' . $psych_url . ' #' . $psych_id);
} else {
    $psych_note = 'Needs Review: no valid Psychology/Psychologists staging page found; unlinked.';
    $log('WARN ' . $psych_note);
}

$fix_psych_links = static function (string $html, string $label) use ($psych_url, $psych_id, $log): string {
    $next = preg_replace_callback(
        '#<a\b([^>]*)>(.*?)</a>#is',
        static function (array $m) use ($psych_url, $psych_id, $log, $label): string {
            $attrs = $m[1];
            $inner = $m[2];
            $text = trim(wp_strip_all_tags($inner));
            $is_psych_text = (bool) preg_match(
                '#^(Psychology Department|Department of Psychology|Psychologists|psychologists)$#iu',
                $text
            );
            $href = '';
            if (preg_match('#href=(["\'])([^"\']*)\1#i', $attrs, $hm)) {
                $href = $hm[2];
            }
            $is_psych_href = (bool) preg_match(
                '#(?:/about-us/our-team/psychologists|/about-us/staff-directory/clinical-psychology|/care-treatment/.*?psycholog|/staff-directory/.*?psycholog)#i',
                $href
            );
            if (! $is_psych_text && ! $is_psych_href) {
                return $m[0];
            }

            if ($psych_url === '') {
                $log("FIX {$label}: unlinked psych anchor [{$text}] (no valid destination)");

                return $inner;
            }

            $new_attrs = preg_replace('#href=(["\'])([^"\']*)\1#i', 'href="' . esc_url($psych_url) . '"', $attrs, 1) ?? $attrs;
            if ($psych_id > 0) {
                if (preg_match('#\bdata-id=#i', $new_attrs)) {
                    $new_attrs = preg_replace('#\bdata-id=(["\'])[^"\']*\1#i', 'data-id="' . $psych_id . '"', $new_attrs, 1) ?? $new_attrs;
                } else {
                    $new_attrs .= ' data-id="' . $psych_id . '"';
                }
            }
            $href_ok = untrailingslashit($href) === untrailingslashit($psych_url);
            $id_ok = $psych_id <= 0 || (bool) preg_match('#\bdata-id=(["\'])' . preg_quote((string) $psych_id, '#') . '\1#', $new_attrs);
            if (! $href_ok || ! $id_ok) {
                $log("FIX {$label}: retargeted psych anchor [{$text}] → {$psych_url} data-id={$psych_id}");
            } else {
                $log("OK {$label}: psych anchor already valid [{$text}]");
            }

            return '<a' . $new_attrs . '>' . $inner . '</a>';
        },
        $html
    );

    return is_string($next) ? $next : $html;
};

$strip_duplicate_intro = static function (string $html, string $dup, string $label) use ($plain, $log): string {
    $needle = strtolower($dup);
    if (trim($html) === '') {
        return $html;
    }

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
        $log("FIX {$label}: removed duplicate intro span");
        $html = ltrim($next);
    }

    $next = preg_replace_callback(
        '#(?:<p\b[^>]*class=["\'][^"\']*\bintro\b[^"\']*["\'][^>]*>\s*.*?\s*</p>\s*)#is',
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
        $log("FIX {$label}: removed duplicate intro paragraph");
        $html = ltrim($next);
    }

    // Bare intro span without wrapping p (Temple migrated shape).
    $next = preg_replace_callback(
        '#\A\s*<span\b[^>]*class=["\'][^"\']*\bintro\b[^"\']*["\'][^>]*>\s*.*?\s*</span>\s*#is',
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
        $log("FIX {$label}: removed leading duplicate intro span");
        $html = ltrim($next);
    }

    return $html;
};

$dedupe_temple_leaflets = static function (string $html, string $label) use ($log): string {
    $preferred_re = '#Read the Temple Formulation Group leaflet here#i';
    $leaflet_file_re = '#temple-formulation[^"\']*\.pdf#i';

    // Remove whole blocks that are extra leaflet CTAs (h2 + link, or bare duplicate anchors).
    if (preg_match_all('#(?:<h2[^>]*>\s*[^<]*Temple Formulation[^<]*</h2>\s*)?(?:<p[^>]*>\s*)?<a\b[^>]*href=(["\'])([^"\']*temple-formulation[^"\']*\.pdf[^"\']*)\1[^>]*>.*?</a>\s*(?:</p>\s*)?#is', $html, $blocks, PREG_SET_ORDER)) {
        $keep_idx = null;
        foreach ($blocks as $i => $b) {
            if (preg_match($preferred_re, $b[0])) {
                $keep_idx = $i;
                break;
            }
        }
        if ($keep_idx === null && $blocks !== []) {
            $keep_idx = 0;
        }
        if ($keep_idx !== null && count($blocks) > 1) {
            $keep = $blocks[$keep_idx][0];
            // Ensure preferred link text if we kept a non-preferred first.
            if (! preg_match($preferred_re, $keep) && preg_match('#href=(["\'])([^"\']+)\1#i', $keep, $hm)) {
                $keep = '<a href="' . esc_url($hm[2]) . '" target="_blank" rel="noopener">Read the Temple Formulation Group leaflet here</a> for more information.';
            }
            $first = true;
            $html = preg_replace_callback(
                '#(?:<h2[^>]*>\s*[^<]*Temple Formulation[^<]*</h2>\s*)?(?:<p[^>]*>\s*)?<a\b[^>]*href=(["\'])([^"\']*temple-formulation[^"\']*\.pdf[^"\']*)\1[^>]*>.*?</a>\s*(?:for more information\.?\s*)?(?:</p>\s*)?#is',
                static function (array $m) use (&$first, $keep): string {
                    if ($first) {
                        $first = false;

                        return $keep;
                    }

                    return '';
                },
                $html
            ) ?? $html;
            $log('FIX ' . $label . ': removed duplicate Temple leaflet CTA(s); kept preferred');
        }
    }

    // Also strip any remaining extra PDF anchors that are not the preferred CTA text.
    if (preg_match_all('#<a\b[^>]*href=(["\'])([^"\']*temple-formulation[^"\']*\.pdf[^"\']*)\1[^>]*>(.*?)</a>#is', $html, $anchors, PREG_SET_ORDER)) {
        $seen_preferred = false;
        $changed = false;
        foreach ($anchors as $a) {
            $is_pref = (bool) preg_match($preferred_re, $a[3]);
            if ($is_pref && ! $seen_preferred) {
                $seen_preferred = true;
                continue;
            }
            // Drop non-preferred or subsequent preferred duplicates (anchor only).
            $html = preg_replace('#' . preg_quote($a[0], '#') . '#', '', $html, 1) ?? $html;
            $changed = true;
        }
        if ($changed) {
            $log('FIX ' . $label . ': stripped extra Temple leaflet anchors');
        }
    }

    // Clean empty leftover headings/paragraphs about leaflets.
    $html = preg_replace('#<h2[^>]*>\s*(?:Temple Formulation Group\s+)?leaflet\s*</h2>\s*#iu', '', $html) ?? $html;
    $html = preg_replace('#<p[^>]*>\s*</p>\s*#i', '', $html) ?? $html;

    return $html;
};

$gro_brochure_label = 'Download our Group Radical Openness programme brochure';

$dedupe_gro_brochures = static function (string $html, string $label) use ($gro_brochure_label, $log): string {
    $file_re = '#group-radical-openness[^"\']*(?:leaflet|brochure)[^"\']*\.pdf#i';
    if (! preg_match_all('#(?:<h2[^>]*>\s*[^<]*Group Radical Openness[^<]*(?:brochure|leaflet)[^<]*</h2>\s*)?(?:<p[^>]*>\s*)?<a\b[^>]*href=(["\'])([^"\']*group-radical-openness[^"\']*\.pdf[^"\']*)\1[^>]*>.*?</a>\s*(?:</p>\s*)?#is', $html, $blocks, PREG_SET_ORDER)) {
        // Fall back: any GRO pdf anchors.
        if (! preg_match_all('#<a\b[^>]*href=(["\'])([^"\']*group-radical-openness[^"\']*\.pdf[^"\']*)\1[^>]*>(.*?)</a>#is', $html, $anchors, PREG_SET_ORDER)) {
            return $html;
        }
        $keep_href = $anchors[0][2];
        $replacement = '<a href="' . esc_url($keep_href) . '" target="_blank" rel="noopener">' . esc_html($gro_brochure_label) . '</a>';
        $first = true;
        $html = preg_replace_callback(
            '#<a\b[^>]*href=(["\'])([^"\']*group-radical-openness[^"\']*\.pdf[^"\']*)\1[^>]*>.*?</a>#is',
            static function (array $m) use (&$first, $replacement): string {
                if ($first) {
                    $first = false;

                    return $replacement;
                }

                return '';
            },
            $html
        ) ?? $html;
        $log("FIX {$label}: normalized GRO brochure CTA(s)");

        return $html;
    }

    $keep_href = $blocks[0][2];
    $replacement = '<h2>Group Radical Openness brochure</h2>' . "\n"
        . '<p><a href="' . esc_url($keep_href) . '" target="_blank" rel="noopener">' . esc_html($gro_brochure_label) . '</a></p>';
    $first = true;
    $html = preg_replace_callback(
        '#(?:<h2[^>]*>\s*[^<]*Group Radical Openness[^<]*(?:brochure|leaflet)[^<]*</h2>\s*)?(?:<p[^>]*>\s*)?<a\b[^>]*href=(["\'])([^"\']*group-radical-openness[^"\']*\.pdf[^"\']*)\1[^>]*>.*?</a>\s*(?:</p>\s*)?#is',
        static function (array $m) use (&$first, $replacement): string {
            if ($first) {
                $first = false;

                return $replacement;
            }

            return '';
        },
        $html
    ) ?? $html;

    // Also rename any remaining bare GRO pdf anchors (should be one).
    $html = preg_replace_callback(
        '#<a\b([^>]*href=(["\'])([^"\']*group-radical-openness[^"\']*\.pdf[^"\']*)\2[^>]*)>(.*?)</a>#is',
        static function (array $m) use ($gro_brochure_label): string {
            return '<a' . $m[1] . '>' . esc_html($gro_brochure_label) . '</a>';
        },
        $html
    ) ?? $html;

    $log("FIX {$label}: kept one GRO brochure; renamed CTA to Download-our style");

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
        foreach (['content', 'text_content', 'intro_text', 'body', 'hero_intro', 'summary'] as $f) {
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
        $log('OK updated flexible_content_blocks #' . $id);
    } elseif ($changed) {
        $log('DRY would update flexible_content_blocks #' . $id);
    }
};

$save_programme = static function (int $id, string $label, string $content, string $listing, string $excerpt) use ($dry, $log): void {
    $cur = (string) get_post_field('post_content', $id);
    $cur_ex = (string) get_post_field('post_excerpt', $id);
    $cur_ls = function_exists('get_field') ? (string) get_field('listing_summary', $id) : '';
    if ($content === $cur && $listing === $cur_ls && $excerpt === $cur_ex) {
        $log("SKIP {$label}: no content/listing/excerpt change");

        return;
    }
    if ($dry) {
        $log("DRY {$label} #{$id} would save content_changed=" . ($content !== $cur ? '1' : '0'));

        return;
    }
    wp_update_post([
        'ID' => $id,
        'post_content' => $content,
        'post_excerpt' => $excerpt,
    ]);
    if (function_exists('update_field')) {
        update_field('listing_summary', $listing, $id);
    }
    clean_post_cache($id);
    $log("OK {$label} #{$id} saved");
};

// --- Temple Formulation Group ---
$temple_dup = 'The Temple Formulation Group is a group programme which aims to support people experiencing mental health difficulties.';
$temple_id = $programme_id('temple-formulation-group');
if ($temple_id > 0) {
    $content = (string) get_post_field('post_content', $temple_id);
    $listing = function_exists('get_field') ? (string) get_field('listing_summary', $temple_id) : '';
    $excerpt = (string) get_post_field('post_excerpt', $temple_id);

    $next = $content;
    $next = $fix_psych_links($next, 'temple');
    $next = $dedupe_temple_leaflets($next, 'temple');
    $next = $strip_duplicate_intro($next, $temple_dup, 'temple');

    $transform = static function (string $html) use ($fix_psych_links, $dedupe_temple_leaflets, $strip_duplicate_intro, $temple_dup): string {
        $html = $fix_psych_links($html, 'temple-flexi');
        $html = $dedupe_temple_leaflets($html, 'temple-flexi');
        $html = $strip_duplicate_intro($html, $temple_dup, 'temple-flexi');

        return $html;
    };
    $apply_to_flexi($temple_id, $transform);

    if ($plain($listing) === '' && $plain($excerpt) === '') {
        $listing = $temple_dup;
        $excerpt = $temple_dup;
        $log('FIX temple: restored listing/excerpt from canonical intro');
    } elseif ($plain($listing) === '' && $plain($excerpt) !== '') {
        $listing = $plain($excerpt);
    } elseif ($plain($excerpt) === '' && $plain($listing) !== '') {
        $excerpt = $plain($listing);
    }

    $save_programme($temple_id, 'temple', $next, $listing, $excerpt);
    $after = (string) get_post_field('post_content', $temple_id);
    preg_match_all('#temple-formulation[^"\']*\.pdf#i', $after, $fm);
    $log('EVIDENCE temple leaflet pdf count=' . count($fm[0]));
    $log('EVIDENCE temple preferred CTA=' . (preg_match('#Read the Temple Formulation Group leaflet here#i', $after) ? 'YES' : 'NO'));
    $log('EVIDENCE temple body_has_dup=' . (str_contains(strtolower($plain($after)), strtolower($temple_dup)) ? 'YES' : 'NO'));
} else {
    $log('MISS temple programme');
}

// --- Group Radical Openness ---
$gro_dup = 'Group Radical Openness (GRO) is a group therapy programme for people who have difficulties with costly overcontrol. This includes keeping others at a distance, having trouble showing emotions and a strong desire for control.';
$gro_id = $programme_id('group-radical-openness');
if ($gro_id > 0) {
    $content = (string) get_post_field('post_content', $gro_id);
    $listing = function_exists('get_field') ? (string) get_field('listing_summary', $gro_id) : '';
    $excerpt = (string) get_post_field('post_excerpt', $gro_id);

    $next = $content;
    $next = $fix_psych_links($next, 'gro');
    $next = $dedupe_gro_brochures($next, 'gro');
    $next = $strip_duplicate_intro($next, $gro_dup, 'gro');

    $transform = static function (string $html) use ($fix_psych_links, $dedupe_gro_brochures, $strip_duplicate_intro, $gro_dup): string {
        $html = $fix_psych_links($html, 'gro-flexi');
        $html = $dedupe_gro_brochures($html, 'gro-flexi');
        $html = $strip_duplicate_intro($html, $gro_dup, 'gro-flexi');

        return $html;
    };
    $apply_to_flexi($gro_id, $transform);

    if ($plain($listing) === '' && $plain($excerpt) === '') {
        $listing = $gro_dup;
        $excerpt = $gro_dup;
        $log('FIX gro: restored listing/excerpt from canonical intro');
    } elseif ($plain($listing) === '' && $plain($excerpt) !== '') {
        $listing = $plain($excerpt);
    } elseif ($plain($excerpt) === '' && $plain($listing) !== '') {
        $excerpt = $plain($listing);
    }

    $save_programme($gro_id, 'gro', $next, $listing, $excerpt);
    $after = (string) get_post_field('post_content', $gro_id);
    preg_match_all('#group-radical-openness[^"\']*\.pdf#i', $after, $fm);
    $log('EVIDENCE gro brochure pdf count=' . count($fm[0]));
    $log('EVIDENCE gro download_our_cta=' . (str_contains($after, $gro_brochure_label) ? 'YES' : 'NO'));
    $log('EVIDENCE gro body_has_dup=' . (str_contains(strtolower($plain($after)), strtolower($gro_dup)) ? 'YES' : 'NO'));
    $log('EVIDENCE gro bad_filename_cta=' . (preg_match('#programme brochure\.pdf\s*\(#i', $after) ? 'YES' : 'NO'));
} else {
    $log('MISS gro programme');
}

// --- Group Schema Therapy ---
$gst_dup = 'Group Schema Therapy (GST) is a group therapy programme for people with experiences of childhood or attachment trauma.';
$gst_id = $programme_id('group-schema-therapy');
if ($gst_id > 0) {
    $content = (string) get_post_field('post_content', $gst_id);
    $listing = function_exists('get_field') ? (string) get_field('listing_summary', $gst_id) : '';
    $excerpt = (string) get_post_field('post_excerpt', $gst_id);

    $next = $content;
    $next = $fix_psych_links($next, 'gst');
    $next = $strip_duplicate_intro($next, $gst_dup, 'gst');

    $transform = static function (string $html) use ($fix_psych_links, $strip_duplicate_intro, $gst_dup): string {
        $html = $fix_psych_links($html, 'gst-flexi');
        $html = $strip_duplicate_intro($html, $gst_dup, 'gst-flexi');

        return $html;
    };
    $apply_to_flexi($gst_id, $transform);

    if ($plain($listing) === '' && $plain($excerpt) === '') {
        $listing = $gst_dup;
        $excerpt = $gst_dup;
        $log('FIX gst: restored listing/excerpt from canonical intro');
    } elseif ($plain($listing) === '' && $plain($excerpt) !== '') {
        $listing = $plain($excerpt);
    } elseif ($plain($excerpt) === '' && $plain($listing) !== '') {
        $excerpt = $plain($listing);
    }

    $save_programme($gst_id, 'gst', $next, $listing, $excerpt);
    $after = (string) get_post_field('post_content', $gst_id);
    $log('EVIDENCE gst body_has_dup=' . (str_contains(strtolower($plain($after)), strtolower($gst_dup)) ? 'YES' : 'NO'));
    $log('EVIDENCE gst psych_href_ok=' . (preg_match('#href=["\'][^"\']*/about-us/our-team/psychologists/?["\']#i', $after) || $psych_url === '' ? ($psych_url === '' ? 'UNLINKED' : 'YES') : 'CHECK'));
} else {
    $log('MISS gst programme');
}

if ($psych_note !== '') {
    $log('NOTE ' . $psych_note);
}

echo implode("\n", $lines) . "\nDONE\n";
