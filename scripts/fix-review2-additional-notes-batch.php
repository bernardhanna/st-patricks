<?php
/**
 * Review 2 Additional-notes batch (client sheet 24 Nov 2026).
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-additional-notes-batch.php?key=fix-review2
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

$walk_flexi = static function (int $post_id, callable $mutator) use (&$log): void {
    if ($post_id < 1 || ! function_exists('get_field') || ! function_exists('update_field')) {
        return;
    }
    $rows = get_field('flexible_content_blocks', $post_id);
    if (! is_array($rows) || $rows === []) {
        return;
    }
    $changed = false;
    foreach ($rows as $i => $row) {
        if (! is_array($row)) {
            continue;
        }
        $next = $mutator($row, $i);
        if (is_array($next)) {
            $rows[$i] = $next;
            $changed = true;
        }
    }
    if ($changed) {
        update_field('flexible_content_blocks', $rows, $post_id);
        clean_post_cache($post_id);
    }
};

$replace_in_fields = static function (array $row, array $fields, callable $replacer): ?array {
    $changed = false;
    foreach ($fields as $field) {
        $html = (string) ($row[$field] ?? '');
        if ($html === '') {
            continue;
        }
        $next = $replacer($html);
        if ($next !== $html) {
            $row[$field] = $next;
            $changed = true;
        }
    }

    return $changed ? $row : null;
};

$string_fields = [
    'content', 'intro_text', 'hero_intro', 'heading', 'heading_text', 'title',
    'body', 'text', 'snippet', 'description', 'cta_label', 'button_label',
    'button_text', 'link_label', 'label',
];

// ---------------------------------------------------------------------------
// 1. Our present and future — remove stray Modernising block; fix Learn more wording
// ---------------------------------------------------------------------------
$present_id = (int) (get_page_by_path('about-us/our-present-and-future')?->ID ?? 0);
if ($present_id > 0) {
    $rows = get_field('flexible_content_blocks', $present_id);
    if (is_array($rows)) {
        $new_rows = [];
        $changed = false;
        $new_hospital = home_url('/about-us/our-present-and-future/new-hospital/');
        $national = home_url('/about-us/our-present-and-future/national-centre/');
        $extending = home_url('/about-us/our-present-and-future/extending-and-enhancing-our-services/');
        $partnering = home_url('/about-us/our-present-and-future/partnering-with-service-users/');

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $layout = (string) ($row['acf_fc_layout'] ?? '');
            $blob = wp_strip_all_tags(implode(' ', array_map(
                static fn ($f) => (string) ($row[$f] ?? ''),
                ['content', 'intro_text', 'heading', 'heading_text', 'title']
            )));

            // Drop the orphan “Modernising our facilities” content row after videos.
            if (
                in_array($layout, ['content', 'text', 'rich_text'], true)
                && str_contains($blob, 'Modernising our facilities')
                && ! str_contains(strtolower($blob), 'key strategic')
                && ! str_contains(strtolower($blob), 'strategic developments')
            ) {
                $changed = true;
                $log[] = 'OK present: removed stray Modernising block';
                continue;
            }

            if ($layout === 'content' || $layout === 'about_links_grid' || $layout === 'card_grid') {
                foreach ($string_fields as $field) {
                    $html = (string) ($row[$field] ?? '');
                    if ($html === '') {
                        continue;
                    }
                    $next = $html;
                    $next = preg_replace(
                        '#<a([^>]*href=["\'][^"\']*extending[^"\']*["\'][^>]*)>.*?Extending\s+And\s+Enhancing\s+Our\s+Services.*?</a>#is',
                        '<a$1>Learn more about extending and enhancing our services here</a>',
                        $next
                    ) ?? $next;
                    $next = preg_replace(
                        '#Learn more\s+Extending\s+And\s+Enhancing\s+Our\s+Services#i',
                        'Learn more about extending and enhancing our services here',
                        $next
                    ) ?? $next;
                    // National Centre bare label → Learn more here (keep href).
                    $next = preg_replace(
                        '#(<a([^>]*href=["\'][^"\']*national-centre[^"\']*["\'][^>]*)>)(?:Learn more\s*)?National\s+Centre(</a>)#i',
                        '$1Learn more here$3',
                        $next
                    ) ?? $next;
                    $next = preg_replace(
                        '#(<a([^>]*href=["\'][^"\']*partnering[^"\']*["\'][^>]*)>)(?:Learn more\s*)?Partnering\s+With\s+Service\s+Users(</a>)#i',
                        '$1Learn more here$3',
                        $next
                    ) ?? $next;
                    // Ensure modernising has a Learn more here link to new hospital.
                    if (
                        str_contains($next, 'Modernising our facilities')
                        && ! preg_match('#href=["\'][^"\']*new-hospital#i', $next)
                    ) {
                        $next = preg_replace(
                            '#(Modernising our facilities\.?)#i',
                            '$1 <a href="' . esc_url($new_hospital) . '">Learn more here</a>.',
                            $next,
                            1
                        ) ?? $next;
                    } else {
                        $next = preg_replace(
                            '#(<a([^>]*href=["\'][^"\']*new-hospital[^"\']*["\'][^>]*)>)(?:Learn more\s*)?(?:about\s+)?(?:modernising[^<]*|here)?(</a>)#i',
                            '$1Learn more here$3',
                            $next
                        ) ?? $next;
                    }
                    if ($next !== $html) {
                        $row[$field] = $next;
                        $changed = true;
                    }
                }
            }

            $new_rows[] = $row;
        }

        if ($changed) {
            update_field('flexible_content_blocks', $new_rows, $present_id);
            clean_post_cache($present_id);
            $log[] = 'OK present flexi updated';
        } else {
            $log[] = 'SKIP present: no flexi change';
        }
    }
} else {
    $log[] = 'MISS present page';
}

// ---------------------------------------------------------------------------
// 2. Careers — drop duplicate Career-1 body images / leftover Figma asset
// ---------------------------------------------------------------------------
$careers_id = (int) (get_page_by_path('about-us/careers')?->ID ?? 0);
if ($careers_id > 0) {
    $rows = get_field('flexible_content_blocks', $careers_id);
    if (is_array($rows)) {
        $changed = false;
        $seen_career = 0;
        foreach ($rows as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            $layout = (string) ($row['acf_fc_layout'] ?? '');
            // Remove image-only rows that re-use Career 1 or Figma dumps under Why work with us.
            if (in_array($layout, ['image', 'media', 'full_width_image', 'image_feature'], true)) {
                $img = $row['image'] ?? $row['hero_image'] ?? $row['media'] ?? null;
                $id = is_array($img) ? (int) ($img['ID'] ?? $img['id'] ?? 0) : (int) $img;
                $url = '';
                if ($id > 0) {
                    $url = (string) wp_get_attachment_url($id);
                } elseif (is_array($img)) {
                    $url = (string) ($img['url'] ?? '');
                }
                $name = strtolower($url . ' ' . (is_array($img) ? (string) ($img['filename'] ?? '') : ''));
                if (str_contains($name, 'career-1') || preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-/i', $name)) {
                    $seen_career++;
                    // Keep the first career image if it's a dedicated content image; drop extras + figma.
                    if ($seen_career > 1 || preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-/i', $name)) {
                        unset($rows[$i]);
                        $changed = true;
                        $log[] = 'OK careers: removed duplicate/figma image row';
                    }
                }
            }
            // Also strip nested image fields that duplicate Career-1 inside content cards.
            foreach (['image', 'side_image', 'card_image', 'feature_image'] as $img_field) {
                if (! isset($row[$img_field])) {
                    continue;
                }
                $img = $row[$img_field];
                $id = is_array($img) ? (int) ($img['ID'] ?? $img['id'] ?? 0) : (int) $img;
                $url = $id > 0 ? (string) wp_get_attachment_url($id) : (is_array($img) ? (string) ($img['url'] ?? '') : '');
                if ($url !== '' && str_contains(strtolower($url), 'career-1') && $layout !== 'hero_with_breadcrumbs' && $layout !== 'hero') {
                    // Clear duplicate body Career-1; keep hero alone.
                    $row[$img_field] = null;
                    $rows[$i] = $row;
                    $changed = true;
                    $log[] = 'OK careers: cleared duplicate Career-1 on ' . $layout;
                }
            }
        }
        if ($changed) {
            update_field('flexible_content_blocks', array_values($rows), $careers_id);
            clean_post_cache($careers_id);
        } else {
            $log[] = 'SKIP careers: no duplicate image rows cleared (may need manual crop)';
        }
    }
}

// ---------------------------------------------------------------------------
// 3. HCP Training Centre — GP Webinars on YouTube
// ---------------------------------------------------------------------------
$hcp_training = (int) (get_page_by_path('healthcare-professionals/training-centre')?->ID ?? 0);
$gp_playlist = 'https://www.youtube.com/playlist?list=PL9Qr7kXsp_qRJM-4TAIIwlUG5CLzrrzV9';
if ($hcp_training > 0) {
    $walk_flexi($hcp_training, static function (array $row) use ($gp_playlist, $string_fields, &$log): ?array {
        $changed = false;
        foreach ($string_fields as $field) {
            $html = (string) ($row[$field] ?? '');
            if ($html === '') {
                continue;
            }
            $next = str_replace('SPMHS on YouTube', 'GP Webinars on YouTube', $html);
            $next = preg_replace(
                '#(href=["\'])https?://(www\.)?youtube\.com/?["\']#i',
                '$1' . esc_url($gp_playlist) . '"',
                $next
            ) ?? $next;
            $next = preg_replace(
                '#(href=["\'])https?://(www\.)?youtube\.com/(@StPatricksMentalHealth|c/[^"\']+|user/[^"\']+)["\']#i',
                '$1' . esc_url($gp_playlist) . '"',
                $next
            ) ?? $next;
            if ($next !== $html) {
                $row[$field] = $next;
                $changed = true;
            }
        }
        // CTA link objects
        foreach (['cta', 'button', 'link', 'primary_cta'] as $cta_field) {
            if (! isset($row[$cta_field]) || ! is_array($row[$cta_field])) {
                continue;
            }
            $cta = $row[$cta_field];
            $title = (string) ($cta['title'] ?? $cta['label'] ?? '');
            $url = (string) ($cta['url'] ?? '');
            $new_title = str_replace('SPMHS on YouTube', 'GP Webinars on YouTube', $title);
            $new_url = $url;
            if ($title !== '' && (str_contains($title, 'YouTube') || str_contains($new_title, 'GP Webinars'))) {
                $new_url = $gp_playlist;
            }
            if ($new_title !== $title || $new_url !== $url) {
                $cta['title'] = $new_title !== '' ? $new_title : 'GP Webinars on YouTube';
                $cta['url'] = $new_url;
                $row[$cta_field] = $cta;
                $changed = true;
            }
        }
        if ($changed) {
            $log[] = 'OK hcp training: GP Webinars on YouTube';
            return $row;
        }
        return null;
    });
} else {
    $log[] = 'MISS hcp training-centre';
}

// ---------------------------------------------------------------------------
// 4. HCP Contact numbers — include service users in Clinical Governance copy
// ---------------------------------------------------------------------------
$contact_numbers = (int) (get_page_by_path('healthcare-professionals/contact-numbers')?->ID ?? 0);
if ($contact_numbers > 0) {
    $target = 'The Clinical Governance Office manages feedback and complaints. Healthcare professionals and service users can contact the office directly at <a href="mailto:clinicalgovernance@stpatricks.ie">clinicalgovernance@stpatricks.ie</a>.';
    $walk_flexi($contact_numbers, static function (array $row) use ($target, $string_fields, &$log): ?array {
        $changed = false;
        foreach ($string_fields as $field) {
            $html = (string) ($row[$field] ?? '');
            if ($html === '' || ! str_contains(strtolower($html), 'clinical governance')) {
                continue;
            }
            if (str_contains(strtolower($html), 'service users can contact')) {
                continue;
            }
            // Replace the healthcare-professionals-only sentence.
            $next = preg_replace(
                '#Healthcare professionals can contact[^<]*(?:<[^>]+>[^<]*)*clinicalgovernance@stpatricks\.ie(?:</a>)?\.#i',
                $target,
                $html
            ) ?? $html;
            if ($next === $html) {
                $next = preg_replace(
                    '#The Clinical Governance Office[^<]*(?:<[^>]+>[^<]*)*\.#i',
                    $target,
                    $html,
                    1
                ) ?? $html;
            }
            if ($next !== $html) {
                $row[$field] = $next;
                $changed = true;
                $log[] = 'OK contact-numbers: service users + clinical governance';
            }
        }
        return $changed ? $row : null;
    });
}

// ---------------------------------------------------------------------------
// 5. Service users landing — remove sitemap placeholder snippet
// ---------------------------------------------------------------------------
$su_landing = (int) (get_page_by_path('service-users-and-visitors')?->ID ?? 0);
if ($su_landing > 0) {
    $walk_flexi($su_landing, static function (array $row) use ($string_fields, &$log): ?array {
        $changed = false;
        foreach ($string_fields as $field) {
            $html = (string) ($row[$field] ?? '');
            if ($html === '') {
                continue;
            }
            if (
                str_contains($html, 'landing page (per sitemap)')
                || str_contains($html, 'Page context goes here')
            ) {
                $row[$field] = '';
                $changed = true;
                $log[] = 'OK su landing: cleared placeholder snippet in ' . $field;
            }
        }
        return $changed ? $row : null;
    });
    // Also clear page excerpt if it holds the placeholder.
    $excerpt = (string) get_post_field('post_excerpt', $su_landing);
    if (str_contains($excerpt, 'landing page (per sitemap)') || str_contains($excerpt, 'Page context goes here')) {
        wp_update_post(['ID' => $su_landing, 'post_excerpt' => '']);
        $log[] = 'OK su landing: cleared post_excerpt';
    }
}

// ---------------------------------------------------------------------------
// 6. Adolescent homecare — FAQs button label
// ---------------------------------------------------------------------------
$homecare = (int) (get_page_by_path('service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent')?->ID ?? 0);
if ($homecare > 0) {
    $walk_flexi($homecare, static function (array $row) use ($string_fields, &$log): ?array {
        $changed = false;
        foreach ($string_fields as $field) {
            $html = (string) ($row[$field] ?? '');
            if ($html === '') {
                continue;
            }
            $next = preg_replace(
                '#(>|\b)Frequently asked questions(?!\s*\(FAQs\))(<|\b)#i',
                '$1Frequently Asked Questions (FAQs)$2',
                $html
            ) ?? $html;
            $next = str_replace('requently Asked Questions (FAQs)', 'Frequently Asked Questions (FAQs)', $next);
            if ($next !== $html) {
                $row[$field] = $next;
                $changed = true;
            }
        }
        foreach (['cta', 'button', 'link', 'primary_cta'] as $cta_field) {
            if (! isset($row[$cta_field]) || ! is_array($row[$cta_field])) {
                continue;
            }
            $title = (string) ($row[$cta_field]['title'] ?? '');
            if ($title !== '' && preg_match('/frequently asked questions/i', $title) && ! str_contains($title, '(FAQs)')) {
                $row[$cta_field]['title'] = 'Frequently Asked Questions (FAQs)';
                $changed = true;
            }
        }
        if ($changed) {
            $log[] = 'OK adolescent homecare: FAQs button label';
            return $row;
        }
        return null;
    });
}

// ---------------------------------------------------------------------------
// 7. Attending day programmes — mailto suits@
// ---------------------------------------------------------------------------
$day_prog = (int) (get_page_by_path('service-users-and-visitors/attending-day-programmes')?->ID ?? 0);
if ($day_prog > 0) {
    $walk_flexi($day_prog, static function (array $row) use ($string_fields, &$log): ?array {
        $changed = false;
        foreach ($string_fields as $field) {
            $html = (string) ($row[$field] ?? '');
            if ($html === '' || ! str_contains($html, 'suits@stpatricks.ie')) {
                continue;
            }
            if (str_contains($html, 'mailto:suits@stpatricks.ie')) {
                continue;
            }
            $next = preg_replace(
                '#(?<!mailto:|">)suits@stpatricks\.ie#i',
                '<a href="mailto:suits@stpatricks.ie">suits@stpatricks.ie</a>',
                $html
            ) ?? $html;
            if ($next !== $html) {
                $row[$field] = $next;
                $changed = true;
                $log[] = 'OK attending day programmes: mailto suits@';
            }
        }
        return $changed ? $row : null;
    });
}

// ---------------------------------------------------------------------------
// 8. Attending a Dean Clinic — Contact Us link wording
// ---------------------------------------------------------------------------
$dean_clinic = (int) (get_page_by_path('service-users-and-visitors/attending-a-dean-clinic')?->ID ?? 0);
if ($dean_clinic > 0) {
    $locations = home_url('/about-us/our-locations/');
    $walk_flexi($dean_clinic, static function (array $row) use ($string_fields, $locations, &$log): ?array {
        $changed = false;
        foreach ($string_fields as $field) {
            $html = (string) ($row[$field] ?? '');
            if ($html === '') {
                continue;
            }
            $next = $html;
            // Preferred client wording from Additional notes.
            $next = preg_replace(
                '#If you are attending in-person,\s*you can get addresses and directions for our Dean Clinics\s*<a([^>]*)>\s*Contact Us\s*</a>\.?#i',
                'If you are attending in person, you can get addresses and directions for our Dean Clinics <a$1>here</a>.',
                $next
            ) ?? $next;
            $next = preg_replace(
                '#Dean Clinics\s*<a([^>]*)>\s*Contact Us\s*</a>#i',
                'Dean Clinics <a$1>here</a>',
                $next
            ) ?? $next;
            if ($next !== $html) {
                $row[$field] = $next;
                $changed = true;
                $log[] = 'OK dean clinic: Contact Us wording';
            }
        }
        return $changed ? $row : null;
    });
}

// ---------------------------------------------------------------------------
// 9. About St Patrick's at Home — accordion heading swap if mixed
// ---------------------------------------------------------------------------
$athome = (int) (get_page_by_path('service-users-and-visitors/about-our-st-patricks-at-home-service')?->ID ?? 0);
if ($athome > 0) {
    $walk_flexi($athome, static function (array $row) use (&$log): ?array {
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if (! in_array($layout, ['accordion', 'faqs', 'faq_accordion', 'expandable_content'], true)) {
            return null;
        }
        $heading = (string) ($row['heading'] ?? $row['heading_text'] ?? $row['title'] ?? '');
        // If second FAQ group is still "Receiving care", rename to What to expect from homecare when Getting started already exists nearby — only rename Receiving care → What to expect from homecare when that's the client ask.
        if (strcasecmp(trim($heading), 'Receiving care') === 0) {
            if (isset($row['heading'])) {
                $row['heading'] = 'What to expect from homecare';
            }
            if (isset($row['heading_text'])) {
                $row['heading_text'] = 'What to expect from homecare';
            }
            if (isset($row['title'])) {
                $row['title'] = 'What to expect from homecare';
            }
            $log[] = 'OK at-home: Receiving care → What to expect from homecare';
            return $row;
        }
        return null;
    });
}

// ---------------------------------------------------------------------------
// 10. Programmes — Array snippet, H2 dump, mindfulness unlink, brochure CTA
// ---------------------------------------------------------------------------
$fix_programme = static function (string $slug, callable $fn) use (&$log): void {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'programmes_therapies',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    if ($posts === []) {
        $log[] = 'MISS programme ' . $slug;
        return;
    }
    $fn((int) $posts[0]->ID, $posts[0]);
};

$fix_programme('emotion-focused-therapy-for-young-adults', static function (int $id) use (&$log): void {
    $summary = (string) get_field('listing_summary', $id);
    if (strcasecmp(trim($summary), 'Array') === 0 || trim($summary) === 'array') {
        update_field('listing_summary', '', $id);
        $log[] = 'OK EFT-YA: cleared Array listing_summary';
    }
    // Hero intro fields
    $rows = get_field('flexible_content_blocks', $id);
    if (is_array($rows)) {
        $changed = false;
        foreach ($rows as $i => $row) {
            foreach (['intro_text', 'hero_intro', 'content', 'snippet'] as $f) {
                $v = (string) ($row[$f] ?? '');
                if (strcasecmp(trim(wp_strip_all_tags($v)), 'Array') === 0) {
                    $rows[$i][$f] = '';
                    $changed = true;
                }
            }
        }
        if ($changed) {
            update_field('flexible_content_blocks', $rows, $id);
            $log[] = 'OK EFT-YA: cleared Array from flexi';
        }
    }
    $excerpt = (string) get_post_field('post_excerpt', $id);
    if (strcasecmp(trim($excerpt), 'Array') === 0) {
        wp_update_post(['ID' => $id, 'post_excerpt' => '']);
        $log[] = 'OK EFT-YA: cleared Array excerpt';
    }
});

$fix_programme('young-adult-programme-2', static function (int $id) use (&$log): void {
    $content = (string) get_post_field('post_content', $id);
    $next = preg_replace('#(?:<p>)?\s*H2:\s*How are referrals made\??\s*(?:</p>)?#i', '', $content) ?? $content;
    $next = str_replace("H2: How are referrals made?", '', $next);
    if ($next !== $content) {
        wp_update_post(['ID' => $id, 'post_content' => $next]);
        $log[] = 'OK young-adult-programme: removed H2 dump';
    }
    $rows = get_field('flexible_content_blocks', $id);
    if (is_array($rows)) {
        $changed = false;
        foreach ($rows as $i => $row) {
            foreach (['content', 'intro_text', 'body'] as $f) {
                $html = (string) ($row[$f] ?? '');
                if ($html === '' || ! str_contains($html, 'H2: How are referrals made')) {
                    continue;
                }
                $rows[$i][$f] = preg_replace('#(?:<p>)?\s*H2:\s*How are referrals made\??\s*(?:</p>)?#i', '', $html) ?? $html;
                $changed = true;
            }
        }
        if ($changed) {
            update_field('flexible_content_blocks', $rows, $id);
            $log[] = 'OK young-adult-programme: cleared H2 dump from flexi';
        }
    }
});

$fix_programme('focused-acceptance-and-commitment-therapy', static function (int $id) use (&$log): void {
    $unlink = static function (string $html): string {
        return preg_replace(
            '#<a[^>]+href=["\'][^"\']*spmhs-mindfulness\.pdf[^"\']*["\'][^>]*>(.*?)</a>#is',
            '$1',
            $html
        ) ?? $html;
    };
    $content = (string) get_post_field('post_content', $id);
    $next = $unlink($content);
    if ($next !== $content) {
        wp_update_post(['ID' => $id, 'post_content' => $next]);
        $log[] = 'OK FACT: unlinked mindfulness PDF from content';
    }
    $rows = get_field('flexible_content_blocks', $id);
    if (is_array($rows)) {
        $changed = false;
        foreach ($rows as $i => $row) {
            foreach (['content', 'intro_text', 'body'] as $f) {
                $html = (string) ($row[$f] ?? '');
                $n = $unlink($html);
                if ($n !== $html) {
                    $rows[$i][$f] = $n;
                    $changed = true;
                }
            }
        }
        if ($changed) {
            update_field('flexible_content_blocks', $rows, $id);
            $log[] = 'OK FACT: unlinked mindfulness from flexi';
        }
    }
});

$fix_programme('eating-disorders-treatment-information-programme', static function (int $id) use (&$log): void {
    // Ensure brochure CTA uses button-style field if present as plain linked text in hero.
    $rows = get_field('flexible_content_blocks', $id);
    if (! is_array($rows)) {
        return;
    }
    $changed = false;
    foreach ($rows as $i => $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if (! in_array($layout, ['hero_with_breadcrumbs', 'hero'], true)) {
            continue;
        }
        foreach (['intro_text', 'content', 'hero_intro'] as $f) {
            $html = (string) ($row[$f] ?? '');
            if ($html === '' || ! str_contains(strtolower($html), 'get the programme brochure')) {
                continue;
            }
            // If it's already an <a>, add button classes.
            $next = preg_replace(
                '#<a([^>]*href=["\'][^"\']+["\'][^>]*)>(\s*Get the programme brochure\s*)</a>#i',
                '<a$1 class="btn inline-flex items-center justify-center rounded-[6px] border border-[#024B79] bg-[#024B79] px-4 py-2 text-white">$2</a>',
                $html
            ) ?? $html;
            if ($next !== $html) {
                $rows[$i][$f] = $next;
                $changed = true;
                $log[] = 'OK ED TIP: brochure link styled as CTA';
            }
        }
        // Prefer dedicated brochure CTA fields when empty.
        if (empty($row['brochure_cta_label']) && empty($row['cta_label'])) {
            // no-op if fields don't exist
        }
    }
    if ($changed) {
        update_field('flexible_content_blocks', $rows, $id);
    }
});

// ---------------------------------------------------------------------------
// 11. HCP referral pages — remove placeholder testimonials; fix YouTube
// ---------------------------------------------------------------------------
$referral_fixes = [
    'healthcare-professionals/refer-an-adult-for-inpatient-care' => [
        'videos' => [
            ['url' => 'https://youtu.be/mQmYjdN9f6E', 'caption' => 'Our inpatient facilities and activities'],
            ['url' => 'https://youtu.be/_Qz7oaebdO8', 'caption' => 'What to expect as an adult inpatient'],
        ],
    ],
    'healthcare-professionals/refer-to-a-day-programme' => [
        'videos' => [],
    ],
    'healthcare-professionals/refer-for-outpatient-care' => [
        'videos' => [
            ['url' => 'https://youtu.be/mN0Qyhix71E', 'caption' => "Learn more about St Patrick's Mental Health Services"],
        ],
    ],
];

foreach ($referral_fixes as $path => $cfg) {
    $page_id = (int) (get_page_by_path($path)?->ID ?? 0);
    if ($page_id < 1) {
        $log[] = 'MISS ' . $path;
        continue;
    }
    $rows = get_field('flexible_content_blocks', $page_id);
    if (! is_array($rows)) {
        continue;
    }
    $changed = false;
    $new_rows = [];
    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }
        $layout = (string) ($row['acf_fc_layout'] ?? '');

        if ($layout === 'testimonials') {
            // Drop placeholder / generic advocacy testimonials for now.
            $items = $row['manual_items'] ?? $row['items'] ?? [];
            $blob = wp_json_encode($items);
            if (
                str_contains((string) $blob, 'Advocacy Committee')
                || str_contains((string) $blob, 'Long testimonials example')
                || str_contains((string) $blob, 'Tom, Service User')
            ) {
                $changed = true;
                $log[] = 'OK ' . $path . ': removed placeholder testimonials';
                continue;
            }
        }

        if ($layout === 'video_showcase' && $cfg['videos'] !== []) {
            $slides = [];
            foreach ($cfg['videos'] as $vid) {
                $slides[] = [
                    'video_source_type' => 'embed_url',
                    'video_embed_url' => $vid['url'],
                    'caption' => $vid['caption'],
                ];
            }
            $row['slides'] = $slides;
            $row['layout_style'] = $row['layout_style'] ?? 'feature_slider';
            if (empty($row['heading']) && empty($row['heading_text'])) {
                $row['heading'] = 'Watch';
            }
            $changed = true;
            $log[] = 'OK ' . $path . ': replaced videos';
        } elseif ($layout === 'video_showcase') {
            // Day programmes: still drop broken placeholder videos if present.
            $slides = $row['slides'] ?? [];
            $json = wp_json_encode($slides);
            if (str_contains((string) $json, 'ysz5S6PUM-U')) {
                $changed = true;
                $log[] = 'OK ' . $path . ': removed broken placeholder videos';
                continue;
            }
        }

        $new_rows[] = $row;
    }
    if ($changed) {
        update_field('flexible_content_blocks', array_values($new_rows), $page_id);
        clean_post_cache($page_id);
    }
}

// ---------------------------------------------------------------------------
// 12. News bylines Bernard → St Patrick's Mental Health Services
// ---------------------------------------------------------------------------
$byline_slugs = [
    'research-on-emotional-regulation-for-young-people',
    '2023-stigma-survey',
    'artmindfulness',
    'mental-health-recovery',
    'finding-meaning',
    'why-we-should-celebrate-mental-health-recovery',
    'the-mirror-view-a-reflection-on-self-stigma',
    'speakagainststigma',
    'podcasts-from-shac-2019',
    'vulnerability-hacking',
    'vulnerability-hacking-social-media-and-eating-disorders',
    'improving-guidance-for-managing-antipsychotic-induced-weight-gain',
    'recognising-and-responding-to-under-represented-groups-in-eating-disorders',
    'antipsychotic-medication-weight-gain',
    'coronavirus-anxiety',
    'assisted-decision-making-capacity-act',
    'depression-in-older-adults',
    'what-is-depression',
    'migrant-and-refugee-mental-health-founders-day',
    'enjoying-wellbeing-and-healthy-aging-in-older-adulthood',
    'supporting-those-bereaved-by-suicide',
    'recovery-and-me-how-cinema-has-helped-with-my-depression',
    'memories-light-the-corners-of-my-mind',
];
$org = "St Patrick's Mental Health Services";
$byline_count = 0;
foreach ($byline_slugs as $slug) {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'post',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    if ($posts === []) {
        continue;
    }
    $pid = (int) $posts[0]->ID;
    $current = function_exists('matrix_get_blog_post_author_name')
        ? (string) matrix_get_blog_post_author_name($pid)
        : (string) get_field('post_author_name', $pid);
    if (stripos($current, 'bernard') === false && $current !== '' && stripos($current, 'patrick') !== false) {
        continue;
    }
    if (function_exists('update_field')) {
        update_field('post_author_name', $org, $pid);
    } else {
        update_post_meta($pid, 'post_author_name', $org);
    }
    $byline_count++;
}
$log[] = 'OK bylines updated: ' . $byline_count;

echo implode("\n", $log) . "\nDONE\n";
