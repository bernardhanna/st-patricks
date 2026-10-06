<?php

/**
 * Apply round 3 snag-list content and layout fixes.
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/fix-round3-snags.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$p = static function (string $html): string {
    return '<p>' . $html . '</p>';
};
$permalink = static function (string $path): string {
    $page = get_page_by_path($path);

    return $page instanceof WP_Post ? (string) get_permalink($page) : home_url('/' . trim($path, '/') . '/');
};
$save = static function (int $post_id, array $rows, string $label): void {
    update_field('flexible_content_blocks', $rows, $post_id);
    WP_CLI::success($label . ' #' . $post_id);
};
$hero_image_id = static function (array $hero): int {
    $image = $hero['hero_image'] ?? '';
    if (is_array($image)) {
        return (int) ($image['ID'] ?? $image['id'] ?? 0);
    }

    return (int) $image;
};
$apply_hero_image = static function (array &$hero, int $image_id): void {
    if ($image_id <= 0 || get_post_type($image_id) !== 'attachment') {
        return;
    }

    $hero['hero_image'] = $image_id;
    $hero['layout_style'] = 'image_split';
};

$charter_url = 'https://www.stpatricks.ie/media/1241/charter-of-patient-and-family-rights-and-responsibilities.pdf';
$charter_button = '<p><a class="' . esc_attr(matrix_get_content_button_class_names('filled')) . '" href="' . esc_url($charter_url) . '" target="_blank" rel="noopener noreferrer">See the service user charter here</a></p>';
$rec_url = $permalink('about-us/research/research-ethics-committee');
$participation_url = $permalink('service-users-and-visitors/service-user-participation');
$support_url = $permalink('about-us/support-us');

// --- Overview: bold + italic Swift quote ---
$overview_id = 216;
$overview = get_field('flexible_content_blocks', $overview_id);
if (is_array($overview)) {
    foreach ($overview as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'content') {
            continue;
        }
        if (trim((string) ($row['heading'] ?? '')) !== 'Our vision and mission') {
            continue;
        }
        $overview[$index]['intro_text'] = '<p><strong><em>“Vision is the art of seeing the invisible.”</em> – Jonathan Swift</strong></p>';
    }
    $save($overview_id, $overview, 'Overview Swift quote');
}

// --- Policies: charter button ---
$policies_id = 245;
$policies = get_field('flexible_content_blocks', $policies_id);
if (is_array($policies)) {
    foreach ($policies as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'content_accordion') {
            continue;
        }
        $items = $row['items'] ?? [];
        if (! is_array($items)) {
            continue;
        }
        foreach ($items as $item_index => $item) {
            if (trim((string) ($item['title'] ?? '')) !== 'Service user charter') {
                continue;
            }
            $rows = $item['content_rows'] ?? [];
            if (! is_array($rows)) {
                continue;
            }
            foreach ($rows as $row_index => $content_row) {
                $html = (string) ($content_row['content'] ?? '');
                $html = preg_replace(
                    '#<p>\s*<a[^>]*>See the service user charter here</a>\.?\s*</p>#i',
                    $charter_button,
                    $html
                ) ?? $html;
                $policies[$index]['items'][$item_index]['content_rows'][$row_index]['content'] = $html;
            }
        }
    }
    $save($policies_id, $policies, 'Policies charter button');
}

// --- Research: REC cards as anchors on the nested REC page ---
$research_id = 265;
$research = get_field('flexible_content_blocks', $research_id);
if (is_array($research)) {
    $rec_targets = [
        'role of the rec' => $rec_url . '#role-of-the-rec',
        'applications' => $rec_url . '#applications-to-the-rec',
        'governance' => $rec_url . '#contact',
        'rec membership' => $rec_url . '#apply-for-rec-membership',
    ];
    foreach ($research as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'research_cards_grid') {
            continue;
        }
        if (trim((string) ($row['heading'] ?? '')) !== 'Research Ethics Committee') {
            continue;
        }
        $cards = $row['cards'] ?? [];
        if (! is_array($cards)) {
            continue;
        }
        foreach ($cards as $card_index => $card) {
            $key = strtolower(trim((string) ($card['title'] ?? '')));
            if (! isset($rec_targets[$key])) {
                continue;
            }
            $link = is_array($card['link'] ?? null) ? $card['link'] : [];
            $link['url'] = $rec_targets[$key];
            $link['title'] = (string) ($link['title'] !== '' ? $link['title'] : $card['title']);
            $link['target'] = '_self';
            $research[$index]['cards'][$card_index]['link'] = $link;
        }
    }
    $save($research_id, $research, 'Research REC anchors');
}

// --- REC applications: tighten space between intro and accordion ---
$rec_page = get_page_by_path('about-us/research/research-ethics-committee');
if (! $rec_page instanceof WP_Post) {
    $rec_page = get_page_by_path('research-ethics-committee');
}
if ($rec_page instanceof WP_Post) {
    $rec_blocks = get_field('flexible_content_blocks', $rec_page->ID);
    if (is_array($rec_blocks)) {
        foreach ($rec_blocks as $index => $row) {
            $layout = $row['acf_fc_layout'] ?? '';
            $heading = trim((string) ($row['heading'] ?? ''));
            if ($layout === 'content' && $heading === 'Applications to the REC') {
                $rec_blocks[$index]['vertical_padding'] = 'no_bottom';
            }
            if ($layout === 'content_accordion') {
                $titles = array_map(
                    static fn ($item) => is_array($item) ? trim((string) ($item['title'] ?? '')) : '',
                    is_array($row['items'] ?? null) ? $row['items'] : []
                );
                if (in_array('What to prepare before you make an application', $titles, true)) {
                    $rec_blocks[$index]['vertical_padding'] = 'compact';
                }
            }
        }
        $save((int) $rec_page->ID, $rec_blocks, 'REC applications accordion spacing');
    }
}

// --- Advocacy hero image ---
$advocacy_id = 271;
$advocacy = get_field('flexible_content_blocks', $advocacy_id);
if (is_array($advocacy) && isset($advocacy[0]) && ($advocacy[0]['acf_fc_layout'] ?? '') === 'hero_with_breadcrumbs') {
    $apply_hero_image($advocacy[0], 4090);
    $save($advocacy_id, $advocacy, 'Advocacy hero image');
}

// --- Support us ---
$support_id = 195;
$support = get_field('flexible_content_blocks', $support_id);
if (is_array($support)) {
    $hero = $support[0] ?? [];
    $hero['content'] = $p('We believe everyone should have the opportunity to live mentally healthy lives.');

    $donations = $p('As an independent, not-for-profit organisation, St Patrick’s Mental Health Services (SPMHS) is primarily funded through our service users’ private health insurance. Donations and philanthropic support enable us to extend our impact beyond core service delivery.')
        . $p('Donations support:')
        . '<ul>'
        . '<li>education, awareness-raising, and advocacy initiatives</li>'
        . '<li>capital projects, including the development of a new national centre for mentally healthy living.</li>'
        . '</ul>';

    $ways_intro = $p('With your support, we can go beyond essential services and invest in education, awareness raising and long-term change in how society understands and supports mental health.')
        . $p('Every contribution helps extend our reach and deepen our impact.');
    $ways_row = matrix_orlaith_content_row('Ways to support our work', $ways_intro, 'white');
    $ways_row['vertical_padding'] = 'no_bottom';

    $contact_line = $p('To discuss any of these options, please contact <a href="mailto:supportus@stpatricks.ie">supportus@stpatricks.ie</a> or phone <a href="tel:+35312493916">01 249 3916</a>.');
    $ways_accordion = matrix_orlaith_accordion_row([
        'Make a donation' => $p('Make a once-off or regular gift to support our mental health education, awareness-raising and advocacy work, and our capital development projects.') . $contact_line,
        'Leave a legacy' => $p('Leave a gift in your will to help continue a tradition of philanthropy that has shaped our organisation for 280 years.') . $contact_line,
        'Partner with us' => $p('Work with us to create meaningful partnerships that align with your organisation’s values and deliver real social impact.') . $contact_line,
        'Fundraise for us' => $p('Take part in a challenge, host an event, or fundraise in your own way.') . $contact_line,
    ], 'default', '');
    $ways_accordion['vertical_padding'] = 'compact';

    $new_rows = [$hero];
    $has_donations_section = false;
    foreach (array_slice($support, 1) as $existing) {
        $html = strtolower(wp_strip_all_tags((string) ($existing['content'] ?? '')));
        if (($existing['acf_fc_layout'] ?? '') === 'content' && str_contains($html, 'donations support')) {
            $has_donations_section = true;
            break;
        }
    }
    if (! $has_donations_section) {
        $new_rows[] = matrix_orlaith_content_row('', $donations, 'white');
    }
    $added_ways_accordion = false;
    foreach (array_slice($support, 1) as $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        $heading = trim((string) ($row['heading'] ?? $row['heading_text'] ?? ''));

        if ($layout === 'content' && $heading === 'Ways to support our work') {
            $new_rows[] = $ways_row;
            $new_rows[] = $ways_accordion;
            $added_ways_accordion = true;
            continue;
        }

        if ($layout === 'content_accordion') {
            $titles = array_map(static fn($item): string => trim((string) ($item['title'] ?? '')), (array) ($row['items'] ?? []));
            if ($added_ways_accordion && in_array('Make a donation', $titles, true)) {
                continue;
            }
        }

        if ($layout === 'about_links_grid' && $heading === 'Ways to get involved') {
            $links = $row['links'] ?? [];
            if (is_array($links)) {
                foreach ($links as $link_index => $link) {
                    $title = strtolower(trim((string) ($link['title'] ?? '')));
                    $destination = $support_url;
                    if (str_contains($title, 'participation')) {
                        $destination = $participation_url;
                    } elseif (str_contains($title, 'legacy')) {
                        $destination = $support_url . '#leave-a-legacy';
                    } elseif (str_contains($title, 'fundrais')) {
                        $destination = $support_url . '#fundraise-for-us';
                    }
                    $row['links'][$link_index]['link'] = [
                        'title' => (string) ($link['title'] ?? $link['link']['title'] ?? ''),
                        'url' => $destination,
                        'target' => '',
                    ];
                }
            }
            $new_rows[] = $row;
            continue;
        }

        if ($layout === 'about_links_grid' && str_contains(strtolower($heading), 'engagement')) {
            $group_urls = [
                'suas' => $participation_url . '#service-user-and-supporters-council-suas',
                'suan' => $participation_url . '#service-user-advisory-network-suan',
                'fcs' => $participation_url . '#family-members-carers-and-supporters-fcs-advisory-network',
            ];
            $links = $row['links'] ?? [];
            if (is_array($links)) {
                foreach ($links as $link_index => $link) {
                    $title = strtolower(trim((string) ($link['title'] ?? '')));
                    $destination = $participation_url;
                    foreach ($group_urls as $needle => $url) {
                        if (str_contains($title, $needle)) {
                            $destination = $url;
                            break;
                        }
                    }
                    $row['links'][$link_index]['link'] = [
                        'title' => (string) ($link['title'] ?? $link['link']['title'] ?? ''),
                        'url' => $destination,
                        'target' => '',
                    ];
                }
            }
            $new_rows[] = $row;
            continue;
        }

        $new_rows[] = $row;
    }

    $save($support_id, $new_rows, 'Support us layout');
}

// --- Our locations: image, spacing, accordion placement ---
$locations_id = 282;
$locations = get_field('flexible_content_blocks', $locations_id);
if (is_array($locations)) {
    $hero = $locations[0] ?? [];
    $img_id = $hero_image_id($hero);
    if ($img_id <= 0) {
        $img_id = 3888;
    }
    $apply_hero_image($hero, $img_id);
    $locations[0] = $hero;

    $reordered = [];
    $accessibility = null;
    $accordion = null;
    foreach ($locations as $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        $heading = trim((string) ($row['heading'] ?? ''));
        if ($layout === 'content' && $heading === '') {
            $row['vertical_padding'] = 'no_bottom';
        }
        if ($layout === 'locations_grid') {
            $row['vertical_padding'] = 'no_top';
            $row['heading'] = 'Find us';
        }
        if ($layout === 'content' && $heading === 'Accessibility') {
            $row['vertical_padding'] = 'no_bottom';
            $accessibility = $row;
            continue;
        }
        if ($layout === 'content_accordion' && $accessibility !== null && $accordion === null) {
            $row['vertical_padding'] = 'compact';
            $accordion = $row;
            continue;
        }
        $reordered[] = $row;
        if ($accessibility !== null && $accordion !== null && $heading === 'Accessibility') {
            // already skipped
        }
    }

    $final = [];
    $inserted = false;
    foreach ($reordered as $row) {
        $heading = trim((string) ($row['heading'] ?? ''));
        $layout = $row['acf_fc_layout'] ?? '';
        if (! $inserted && $layout === 'content' && $heading === 'Our outpatient clinics') {
            $final[] = $row;
            if (is_array($accessibility)) {
                $final[] = $accessibility;
            }
            if (is_array($accordion)) {
                $final[] = $accordion;
            }
            $inserted = true;
            continue;
        }
        $final[] = $row;
    }
    if (! $inserted) {
        if (is_array($accessibility)) {
            $final[] = $accessibility;
        }
        if (is_array($accordion)) {
            $final[] = $accordion;
        }
    }

    $save($locations_id, $final, 'Our locations spacing and hero');
}

// --- Present and future: less space above objectives ---
$future_id = 278;
$future = get_field('flexible_content_blocks', $future_id);
if (is_array($future)) {
    foreach ($future as $index => $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        $heading = trim((string) ($row['heading'] ?? ''));
        if ($layout === 'content' && $heading === 'Our strategy today') {
            $future[$index]['vertical_padding'] = 'no_bottom';
        }
        if ($layout === 'content_accordion' && $heading === 'Our objectives for 2023 to 2027') {
            $future[$index]['vertical_padding'] = 'compact';
        }
    }
    $save($future_id, $future, 'Present and future objectives spacing');
}

// --- Day programmes: split fake H2 ---
$day_id = 252;
$day = get_field('flexible_content_blocks', $day_id);
if (is_array($day)) {
    $new_rows = [];
    foreach ($day as $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        $heading = trim((string) ($row['heading'] ?? ''));
        if ($layout === 'content' && $heading === 'What to expect') {
            $content = (string) ($row['content'] ?? '');
            if (! str_contains($content, 'H2: Who are our day programmes suitable for')) {
                $new_rows[] = $row;
                continue;
            }
            $parts = preg_split(
                '#(?:<p[^>]*>)?(?:<strong>\s*)?(?:<br\s*/?>\s*)*H2:\s*Who are our day programmes suitable for\s*(?:</strong>)?\s*(?:</p>)?#i',
                $content,
                2
            );
            $expect = $parts[0] ?? $content;
            $expect = preg_replace('#<p>\s*<strong>\s*<br\s*/?>\s*</strong>\s*</p>#i', '', $expect) ?? $expect;
            $expect = preg_replace('#<br\s*/?>\s*$#i', '', trim($expect)) ?? $expect;
            if ($expect !== '' && ! preg_match('#</p>\s*$#i', $expect)) {
                $expect .= '</p>';
            }
            $row['content'] = $expect;
            $new_rows[] = $row;

            $suitable = $parts[1] ?? '';
            if (trim(strip_tags($suitable)) !== '') {
                $suitable_row = matrix_orlaith_content_row('Who are our day programmes suitable for', $suitable, 'cream');
                $suitable_row['heading_tag'] = 'h2';
                $new_rows[] = $suitable_row;
            }
            continue;
        }
        $new_rows[] = $row;
    }
    $save($day_id, $new_rows, 'Day programmes suitable-for heading');
}

// --- Clinician insights: drop duplicate hero copy; drop Culture to Value if present ---
$insights_id = 267;
$insights = get_field('flexible_content_blocks', $insights_id);
if (is_array($insights)) {
    $cleaned = [];
    foreach ($insights as $index => $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        $heading = trim((string) ($row['heading'] ?? ''));
        $blob = strtolower(wp_strip_all_tags((string) ($row['content'] ?? '') . (string) ($row['intro_text'] ?? '') . $heading));

        if ($index > 0 && $layout === 'content' && $heading === 'Clinician insights') {
            continue;
        }
        if (str_contains($blob, 'a culture to value')) {
            continue;
        }
        $cleaned[] = $row;
    }
    $save($insights_id, $cleaned, 'Clinician insights duplicate copy');
}

WP_CLI::success('Round 3 snag updates complete.');
