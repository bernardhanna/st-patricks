<?php
/**
 * QC seed 26 Nov: What We Offer + Service Users + News To-do snags.
 *
 * WP-CLI:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/qc-seed-batch-nov26-wwo.php
 *
 * Browser (local/staging only):
 *   /wp-content/themes/matrix-starter/scripts/qc-seed-batch-nov26-wwo.php?key=qc-batch-nov26-wwo
 * Dry run:
 *   ...?key=qc-batch-nov26-wwo&dry=1
 *
 * Pair with path-redirect-map change that unshadows closure-of-fax-referrals.
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);
    if (! $allowed || ($_GET['key'] ?? '') !== 'qc-batch-nov26-wwo') {
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

    if (preg_match('#\A((?:<div\b[^>]*>\s*</div>\s*|<h[1-6]\b[^>]*>\s*</h[1-6]>\s*)*)([^<\r\n][^\r\n]*(?:\r?\n(?!\r?\n)[^\r\n]*)*)(\r?\n\r?\n)?#u', $html, $m)) {
        $p = strtolower($plain($m[2]));
        if ($p === $needle || str_starts_with($p, $needle)) {
            return ltrim($m[1] . substr($html, strlen($m[0])));
        }
    }

    return $html;
};

$fix_missing_space_after_stop = static function (string $html): string {
    return preg_replace('/\.(?=[A-ZÀ-ÖØ-Þ])/u', '. ', $html) ?? $html;
};

$dedupe_pdf_links = static function (string $html, string $href_needle, string $preferred_label) use ($log): string {
    if (! preg_match_all('#<a\b[^>]*href=(["\'])([^"\']*' . preg_quote($href_needle, '#') . '[^"\']*)\1[^>]*>(.*?)</a>#is', $html, $matches, PREG_SET_ORDER)) {
        return $html;
    }

    $seen = false;
    foreach ($matches as $m) {
        $full = $m[0];
        if (! $seen) {
            $replacement = '<a href="' . esc_url($m[2]) . '"'
                . (str_contains(strtolower($m[0]), 'target=') ? ' target="_blank" rel="noopener noreferrer"' : '')
                . '>' . esc_html($preferred_label) . '</a>';
            $html = preg_replace('#' . preg_quote($full, '#') . '#', $replacement, $html, 1) ?? $html;
            $seen = true;
        } else {
            $html = preg_replace('#<p\b[^>]*>\s*' . preg_quote($full, '#') . '\s*</p>#i', '', $html, 1) ?? $html;
            $html = str_replace($full, '', $html);
            $log('FIX removed duplicate PDF link matching ' . $href_needle);
        }
    }

    return $html;
};

$fix_pdf_hrefs = static function (string $html, array $map): string {
    foreach ($map as $label_regex => $pdf_basename) {
        $html = preg_replace_callback(
            '#<a\b([^>]*)>(\s*' . $label_regex . '\s*)</a>#iu',
            static function (array $m) use ($pdf_basename): string {
                $attrs = $m[1];
                $inner = $m[2];
                $href = '';
                if (preg_match('#href=(["\'])([^"\']*)\1#i', $attrs, $hm)) {
                    $href = $hm[2];
                }
                if ($href !== '' && preg_match('~\.pdf(?:$|[?\#])~i', $href)) {
                    return $m[0];
                }

                // Prefer an existing media attachment matching the basename.
                $url = $href;
                $attachments = get_posts([
                    'post_type' => 'attachment',
                    'post_status' => 'inherit',
                    'posts_per_page' => 5,
                    's' => pathinfo($pdf_basename, PATHINFO_FILENAME),
                ]);
                foreach ($attachments as $att) {
                    $att_url = (string) wp_get_attachment_url($att->ID);
                    if ($att_url !== '' && str_ends_with(strtolower(parse_url($att_url, PHP_URL_PATH) ?: ''), strtolower($pdf_basename))) {
                        $url = $att_url;
                        break;
                    }
                }
                if ($url === $href || $url === '') {
                    return $m[0];
                }
                $attrs = preg_replace('#href=(["\'])([^"\']*)\1#i', 'href="' . esc_url($url) . '"', $attrs, 1) ?? $attrs;

                return '<a' . $attrs . '>' . $inner . '</a>';
            },
            $html
        ) ?? $html;
    }

    return $html;
};

$ensure_section = static function (string $html, string $heading, string $body_html) use ($plain): string {
    if (preg_match('#<h2[^>]*>\s*' . preg_quote($heading, '#') . '\s*</h2>#iu', $html)) {
        // Replace empty/short body under heading when present.
        $html = preg_replace(
            '#(<h2[^>]*>\s*' . preg_quote($heading, '#') . '\s*</h2>\s*)(?:<p\b[^>]*>\s*</p>\s*)?#iu',
            '$1' . $body_html,
            $html,
            1,
            $count
        ) ?? $html;
        if ($count > 0) {
            // If heading already had a substantial paragraph, leave first match only when body was empty.
            // Revert if we duplicated: detect two consecutive identical bodies.
            return $html;
        }

        return $html;
    }

    return rtrim($html) . "\n<h2>" . esc_html($heading) . "</h2>\n" . $body_html;
};

$replace_section_body = static function (string $html, string $heading, string $body_html): string {
    $pattern = '#(<h2[^>]*>\s*' . preg_quote($heading, '#') . '\s*</h2>\s*)(.*?)(?=<h2\b|\z)#is';
    if (preg_match($pattern, $html)) {
        return preg_replace($pattern, '$1' . $body_html . "\n", $html, 1) ?? $html;
    }

    return rtrim($html) . "\n<h2>" . esc_html($heading) . "</h2>\n" . $body_html . "\n";
};

$save_programme = static function (int $id, string $label, ?string $content = null, ?string $excerpt = null, ?string $listing = null) use ($dry, $log): void {
    if ($dry) {
        $log('DRY ' . $label . ' #' . $id);

        return;
    }

    global $wpdb;
    $update = [];
    $formats = [];
    if ($content !== null) {
        $update['post_content'] = $content;
        $formats[] = '%s';
    }
    if ($excerpt !== null) {
        $update['post_excerpt'] = $excerpt;
        $formats[] = '%s';
    }
    if ($update !== []) {
        $update['post_modified'] = current_time('mysql');
        $update['post_modified_gmt'] = current_time('mysql', true);
        $formats[] = '%s';
        $formats[] = '%s';
        $wpdb->update($wpdb->posts, $update, ['ID' => $id], $formats, ['%d']);
    }
    if ($listing !== null && function_exists('update_field')) {
        update_field('listing_summary', $listing, $id);
    }
    clean_post_cache($id);
    $log('OK ' . $label . ' #' . $id);
};

$save_post = static function (int $id, string $label, string $content) use ($dry, $log): void {
    if ($dry) {
        $log('DRY ' . $label . ' #' . $id);

        return;
    }
    global $wpdb;
    $wpdb->update(
        $wpdb->posts,
        [
            'post_content' => $content,
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', true),
        ],
        ['ID' => $id],
        ['%s', '%s', '%s'],
        ['%d']
    );
    clean_post_cache($id);
    $log('OK ' . $label . ' #' . $id);
};

$mdt_url = trailingslashit(home_url('/about-us/our-team'));
$psychiatrists_url = trailingslashit(home_url('/about-us/our-team/psychiatrists'));
$history_url = trailingslashit(home_url('/about-us/our-history'));
$depression_url = trailingslashit(home_url('/mental-health/depression'));
$anxiety_url = trailingslashit(home_url('/mental-health/anxiety'));
$strategy_url = 'https://www.stpatricks.ie/about-us/strategy';

$log('=== qc-seed-batch-nov26-wwo dry=' . ($dry ? '1' : '0') . ' ===');

// ---------------------------------------------------------------------------
// CBT verify (previously fixed) — space after full stop in blue fields
// ---------------------------------------------------------------------------
$cbt_id = $programme_id('cognitive-behavioural-therapy');
if ($cbt_id > 0) {
    $listing = (string) get_field('listing_summary', $cbt_id);
    $excerpt = (string) get_post_field('post_excerpt', $cbt_id);
    if (str_contains($listing, 'difficulties.Here') || str_contains($excerpt, 'difficulties.Here')) {
        $listing = str_replace('difficulties.Here', 'difficulties. Here', $listing);
        $excerpt = str_replace('difficulties.Here', 'difficulties. Here', $excerpt);
        $save_programme($cbt_id, 'cbt spacing re-fix', null, $excerpt, $listing);
    } else {
        $log('OK cbt spacing already present #' . $cbt_id);
    }
}

// ---------------------------------------------------------------------------
// 1. Depression Recovery — remove mood disorder from blue; strip blue dup from body
// ---------------------------------------------------------------------------
$dep_id = $programme_id('depression-recovery-programme');
if ($dep_id > 0) {
    $blue = 'The Depression Recovery Programme offers support and education for people living with depression.';
    $content = (string) get_post_field('post_content', $dep_id);
    $next = $strip_leading_paragraph_matching($content, $blue);
    // Also strip a leading mood-disorder paragraph when it was only meant for the unfinished blue snippet.
    // Keep educational mood copy in body if it is the second paragraph after programme intro was removed.
    $next = $fix_missing_space_after_stop($next);
    $listing = $blue;
    $excerpt = $blue;
    if ($next !== $content || $listing !== (string) get_field('listing_summary', $dep_id)) {
        $save_programme($dep_id, 'depression blue/mood cleanup', $next, $excerpt, $listing);
    } else {
        $log('SKIP depression: no change');
    }
}

// ---------------------------------------------------------------------------
// 2. DBT — remove dup under blue; booklet CTA label
// ---------------------------------------------------------------------------
$dbt_id = $programme_id('dialectical-behavioural-therapy');
if ($dbt_id > 0) {
    $dup = 'The DBT Programme works to help you manage difficult emotions and improve your wellbeing.';
    $content = (string) get_post_field('post_content', $dbt_id);
    $next = $strip_leading_paragraph_matching($content, $dup);
    if ($next === $content && str_starts_with($plain($content), strtolower($dup))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*(?:<span\b[^>]*class=["\'][^"\']*\bintro\b[^"\']*["\'][^>]*>)?\s*' . preg_quote($dup, '#') . '\s*(?:</span>)?\s*(?:</p>)?#u',
            '',
            $content,
            1
        ) ?? $content;
    }
    $next = $dedupe_pdf_links($next, 'dialectical-behavioural-therapy-booklet', 'Download our Dialectical Behaviour Therapy Booklet here');
    $next = $dedupe_pdf_links($next, 'dialectical-behaviour', 'Download our Dialectical Behaviour Therapy Booklet here');
    if ($next !== $content) {
        $save_programme($dbt_id, 'dbt dedupe/booklet', $next);
    } else {
        $log('SKIP dbt: no change');
    }
}

// ---------------------------------------------------------------------------
// 3. Eating Disorder Day Care — remove intro dup; anorexia/bulimia → PDFs
// ---------------------------------------------------------------------------
$ed_id = $programme_id('eating-disorder-programme');
if ($ed_id > 0) {
    $dup = 'The Eating Disorder Day Care Programme offers treatment and support towards recovery from an eating disorder for people experiencing an eating disorder and their carers.';
    $content = (string) get_post_field('post_content', $ed_id);
    $next = $strip_leading_paragraph_matching($content, $dup);
    if ($next === $content && str_starts_with($plain($content), strtolower($dup))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($dup, '#') . '\s*(?:</p>)?#u',
            '',
            $content,
            1
        ) ?? $content;
    }
    $next = $fix_pdf_hrefs($next, [
        'anorexia nervosa' => 'spmhs_anorexia_nervosa_booklet-2016_0.pdf',
        'bulimia nervosa' => 'spmhs_bulimia_nervosa_booklet-2016.pdf',
    ]);
    if ($next !== $content) {
        $save_programme($ed_id, 'ed day-care dedupe/pdfs', $next);
    } else {
        $log('SKIP ed-day-care: no change');
    }
}

// ---------------------------------------------------------------------------
// 4. Living Well with MCI — remove MCI difficulties dup under blue; ensure sections
// ---------------------------------------------------------------------------
$mci_id = $programme_id('living-well-with-mild-cognitive-impairment');
if ($mci_id > 0) {
    $dup = 'People with MCI may notice difficulties with areas of cognition such as memory, attention, language, problem-solving, planning or organisation.';
    $content = (string) get_post_field('post_content', $mci_id);
    $next = $strip_leading_paragraph_matching($content, $dup);
    // Strip combined first paragraph if it still starts with MCI difficulties.
    if (str_starts_with($plain($next), strtolower($dup))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($dup, '#') . '.*?(?:</p>)?#us',
            '',
            $next,
            1
        ) ?? $next;
    }
    $next = $replace_section_body(
        $next,
        'Who delivers the programme?',
        '<p>The programme is run by two psychologists and one assistant psychologist.</p>'
    );
    $next = $replace_section_body(
        $next,
        'Who is the programme suitable for?',
        '<p>The programme is designed for people with Mild Cognitive Impairment (MCI) who would benefit from support in managing the emotional, cognitive and practical aspects of their diagnosis.</p>'
    );
    if ($next !== $content) {
        $save_programme($mci_id, 'mci dedupe/sections', $next);
    } else {
        $log('SKIP mci: no change');
    }
}

// ---------------------------------------------------------------------------
// 5. Pathways to Wellness — remove aims dup under blue
// ---------------------------------------------------------------------------
$path_id = $programme_id('pathways-to-wellness-2');
if ($path_id > 0) {
    $dup = 'The programme aims to offer opportunities to engage in activities which will encourage participation and re-engagement in community and society.';
    $content = (string) get_post_field('post_content', $path_id);
    $next = $strip_leading_paragraph_matching($content, $dup);
    if ($next === $content && str_starts_with($plain($content), strtolower($dup))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($dup, '#') . '\s*(?:</p>)?#u',
            '',
            $content,
            1
        ) ?? $content;
    }
    if ($next !== $content) {
        $save_programme($path_id, 'pathways remove aims dup', $next);
    } else {
        $log('SKIP pathways: no change');
    }
}

// ---------------------------------------------------------------------------
// 6. Psychology Skills / SABE — remove neurodiversity from blue; remove Skills dup; fix Who suitable
// ---------------------------------------------------------------------------
$sabe_id = $programme_id('psychology-skills-group-for-adolescents');
if ($sabe_id > 0) {
    $listing_blue = 'The Skills for Attention, Behaviour and Emotions (SABE) for Adolescents and Families Programme is an online group for neurodiverse young people aged 12 to 17 and their families.';
    $content = (string) get_post_field('post_content', $sabe_id);
    $next = $content;

    // Remove leading Skills/neurodiversity definition blocks that duplicate the blue container.
    $next = preg_replace(
        '#\A\s*(?:<p\b[^>]*>)?\s*The Skills for Attention, Behaviour and Emotions \(SABE\) for Adolescents and Families Programme is designed for young people with neurodiversity\.?\s*(?:</p>)?\s*#u',
        '',
        $next,
        1
    ) ?? $next;
    $next = preg_replace(
        '#\A\s*(?:<p\b[^>]*>)?\s*Neurodiversity means that people’s brains work in different, unique ways which affect how we think, process information, and(?:…|\.\.\.| communicate\.?)?\s*(?:</p>)?\s*#u',
        '',
        $next,
        1
    ) ?? $next;
    // Also bare-text variants without <p>.
    $next = preg_replace(
        '#\A\s*The Skills for Attention, Behaviour and Emotions \(SABE\) for Adolescents and Families Programme is designed for young people with neurodiversity\.?\s*#u',
        '',
        $next,
        1
    ) ?? $next;
    $next = preg_replace(
        '#\A\s*Neurodiversity means that people’s brains work in different, unique ways which affect how we think, process information, and(?:…|\.\.\.| communicate\.?)?\s*#u',
        '',
        $next,
        1
    ) ?? $next;

    // Drop unfinished placeholder paragraphs if still present.
    foreach ([
        'It focuses on building skills, such as xxxx',
        'It involves families by…',
        'It involves families by...',
        'There are xx s',
    ] as $bad) {
        $next = preg_replace(
            '#(?:<p\b[^>]*>\s*)?' . preg_quote($bad, '#') . '[^<]*(?:</p>)?\s*#iu',
            '',
            $next
        ) ?? $next;
    }

    // Fix mistaken psychosis copy under Who suitable.
    $sabe_suitable = '<p>The programme is suitable for neurodiverse young people aged 12 to 17, and their families.</p>';
    $next = $replace_section_body($next, 'Who is the programme suitable for?', $sabe_suitable);

    $listing = $listing_blue;
    $excerpt = $listing_blue;
    if ($next !== $content || $listing !== (string) get_field('listing_summary', $sabe_id)) {
        $save_programme($sabe_id, 'sabe neurodiversity/dedupe/suitable', $next, $excerpt, $listing);
    } else {
        $log('SKIP sabe: no change');
    }
}

// ---------------------------------------------------------------------------
// 7. Psychosis Recovery — ensure Who suitable supplied copy
// ---------------------------------------------------------------------------
$psy_id = $programme_id('psychosis-recovery-programme');
if ($psy_id > 0) {
    $content = (string) get_post_field('post_content', $psy_id);
    $suitable = '<p>The programme is suitable for both inpatient and day patient service users who are experiencing psychosis and would benefit from structured education, therapeutic input and recovery-focused support.</p>';
    $next = $replace_section_body($content, 'Who is the programme suitable for?', $suitable);
    // Also remove a duplicate header block near the end if present (Additional notes).
    $next = preg_replace(
        '#(?:<h1\b[^>]*>\s*Psychosis Recovery Programme\s*</h1>\s*){2,}#iu',
        '<h1>Psychosis Recovery Programme</h1>',
        $next
    ) ?? $next;
    if ($next !== $content) {
        $save_programme($psy_id, 'psychosis who-suitable', $next);
    } else {
        $log('SKIP psychosis: no change');
    }
}

// ---------------------------------------------------------------------------
// 8. Recovery WRAP — remove empower dup; fix consultant/MDT; dedupe leaflet; brochure label
// ---------------------------------------------------------------------------
$wrap_id = $programme_id('recovery-wrap-programme');
if ($wrap_id > 0) {
    $dup = 'The Recovery Programme aims to empower service users to monitor their mental health and to address factors that may block or support their recovery.';
    $content = (string) get_post_field('post_content', $wrap_id);
    $next = $strip_leading_paragraph_matching($content, $dup);
    if ($next === $content && str_starts_with($plain($content), strtolower($dup))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($dup, '#') . '\s*(?:</p>)?#u',
            '',
            $content,
            1
        ) ?? $content;
    }

    // Consultant psychiatrist → our-team/psychiatrists.
    $next = preg_replace_callback(
        '#<a\b([^>]*)>(.*?consultant psychiatrist.*?)</a>#is',
        static function (array $m) use ($psychiatrists_url): string {
            $attrs = preg_replace('#href=(["\'])([^"\']*)\1#i', 'href="' . esc_url($psychiatrists_url) . '"', $m[1], 1) ?? $m[1];

            return '<a' . $attrs . '>' . $m[2] . '</a>';
        },
        $next
    ) ?? $next;

    // MDT → our-team.
    $next = str_replace(
        [
            home_url('/about-us/multidisciplinary-teams/'),
            home_url('/about-us/multidisciplinary-teams'),
            '/about-us/multidisciplinary-teams/',
            '/about-us/multidisciplinary-teams',
            'https://www.stpatricks.ie/about-us/multidisciplinary-teams/',
            'https://www.stpatricks.ie/about-us/multidisciplinary-teams',
        ],
        [
            $mdt_url,
            $mdt_url,
            wp_make_link_relative($mdt_url),
            wp_make_link_relative($mdt_url),
            $mdt_url,
            $mdt_url,
        ],
        $next
    );

    $next = $dedupe_pdf_links($next, 'spmhs-wrap-leaflet', 'Download our Recovery Programme with WRAP leaflet here');
    $next = $dedupe_pdf_links($next, 'wrap-leaflet', 'Download our Recovery Programme with WRAP leaflet here');

    if ($next !== $content) {
        $save_programme($wrap_id, 'wrap dedupe/links/brochure', $next);
    } else {
        $log('SKIP wrap: no change');
    }
}

// ---------------------------------------------------------------------------
// 9. SAGE — remove unfinished heading snippet; remove blue/body dups; ensure Who suitable
// ---------------------------------------------------------------------------
$sage_id = $programme_id('sage');
if ($sage_id > 0) {
    $blue = 'SAGE is a psychological therapy group for older adults who are experiencing difficulties with their mental health.';
    $content = (string) get_post_field('post_content', $sage_id);
    $listing = (string) get_field('listing_summary', $sage_id);
    $excerpt = (string) get_post_field('post_excerpt', $sage_id);

    $new_listing = preg_replace(
        '#\s*It is tailored towards older people[…\.].*$#u',
        '',
        $listing
    ) ?? $listing;
    if (str_contains($new_listing, 'It is tailored towards older people')) {
        $new_listing = $blue;
    }
    // Prefer a clean blue without unfinished ellipsis.
    if (preg_match('/It is tailored towards older people[…\.]/u', $new_listing)) {
        $new_listing = $blue;
    }
    if (trim($plain($new_listing)) === '' || str_contains($new_listing, '…')) {
        $new_listing = $blue . ' The group programme is adapted from psychological theories about how some coping styles can impact mental health difficulties.';
    }

    $next = $strip_leading_paragraph_matching($content, $blue);
    // Remove second blue-ish paragraph that duplicates listing adaptation line when immediately under blue.
    $adapt = 'The group programme is adapted from psychological theories about how some coping styles can impact mental health difficulties.';
    if (str_starts_with($plain($next), strtolower($adapt))) {
        $next = $strip_leading_paragraph_matching($next, $adapt);
    }
    // Keep the tailored pattern list in body (complete), but drop unfinished ellipsis-only snippet if present alone.
    $next = preg_replace(
        '#(?:<p\b[^>]*>\s*)?It is tailored towards older people[…\.]+\s*(?:</p>)?#u',
        '',
        $next
    ) ?? $next;

    $next = $replace_section_body(
        $next,
        'Who is the programme suitable for?',
        '<p>SAGE is suitable for older adult service users, aged 65 and over, who are experiencing mental health difficulties and who identify with the coping patterns described above.</p>'
    );

    if ($next !== $content || $new_listing !== $listing) {
        $save_programme($sage_id, 'sage unfinished/dedupe/suitable', $next, null, $new_listing);
    } else {
        $log('SKIP sage: no change');
    }
}

// ---------------------------------------------------------------------------
// 10. Trauma Programme — remove supports dup; dedupe brochure; drop "(opens in a new tab)" from visible text
// ---------------------------------------------------------------------------
$trauma_id = $programme_id('trauma-programme');
if ($trauma_id > 0) {
    $dup = 'The Trauma Programme supports people who have had experiences of trauma and adverse childhood experiences.';
    $content = (string) get_post_field('post_content', $trauma_id);
    $next = $strip_leading_paragraph_matching($content, $dup);
    if ($next === $content && str_starts_with($plain($content), strtolower($dup))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($dup, '#') . '\s*(?:</p>)?#u',
            '',
            $content,
            1
        ) ?? $content;
    }
    $next = $dedupe_pdf_links($next, 'trauma-programme', 'See our Trauma Programme brochure');
    $next = preg_replace(
        '#(Trauma Programme brochure)\s*\(opens in a new tab\)#iu',
        '$1',
        $next
    ) ?? $next;
    if ($next !== $content) {
        $save_programme($trauma_id, 'trauma dedupe/brochure', $next);
    } else {
        $log('SKIP trauma: no change');
    }
}

// ---------------------------------------------------------------------------
// 11. Young Adult Formulation Group — remove dup under blue; ensure Who suitable 18–25
// ---------------------------------------------------------------------------
$yaf_id = $programme_id('young-adult-psychology-groups');
if ($yaf_id > 0) {
    $dup = 'The Young Adult Formulation Group programme is for young adults who wish to develop a psychological understanding of their difficulties, coping strategies and strengths.';
    $content = (string) get_post_field('post_content', $yaf_id);
    $next = $strip_leading_paragraph_matching($content, $dup);
    if ($next === $content && str_starts_with($plain($content), strtolower($dup))) {
        $next = preg_replace(
            '#^\s*(?:<p\b[^>]*>)?\s*' . preg_quote($dup, '#') . '\s*(?:</p>)?#u',
            '',
            $content,
            1
        ) ?? $content;
    }
    $next = $replace_section_body(
        $next,
        'Who is the programme suitable for?',
        '<p>The programme is suitable for young adults aged 18 to 25 who wish to develop a psychological understanding of their difficulties, coping strategies and strengths.</p>'
    );
    if ($next !== $content) {
        $save_programme($yaf_id, 'ya-formulation dedupe/suitable', $next);
    } else {
        $log('SKIP ya-formulation: no change');
    }
}

// ---------------------------------------------------------------------------
// 12. Young Adult Programme — remove ' and aims to…'; fix broken referrals H2
// ---------------------------------------------------------------------------
$yap_id = $programme_id('young-adult-programme-2');
if ($yap_id > 0) {
    $content = (string) get_post_field('post_content', $yap_id);
    $listing = (string) get_field('listing_summary', $yap_id);
    $excerpt = (string) get_post_field('post_excerpt', $yap_id);
    $next = preg_replace('/\s+and aims to[…\.][^\.<]*/u', '', $content) ?? $content;
    $new_listing = preg_replace('/\s+and aims to[…\.][^\.<]*/u', '', $listing) ?? $listing;
    $new_excerpt = preg_replace('/\s+and aims to[…\.][^\.<]*/u', '', $excerpt) ?? $excerpt;
    // Broken "H2: How are referrals made?" stuffed into a <strong>.
    $next = preg_replace(
        '#<strong>\s*(?:\\\\n\\\\t\s*)*H2:\s*How are referrals made\?\s*</strong>#iu',
        '<h2>How are referrals made?</h2>',
        $next
    ) ?? $next;
    $next = preg_replace(
        '#<strong>\s*H2:\s*How are referrals made\?\s*</strong>#iu',
        '<h2>How are referrals made?</h2>',
        $next
    ) ?? $next;
    // Also plain-text broken heading.
    $next = preg_replace(
        '#(?:<p\b[^>]*>\s*)?H2:\s*How are referrals made\?\s*(?:</p>)?#iu',
        '<h2>How are referrals made?</h2>',
        $next
    ) ?? $next;
    if ($next !== $content || $new_listing !== $listing || $new_excerpt !== $excerpt) {
        $save_programme($yap_id, 'yap aims/referrals heading', $next, $new_excerpt, $new_listing);
    } else {
        $log('SKIP yap: no change');
    }
}

// ---------------------------------------------------------------------------
// 13. Getting help — move "But the biggest change…" into H2; drop H3
// ---------------------------------------------------------------------------
$help_id = (int) (get_page_by_path('getting-help')?->ID ?? 0);
if ($help_id > 0) {
    $blocks = get_field('flexible_content_blocks', $help_id);
    $changed = false;
    if (is_array($blocks)) {
        foreach ($blocks as $i => $block) {
            if (($block['acf_fc_layout'] ?? '') !== 'content') {
                continue;
            }
            $heading = (string) ($block['heading'] ?? '');
            $content = (string) ($block['content'] ?? '');
            if (! str_contains($heading, 'If you have concerns about your mental health')) {
                continue;
            }
            $phrase = 'But the biggest change can come from just a single step.';
            if (! str_contains($heading, $phrase)) {
                $blocks[$i]['heading'] = rtrim($heading, " \t\n\r\0\x0B.") . '. ' . $phrase;
                $changed = true;
                $log('FIX getting-help: phrase moved into H2');
            }
            if (preg_match('#<h3[^>]*>\s*' . preg_quote($phrase, '#') . '\s*</h3>\s*#iu', $content)) {
                $blocks[$i]['content'] = preg_replace(
                    '#<h3[^>]*>\s*' . preg_quote($phrase, '#') . '\s*</h3>\s*#iu',
                    '',
                    $content,
                    1
                ) ?? $content;
                $changed = true;
                $log('FIX getting-help: removed H3 phrase');
            }
        }
        if ($changed) {
            if ($dry) {
                $log('DRY getting-help flexi #' . $help_id);
            } else {
                update_field('flexible_content_blocks', $blocks, $help_id);
                clean_post_cache($help_id);
                $log('OK getting-help #' . $help_id);
            }
        } else {
            $log('SKIP getting-help: no change');
        }
    }
}

// ---------------------------------------------------------------------------
// 14. CFT assessment tool news — founder link; remove PLACEHOLDER; qualitative themes link
// ---------------------------------------------------------------------------
$cft_news = get_posts([
    'name' => 'compassion-focused-therapy-eating-disorders-scale-launches',
    'post_type' => 'post',
    'post_status' => 'any',
    'posts_per_page' => 1,
]);
if ($cft_news !== []) {
    $id = (int) $cft_news[0]->ID;
    $content = (string) get_post_field('post_content', $id);
    $next = $content;

    // Replace broken PLACEHOLDER anchor block with correct founder + themes links.
    $wiley = 'https://onlinelibrary.wiley.com/doi/10.1002/capr.12283';
    $replacement = 'named in honour of <a href="' . esc_url($history_url) . '">our founder</a>, Jonathan Swift, is '
        . '<a href="' . esc_url($wiley) . '" target="_blank" rel="noopener noreferrer">derived from qualitative themes identified in earlier published doctoral research</a>'
        . ' conducted at UCD by Dr Georgina Mullen, which examined participants’ experiences of the CFT-E programme.';

    $patterns = [
        '#named in honour of\s*<a\b[^>]*>\s*PLACEHOLDER,\s*Jonathan Swift,\s*is derived from qualitative themes identified in earlier published doctoral research conducted at UCD by Dr Georgina Mullen,\s*which\s*<a\b[^>]*>examined participants[’\'] experiences of the CFT-E programme</a>\s*\.?#isu',
        '#named in honour of\s*PLACEHOLDER,\s*Jonathan Swift,\s*is derived from qualitative themes identified in earlier published doctoral research conducted at UCD by Dr Georgina Mullen,\s*which\s*<a\b[^>]*>examined participants[’\'] experiences of the CFT-E programme</a>\s*\.?#isu',
        '#<a\b[^>]*>\s*PLACEHOLDER,\s*Jonathan Swift,\s*is derived from qualitative themes identified in earlier published doctoral research conducted at UCD by Dr Georgina Mullen,\s*which\s*<a\b[^>]*>examined participants[’\'] experiences of the CFT-E programme</a>\s*</a>#isu',
    ];
    foreach ($patterns as $pattern) {
        $replaced = preg_replace($pattern, $replacement, $next, 1, $count);
        if (is_string($replaced) && $count > 0) {
            $next = $replaced;
            $log('FIX cft-news: PLACEHOLDER/founder/themes rewritten');
            break;
        }
    }
    // Fallback: strip PLACEHOLDER token and ensure founder + themes anchors exist.
    if (str_contains($next, 'PLACEHOLDER')) {
        $next = str_replace('PLACEHOLDER, Jonathan Swift', 'our founder, Jonathan Swift', $next);
        $next = str_replace('PLACEHOLDER', 'our founder', $next);
        $log('FIX cft-news: stripped PLACEHOLDER token');
    }
    // Ensure "our founder" links to our-history when bare or wrong.
    if (preg_match('#our founder#i', $next) && ! preg_match('#<a\b[^>]*>\s*our founder\s*</a>#i', $next)) {
        $next = preg_replace('#(?<!["\'>])our founder#i', '<a href="' . esc_url($history_url) . '">our founder</a>', $next, 1) ?? $next;
    } else {
        $next = preg_replace(
            '#<a\b([^>]*)>\s*our founder\s*</a>#i',
            '<a href="' . esc_url($history_url) . '">our founder</a>',
            $next,
            1
        ) ?? $next;
    }

    if ($next !== $content) {
        $save_post($id, 'cft-assessment-tool news', $next);
    } else {
        $log('SKIP cft-news: no change');
    }
}

// ---------------------------------------------------------------------------
// 15. 20 years of SUAS — strategy link → live strategy URL
// ---------------------------------------------------------------------------
$suas = get_posts([
    'name' => '20-years-of-service-user-engagement',
    'post_type' => 'post',
    'post_status' => 'any',
    'posts_per_page' => 1,
]);
if ($suas !== []) {
    $id = (int) $suas[0]->ID;
    $content = (string) get_post_field('post_content', $id);
    $next = preg_replace_callback(
        '#<a\b([^>]*)>([^<]*2023 to 2027 organisational strategy[^<]*)</a>#iu',
        static function (array $m) use ($strategy_url): string {
            $attrs = preg_replace('#href=(["\'])([^"\']*)\1#i', 'href="' . esc_url($strategy_url) . '"', $m[1], 1) ?? $m[1];
            // External live URL.
            if (! preg_match('#\btarget=#i', $attrs)) {
                $attrs .= ' target="_blank" rel="noopener noreferrer"';
            }

            return '<a' . $attrs . '>' . $m[2] . '</a>';
        },
        $content
    ) ?? $content;
    if ($next !== $content) {
        $save_post($id, '20-years strategy link', $next);
    } else {
        $log('SKIP 20-years: strategy link already ok or missing');
    }
}

// ---------------------------------------------------------------------------
// 16. Fax referrals — ensure published; fix legacy RAS URL (redirect map too)
// ---------------------------------------------------------------------------
$fax = get_posts([
    'name' => 'closure-of-fax-referrals',
    'post_type' => 'post',
    'post_status' => 'any',
    'posts_per_page' => 1,
]);
if ($fax !== []) {
    $id = (int) $fax[0]->ID;
    $status = get_post_status($id);
    if ($status !== 'publish') {
        if ($dry) {
            $log('DRY fax publish #' . $id . ' was ' . $status);
        } else {
            wp_update_post(['ID' => $id, 'post_status' => 'publish']);
            $log('OK fax published #' . $id);
        }
    } else {
        $log('OK fax already publish #' . $id . ' (remove path-redirect-map entry if still shadowed)');
    }

    $content = (string) get_post_field('post_content', $id);
    $hp_url = trailingslashit(home_url('/healthcare-professionals'));
    $next = preg_replace(
        '#href=(["\'])(?:https?://[^/\'"]+)?/gps-referrals/referrals-admissions[^"\']*\1#iu',
        'href="' . esc_url($hp_url) . '"',
        $content
    ) ?? $content;
    if ($next !== $content) {
        $save_post($id, 'fax RAS link → healthcare-professionals', $next);
    } else {
        $log('SKIP fax: RAS link already ok or missing');
    }
}

// ---------------------------------------------------------------------------
// 17. 2024 Founder's Day — fix broken links; leave related auto-query
// ---------------------------------------------------------------------------
$fd = get_posts([
    'name' => 'founders-day-conference-unlocking-potential-of-later-life',
    'post_type' => 'post',
    'post_status' => 'any',
    'posts_per_page' => 1,
]);
if ($fd !== []) {
    $id = (int) $fd[0]->ID;
    $content = (string) get_post_field('post_content', $id);
    $next = $content;

    // Normalize depression/anxiety destinations (collapse corrupt repeats; fix bare paths).
    $next = preg_replace(
        '#(?:https?://[^"\']+)?(?:/mental-health)+/(depression|anxiety)/+#i',
        '/mental-health/$1/',
        $next
    ) ?? $next;
    $next = preg_replace('#(?<!mental-health)/depression/?#i', '/mental-health/depression/', $next) ?? $next;
    $next = preg_replace('#(?<!mental-health)/anxiety/?#i', '/mental-health/anxiety/', $next) ?? $next;

    $next = preg_replace(
        '#(?:https?://[^"\']+)?/about-us/staff-directory/consultant-psychiatrists/?#i',
        wp_make_link_relative($psychiatrists_url),
        $next
    ) ?? $next;

    // Ensure drinking article link stays on the published post permalink.
    $drink = get_posts([
        'name' => 'how-can-you-tell-if-your-drinking-is-harmful',
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => 1,
    ]);
    if ($drink !== []) {
        $drink_url = trailingslashit((string) get_permalink($drink[0]));
        $next = preg_replace(
            '#href=(["\'])[^"\']*how-can-you-tell-if-your-drinking-is-harmful/?\1#i',
            'href="' . esc_url($drink_url) . '"',
            $next
        ) ?? $next;
    }

    // Consultant Old Age Psychiatrist → psychiatrists team page.
    $next = preg_replace_callback(
        '#<a\b([^>]*)>([^<]*Consultant Old Age Psychiatrist[^<]*)</a>#iu',
        static function (array $m) use ($psychiatrists_url): string {
            $attrs = preg_replace('#href=(["\'])([^"\']*)\1#i', 'href="' . esc_url($psychiatrists_url) . '"', $m[1], 1) ?? $m[1];

            return '<a' . $attrs . '>' . $m[2] . '</a>';
        },
        $next
    ) ?? $next;

    if ($next !== $content) {
        $save_post($id, 'founders-day-2024 links', $next);
    } else {
        $log('SKIP founders-day: no content link change');
    }
    $log('NOTE founders-day PDF icon is theme-controlled (is-pdf-link); related links are auto-queried.');
}

$log('=== done ===');
if (! defined('WP_CLI') || ! WP_CLI) {
    echo implode("\n", $lines) . "\n";
}
