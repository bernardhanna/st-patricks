<?php

/**
 * Apply Service Users and Visitors snag-list content fixes.
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/fix-service-users-snags.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';

$permalink = static function (string $path): string {
    $page = get_page_by_path($path);

    return $page instanceof WP_Post ? (string) get_permalink($page) : home_url('/' . trim($path, '/') . '/');
};
$save = static function (int $post_id, array $rows, string $label): void {
    update_field('flexible_content_blocks', $rows, $post_id);
    WP_CLI::success($label . ' #' . $post_id);
};
$strip_trailing_link_paragraph = static function (string $html): string {
    $html = preg_replace('#<p>\s*<a[^>]*>.*?</a>\s*\.?\s*</p>\s*$#is', '', $html) ?? $html;

    return trim($html);
};

$contact_url = $permalink('contact-us');
$survey_url = $permalink('service-users-and-visitors/feedback-and-comments/service-user-experience-survey');
$participation_url = $permalink('service-users-and-visitors/service-user-participation');
$newsletter_url = (string) wp_get_attachment_url(812);
$safety_article_url = (string) get_permalink(1218);

// --- Landing: remove placeholder section titles ---
$landing_id = 243;
$landing = get_field('flexible_content_blocks', $landing_id);
if (is_array($landing)) {
    foreach ($landing as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'research_cards_grid') {
            continue;
        }
        $heading = trim((string) ($row['heading'] ?? ''));
        if (str_contains($heading, 'placeholder') || str_contains($heading, 'Your account')) {
            $landing[$index]['heading'] = 'Your account';
        }
        if (str_contains($heading, 'Placeholder') || str_contains($heading, 'Your Stay')) {
            $landing[$index]['heading'] = 'Your stay';
        }
    }
    $save($landing_id, $landing, 'Service users landing headings');
}

// --- Directions: useful links without underline; map button to contact map ---
$directions_id = 233;
$directions = get_field('flexible_content_blocks', $directions_id);
if (is_array($directions)) {
    foreach ($directions as $index => $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        if ($layout === 'hero_with_breadcrumbs') {
            $directions[$index]['primary_button'] = matrix_orlaith_button('Our Locations Map', $contact_url);
        }
        if ($layout === 'useful_links') {
            $directions[$index]['variant'] = 'flexi';
        }
    }
    $save($directions_id, $directions, 'Directions map CTA and useful links');
}

// --- Adult stay: more space above accordions, less below ---
$stay_id = 255;
$stay = get_field('flexible_content_blocks', $stay_id);
if (is_array($stay)) {
    foreach ($stay as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'content_accordion') {
            continue;
        }
        $stay[$index]['vertical_padding'] = 'compact';
    }
    $save($stay_id, $stay, 'Adult stay accordion padding');
}

// --- Homecare: less space above How referrals work ---
$home_id = 266;
$home = get_field('flexible_content_blocks', $home_id);
if (is_array($home)) {
    foreach ($home as $index => $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        $heading = trim((string) ($row['heading'] ?? ''));
        if ($layout === 'content' && in_array($heading, [
            'Getting started in homecare',
            'Receiving care',
            'Making the most of homecare',
        ], true)) {
            $home[$index]['vertical_padding'] = 'no_bottom';
        }
        if ($layout === 'content_accordion') {
            $home[$index]['vertical_padding'] = 'compact';
        }
    }
    $save($home_id, $home, 'Homecare accordion padding');
}

// --- Participation: full-width intro + CTA buttons ---
$part_id = 284;
$part = get_field('flexible_content_blocks', $part_id);
if (is_array($part)) {
    $hero = $part[0] ?? [];
    $hero_html = (string) ($hero['content'] ?? '');
    $paragraphs = [];
    if (preg_match_all('#<p\b[^>]*>.*?</p>#is', $hero_html, $matches) === 1 || isset($matches[0])) {
        $paragraphs = $matches[0] ?? [];
    }
    if ($paragraphs !== []) {
        $hero['content'] = $paragraphs[0];
        $part[0] = $hero;
        $overview_html = implode('', array_slice($paragraphs, 1));
        $has_overview = false;
        foreach ($part as $row) {
            if (($row['acf_fc_layout'] ?? '') === 'content' && trim((string) ($row['heading'] ?? '')) === '') {
                $has_overview = true;
                break;
            }
        }
        if ($overview_html !== '' && ! $has_overview) {
            array_splice($part, 1, 0, [matrix_orlaith_content_row('', $overview_html, 'white')]);
        }
    }

    $cta_map = [
        'Service User and Supporters Council (SUAS)' => [
            'title' => 'Find out more about SUAS',
            'needle' => 'service-user-and-supporters-council',
        ],
        'Service User Advisory Network (SUAN)' => [
            'title' => 'Learn more about SUAN',
            'needle' => 'service-user-advisory-network',
        ],
        'Family Members, Carers and Supporters (FCS) Advisory Network' => [
            'title' => 'See more on the FCS Advisory Network',
            'needle' => 'family-carers-and-supporters-advisory-network',
        ],
        'Service user experience surveys' => [
            'title' => 'Take the surveys here',
            'needle' => 'service-user-experience-survey',
        ],
    ];

    foreach ($part as $index => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'content') {
            continue;
        }
        $heading = trim((string) ($row['heading'] ?? ''));
        if (! isset($cta_map[$heading])) {
            continue;
        }
        $content = (string) ($row['content'] ?? '');
        $url = '';
        if (preg_match('#href="([^"]+)"#i', $content, $href)) {
            $url = html_entity_decode($href[1]);
        }
        if ($url === '' && $heading === 'Service user experience surveys') {
            $url = $survey_url;
        }
        $part[$index]['content'] = $strip_trailing_link_paragraph($content);
        if ($url !== '') {
            $part[$index]['primary_button'] = matrix_orlaith_button($cta_map[$heading]['title'], $url);
            $part[$index]['primary_button_variant'] = 'filled';
        }
        $part[$index]['text_width'] = 'full';
    }
    $save($part_id, $part, 'Service user participation CTAs');
}

// --- Medication: heading + working useful links ---
$med_id = 283;
$med = get_field('flexible_content_blocks', $med_id);
if (is_array($med)) {
    foreach ($med as $index => $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        $heading = trim((string) ($row['heading'] ?? ''));
        $content = (string) ($row['content'] ?? '');
        if ($layout === 'content' && ($heading === '' || $heading === 'Pregnancy and valproate: Prevent programme') && str_contains($content, 'valproate')) {
            $med[$index]['heading'] = 'Pregnancy and valproate: Prevent programme';
            $med[$index]['heading_tag'] = 'h2';
            $med[$index]['intro_text'] = '';
        }
        if ($layout === 'useful_links') {
            $med[$index]['variant'] = 'flexi';
            $links = [];
            if ($safety_article_url !== '') {
                $links['Medication safety'] = $safety_article_url;
            }
            if ($newsletter_url !== '') {
                $links['Medication Safety Newsletter'] = $newsletter_url;
            }
            if ($links !== []) {
                $med[$index] = matrix_orlaith_useful_links_row($links);
                if ($newsletter_url !== '') {
                    foreach ($med[$index]['links'] as $link_index => $link_row) {
                        $title = (string) ($link_row['link']['title'] ?? '');
                        if ($title === 'Medication Safety Newsletter') {
                            $med[$index]['links'][$link_index]['link']['target'] = '_blank';
                        }
                    }
                }
            }
        }
    }
    $save($med_id, $med, 'Medication heading and useful links');
}

// --- Feedback: more space above How to give feedback ---
$feedback_id = 285;
$feedback = get_field('flexible_content_blocks', $feedback_id);
if (is_array($feedback)) {
    $welcome_html = '<p>At SPMHS, we welcome feedback on all aspects of our services.</p>'
        . '<p>We recognise the importance of receiving continuous <a href="' . esc_url($participation_url) . '">feedback from our service users</a> and everyone involved in our services and activities. We are also committed to the protection of children and vulnerable adults.</p>'
        . '<p>Feedback enables us to continue to deliver a high quality service and to place the welfare and needs of service users at the forefront of all our activities.</p>'
        . '<p>We are committed to ensuring that all complaints and comments are given fair consideration. All feedback is provided to the most appropriate senior manager and, where appropriate, is investigated and resolved.</p>';

    $has_welcome = false;
    $cleaned = [];
    foreach ($feedback as $row) {
        $layout = $row['acf_fc_layout'] ?? '';
        $heading = trim((string) ($row['heading'] ?? ''));
        if ($layout === 'useful_links') {
            continue;
        }
        if ($layout === 'content' && $heading === '') {
            $has_welcome = true;
            $row['content'] = $welcome_html;
            $row['vertical_padding'] = 'no_bottom';
        }
        if ($layout === 'content' && $heading === 'How to give feedback or make a complaint') {
            $row['vertical_padding'] = 'default';
        }
        $cleaned[] = $row;
    }

    if (! $has_welcome) {
        $insert_at = 1;
        array_splice($cleaned, $insert_at, 0, [matrix_orlaith_content_row('', $welcome_html, 'white', 0, 'image_left', [
            'vertical_padding' => 'no_bottom',
        ])]);
    }

    $cleaned[] = matrix_orlaith_useful_links_row([
        'Service User Participation' => $participation_url,
        'Service User Experience Survey' => $survey_url,
        'Frequently Asked Questions' => $permalink('service-users-and-visitors/frequently-asked-questions-faqs'),
    ]);

    $save($feedback_id, $cleaned, 'Feedback spacing and useful links');
}
