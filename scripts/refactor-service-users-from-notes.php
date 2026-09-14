<?php

/**
 * Apply Service Users refactor notes from old/content/Service Users refactor.md
 *
 * wp eval-file wp-content/themes/matrix-starter/scripts/refactor-service-users-from-notes.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once get_template_directory() . '/scripts/lib/page-seed-conventions.php';

if (! function_exists('matrix_seed_su_faq_ensure_term')) {
    function matrix_seed_su_faq_ensure_term(string $slug, string $name, int $parent_id = 0): int
    {
        $existing = get_term_by('slug', $slug, 'faq_category');
        if ($existing instanceof WP_Term) {
            if ($parent_id > 0 && (int) $existing->parent !== $parent_id) {
                wp_update_term((int) $existing->term_id, 'faq_category', ['parent' => $parent_id]);
            }

            return (int) $existing->term_id;
        }
        $args = ['slug' => $slug];
        if ($parent_id > 0) {
            $args['parent'] = $parent_id;
        }
        $created = wp_insert_term($name, 'faq_category', $args);

        return is_wp_error($created) ? 0 : (int) ($created['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_seed_su_faq_ensure_post')) {
    function matrix_seed_su_faq_ensure_post(string $title, string $content, string $seed_key, array $term_ids, int $menu_order = 0): int
    {
        $existing = get_posts([
            'post_type' => 'faqs',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'meta_query' => [[
                'key' => '_matrix_seed_key',
                'value' => $seed_key,
            ]],
        ]);
        if ($existing !== []) {
            $faq_id = (int) $existing[0]->ID;
            wp_update_post([
                'ID' => $faq_id,
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'menu_order' => $menu_order,
            ]);
        } else {
            $faq_id = (int) wp_insert_post([
                'post_type' => 'faqs',
                'post_status' => 'publish',
                'post_title' => $title,
                'post_content' => $content,
                'menu_order' => $menu_order,
            ]);
            if ($faq_id < 1) {
                return 0;
            }
            update_post_meta($faq_id, '_matrix_seed_key', $seed_key);
        }
        if ($term_ids !== []) {
            wp_set_object_terms($faq_id, array_map('intval', $term_ids), 'faq_category', false);
        }

        return $faq_id;
    }
}

$home = untrailingslashit(home_url('/'));
$call_tel = 'tel:012493200';
$suas_url = $home . '/get-involved/service-user-participation/service-user-and-supporters-council-suas/';
$carers_url = $home . '/service-users-and-visitors/carers-and-supporters/';
$su_faqs_url = $home . '/service-users-and-visitors/frequently-asked-questions-faqs/';

$ensure_term = static function (string $slug, string $name, int $parent = 0): int {
    return matrix_seed_su_faq_ensure_term($slug, $name, $parent);
};

$ensure_faq = static function (string $title, string $content, string $key, array $term_ids, int $order = 0): int {
    return matrix_seed_su_faq_ensure_post($title, $content, $key, $term_ids, $order);
};

$faq_row = static function (string $heading, array $faq_ids, bool $show_heading = true): array {
    return [
        'acf_fc_layout' => 'faqs',
        'show_heading' => $show_heading ? 1 : 0,
        'layout_style' => 'default',
        'heading' => $heading,
        'heading_tag' => 'h2',
        'source_mode' => 'selected',
        'selected_faqs' => array_values(array_filter(array_map('intval', $faq_ids))),
        'section_background' => '#FBFAF7',
        'heading_color' => '#1E244B',
        'underline_color' => '#6FC9C0',
        'item_background' => '#FFFFFF',
        'open_item_background' => 'linear-gradient(-42.77deg, #F8F6F3 3.24%, #F5F6ED 90.88%)',
        'question_color' => '#1E244B',
        'answer_color' => '#08284B',
    ];
};

/**
 * Parse Q&A pairs from HTML using h3 or strong-as-question patterns.
 *
 * @return list<array{0:string,1:string}>
 */
$parse_qa = static function (string $html): array {
    $pairs = [];
    if (preg_match_all('#<h3[^>]*>(.*?)</h3>(.*?)(?=<h3\b|$)#is', $html, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $q = trim(wp_strip_all_tags($match[1]));
            $a = trim($match[2]);
            if ($q !== '' && $a !== '') {
                $pairs[] = [$q, $a];
            }
        }
        if ($pairs !== []) {
            return $pairs;
        }
    }
    if (preg_match_all('#<p>\s*<strong>(.*?)</strong>\s*</p>\s*(.*?)(?=<p>\s*<strong>|$)#is', $html, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $q = trim(wp_strip_all_tags($match[1]));
            $a = trim($match[2]);
            if ($q !== '' && $a !== '') {
                $pairs[] = [$q, $a];
            }
        }
    }

    return $pairs;
};

$externalise_links = static function (string $html) use ($home): string {
    return (string) preg_replace_callback(
        '#<a\s([^>]*?)href=("|\')(https?://[^"\']+)\2([^>]*)>#i',
        static function ($m) use ($home) {
            $url = html_entity_decode($m[3]);
            $local = untrailingslashit(home_url('/'));
            $is_local = str_starts_with($url, $local) || str_starts_with($url, 'http://localhost');
            $attrs = $m[1] . $m[4];
            if ($is_local) {
                return '<a ' . trim($attrs) . ' href="' . esc_url($url) . '">';
            }
            if (! preg_match('/\btarget=/i', $attrs)) {
                $attrs .= ' target="_blank" rel="noopener noreferrer"';
            } elseif (! preg_match('/\brel=/i', $attrs)) {
                $attrs .= ' rel="noopener noreferrer"';
            }

            return '<a ' . trim($attrs) . ' href="' . esc_url($url) . '">';
        },
        $html
    );
};

$replace_live_links = static function (string $html) use ($home, $suas_url, $carers_url): string {
    $map = [
        'https://www.stpatricks.ie/get-involved/service-user-participation/service-user-and-supporters-council-suas' => $suas_url,
        'https://www.stpatricks.ie/getting-help/carers-supporters' => $carers_url,
        'https://www.stpatricks.ie/care-treatment/your-portal/service-user-it-support' => $home . '/service-user-it-support/',
        'https://www.stpatricks.ie/care-treatment/our-services/remote-services/practical-information-remote-services' => $home . '/practical-information-for-remote-services/',
        'https://www.stpatricks.ie/care-treatment/medication' => $home . '/medication/',
        'https://www.stpatricks.ie/about-us/multidisciplinary-teams/pharmacy' => $home . '/about-us/pharmacy/',
        'https://www.stpatricks.ie/about-us' => $home . '/about-us/',
        'https://www.stpatricks.ie/privacy-notice' => $home . '/data-protection-policy/',
    ];
    foreach ($map as $from => $to) {
        $html = str_replace([$from, rtrim($from, '/')], rtrim($to, '/'), $html);
    }

    return $html;
};

$save_rows = static function (int $post_id, array $rows): void {
    update_field('flexible_content_blocks', $rows, $post_id);
    WP_CLI::log('Updated #' . $post_id . ' ' . get_the_title($post_id) . ' (' . count($rows) . ' blocks)');
};

$parent_term = $ensure_term('service-users-and-visitors', 'Service Users and Visitors');

// ---------------------------------------------------------------------------
// 1) About mental health FAQs
// ---------------------------------------------------------------------------
$about_id = (int) (get_page_by_path('service-users-and-visitors/about-mental-health')?->ID ?? 0);
if ($about_id > 0) {
    $term = $ensure_term('about-mental-health-faqs', 'About mental health', $parent_term);
    $rows = get_field('flexible_content_blocks', $about_id) ?: [];
    $faq_ids = [];
    $new = [];
    foreach ($rows as $row) {
        $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
        if (($row['acf_fc_layout'] ?? '') === 'content' && stripos($heading, 'Frequently Asked') !== false) {
            foreach ($parse_qa((string) $row['content']) as $i => [$q, $a]) {
                $faq_ids[] = $ensure_faq($q, $replace_live_links($a), 'su-about-mh-' . ($i + 1), [$term, $parent_term], $i + 1);
            }
            $new[] = $faq_row('Frequently Asked Questions (FAQs)', $faq_ids);
            continue;
        }
        $new[] = $row;
    }
    $save_rows($about_id, $new);
}

// ---------------------------------------------------------------------------
// 2) Attending Dean Clinic + Day programmes: FAQ block + Call us button
// ---------------------------------------------------------------------------
$queries_to_call = static function (array $row) use ($call_tel, $su_faqs_url): array {
    $row['content'] = '<p>For general queries, please call us. For more on mental health and our services, <a href="' . esc_url($su_faqs_url) . '">see our frequently asked questions (FAQs)</a>.</p>';
    $row['primary_button'] = [
        'title' => 'Call us',
        'url' => $call_tel,
        'target' => '',
    ];
    $row['primary_button_variant'] = 'filled';
    $row['secondary_button'] = ['title' => '', 'url' => '', 'target' => ''];

    return $row;
};

foreach (
    [
        [
            'path' => 'service-users-and-visitors/attending-a-dean-clinic',
            'term_slug' => 'attending-dean-clinic-faqs',
            'term_name' => 'Attending a Dean Clinic',
            'key' => 'su-dean',
            'heading' => 'Frequently asked questions',
        ],
        [
            'path' => 'service-users-and-visitors/attending-day-programmes',
            'term_slug' => 'attending-day-programmes-faqs',
            'term_name' => 'Attending day programmes',
            'key' => 'su-day',
            'heading' => 'Frequently asked questions',
        ],
    ] as $spec
) {
    $page_id = (int) (get_page_by_path($spec['path'])?->ID ?? 0);
    if ($page_id <= 0) {
        continue;
    }
    $term = $ensure_term($spec['term_slug'], $spec['term_name'], $parent_term);
    $rows = get_field('flexible_content_blocks', $page_id) ?: [];
    $new = [];
    foreach ($rows as $row) {
        $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        if ($layout === 'content' && stripos($heading, 'Frequently') !== false) {
            $html = $replace_live_links((string) $row['content']);
            $intro = '';
            if (preg_match('#^(.*?)(?=<h3\b)#is', $html, $im)) {
                $intro = trim($im[1]);
            }
            $faq_ids = [];
            foreach ($parse_qa($html) as $i => [$q, $a]) {
                $faq_ids[] = $ensure_faq($q, $a, $spec['key'] . '-' . ($i + 1), [$term, $parent_term], $i + 1);
            }
            if ($intro !== '' && trim(wp_strip_all_tags($intro)) !== '') {
                $new[] = matrix_orlaith_content_row($spec['heading'], $intro, 'cream');
            }
            $new[] = $faq_row($spec['heading'], $faq_ids, $intro === '');
            continue;
        }
        if ($layout === 'content' && stripos($heading, 'Queries') !== false) {
            $new[] = $queries_to_call($row);
            continue;
        }
        if (! empty($row['content'])) {
            $row['content'] = $replace_live_links((string) $row['content']);
        }
        $new[] = $row;
    }
    $save_rows($page_id, $new);
}

// ---------------------------------------------------------------------------
// 3) Carers and Supporters
// ---------------------------------------------------------------------------
$carers_id = (int) (get_page_by_path('service-users-and-visitors/carers-and-supporters')?->ID ?? 0);
if ($carers_id > 0) {
    $rows = get_field('flexible_content_blocks', $carers_id) ?: [];
    foreach ($rows as &$row) {
        if (empty($row['content'])) {
            continue;
        }
        $html = $replace_live_links((string) $row['content']);
        $html = preg_replace('#<p>\s*<a[^>]*>Download the Carers and Supporters Information Guide here</a>\s*\.?\s*</p>#i', '', $html);
        $row['content'] = $html;
    }
    unset($row);
    $save_rows($carers_id, $rows);
}

// ---------------------------------------------------------------------------
// 4) Depression useful resources -> accordion + external new tab
// ---------------------------------------------------------------------------
$dep_id = 1452;
$rows = get_field('flexible_content_blocks', $dep_id) ?: [];
$new = [];
foreach ($rows as $row) {
    $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
    if (($row['acf_fc_layout'] ?? '') === 'content' && stripos($heading, 'Useful resources') !== false) {
        $html = $externalise_links($replace_live_links((string) $row['content']));
        $items = [];
        foreach ($parse_qa(preg_replace('#<h3#', '<h3', $html) ?: $html) as [$q, $a]) {
            $items[$q] = $a;
        }
        if ($items === [] && preg_match_all('#<h3[^>]*>(.*?)</h3>(.*?)(?=<h3\b|$)#is', $html, $m, PREG_SET_ORDER)) {
            foreach ($m as $match) {
                $items[trim(wp_strip_all_tags($match[1]))] = trim($match[2]);
            }
        }
        $new[] = matrix_orlaith_accordion_row($items, 'default', 'Useful resources');
        continue;
    }
    if (! empty($row['content'])) {
        $row['content'] = $externalise_links($replace_live_links((string) $row['content']));
    }
    $new[] = $row;
}
$save_rows($dep_id, $new);

// ---------------------------------------------------------------------------
// 5) FCS Advisory Network local links
// ---------------------------------------------------------------------------
$fcs_id = (int) (get_page_by_path('service-users-and-visitors/service-user-participation/family-carers-and-supporters-advisory-network')?->ID ?? 0);
if ($fcs_id > 0) {
    $rows = get_field('flexible_content_blocks', $fcs_id) ?: [];
    foreach ($rows as &$row) {
        if (! empty($row['content'])) {
            $row['content'] = $replace_live_links((string) $row['content']);
        }
    }
    unset($row);
    $save_rows($fcs_id, $rows);
}

// ---------------------------------------------------------------------------
// 6) Service Users FAQs landing -> real faqs flexi
// ---------------------------------------------------------------------------
$faqs_page = (int) (get_page_by_path('service-users-and-visitors/frequently-asked-questions-faqs')?->ID ?? 0);
if ($faqs_page > 0) {
    $term = $ensure_term('service-users-faqs-page', 'Service users FAQs page', $parent_term);
    $rows = get_field('flexible_content_blocks', $faqs_page) ?: [];
    $hero = $rows[0] ?? matrix_orlaith_hero_row('FAQs for service users', '', 0);
    $faq_ids = [];
    $order = 0;
    foreach ($rows as $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'content') {
            continue;
        }
        $heading = trim(wp_strip_all_tags((string) ($row['heading'] ?? '')));
        $content = trim((string) ($row['content'] ?? ''));
        if ($heading === '' || stripos($heading, 'FAQs for service users') !== false) {
            continue;
        }
        $order++;
        $faq_ids[] = $ensure_faq($heading, $replace_live_links($content), 'su-faqs-page-' . $order, [$term, $parent_term], $order);
    }
    $save_rows($faqs_page, [
        $hero,
        $faq_row('FAQs', $faq_ids, false) + ['layout_style' => 'page', 'show_heading' => 0],
    ]);
}

// ---------------------------------------------------------------------------
// 7) Information for your family FAQs
// ---------------------------------------------------------------------------
$family_id = (int) (get_page_by_path('service-users-and-visitors/your-care-with-willow-grove/information-for-your-family')?->ID ?? 0);
if ($family_id > 0) {
    $term = $ensure_term('willow-family-faqs', 'Information for your family', $parent_term);
    $rows = get_field('flexible_content_blocks', $family_id) ?: [];
    $new = [];
    foreach ($rows as $row) {
        $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
        if (($row['acf_fc_layout'] ?? '') === 'content' && strcasecmp($heading, 'FAQs') === 0) {
            $html = $replace_live_links((string) $row['content']);
            $intro = '';
            if (preg_match('#^(.*?)(?=<h3\b)#is', $html, $im)) {
                $intro = trim($im[1]);
            }
            $faq_ids = [];
            foreach ($parse_qa($html) as $i => [$q, $a]) {
                $faq_ids[] = $ensure_faq($q, $a, 'su-family-' . ($i + 1), [$term, $parent_term], $i + 1);
            }
            if ($intro !== '') {
                $new[] = matrix_orlaith_content_row('FAQs', $intro, 'cream');
            }
            $new[] = $faq_row('FAQs', $faq_ids, $intro === '');
            continue;
        }
        if (! empty($row['content'])) {
            $row['content'] = $replace_live_links((string) $row['content']);
        }
        $new[] = $row;
    }
    $save_rows($family_id, $new);
}

// ---------------------------------------------------------------------------
// 8) Schizophrenia FAQs
// ---------------------------------------------------------------------------
$sch_posts = get_posts(['post_type' => 'mental_health', 'name' => 'schizophrenia', 'post_status' => 'any', 'posts_per_page' => 1]);
$sch_id = $sch_posts !== [] ? (int) $sch_posts[0]->ID : 0;
if ($sch_id > 0) {
    $term = $ensure_term('schizophrenia-faqs', 'Schizophrenia', $parent_term);
    $rows = get_field('flexible_content_blocks', $sch_id) ?: [];
    $new = [];
    foreach ($rows as $row) {
        $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
        if (($row['acf_fc_layout'] ?? '') === 'content' && stripos($heading, 'Frequently asked') !== false) {
            $faq_ids = [];
            foreach ($parse_qa($replace_live_links((string) $row['content'])) as $i => [$q, $a]) {
                $faq_ids[] = $ensure_faq($q, $a, 'su-schizophrenia-' . ($i + 1), [$term, $parent_term], $i + 1);
            }
            $new[] = $faq_row('Frequently asked questions about schizophrenia', $faq_ids);
            continue;
        }
        $new[] = $row;
    }
    $save_rows($sch_id, $new);
}

// ---------------------------------------------------------------------------
// 9) SUAN + SUAS: question sections -> FAQ block; useful links at bottom
// ---------------------------------------------------------------------------
$convert_question_sections = static function (
    int $post_id,
    string $term_slug,
    string $term_name,
    string $key_prefix,
    array $useful_links
) use ($ensure_term, $ensure_faq, $faq_row, $save_rows, $parent_term, $replace_live_links): void {
    if ($post_id <= 0) {
        return;
    }
    $term = $ensure_term($term_slug, $term_name, $parent_term);
    $rows = get_field('flexible_content_blocks', $post_id) ?: [];
    $hero = null;
    $faq_ids = [];
    $other = [];
    $order = 0;
    foreach ($rows as $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '');
        $heading = trim(wp_strip_all_tags((string) ($row['heading'] ?? '')));
        if ($layout === 'hero_with_breadcrumbs') {
            $hero = $row;
            continue;
        }
        if (in_array($layout, ['key_contact_info', 'related_cards'], true)) {
            // Drop queries/referrals-style contact/related blocks.
            continue;
        }
        if ($layout === 'useful_links' || stripos($heading, 'In this section') !== false || stripos($heading, 'Useful links') !== false) {
            continue;
        }
        if ($layout === 'content' && $heading !== '' && (str_ends_with($heading, '?') || preg_match('/^(What|Who|How|What’s|Whats)\b/i', $heading))) {
            $order++;
            $faq_ids[] = $ensure_faq(
                $heading,
                $replace_live_links((string) ($row['content'] ?? '')),
                $key_prefix . '-' . $order,
                [$term, $parent_term],
                $order
            );
            continue;
        }
        if ($layout === 'contact_form' || $layout === 'stories' || $layout === 'video_showcase') {
            $other[] = $row;
            continue;
        }
        if ($layout === 'content') {
            if (! empty($row['content'])) {
                $row['content'] = $replace_live_links((string) $row['content']);
            }
            $other[] = $row;
        }
    }
    $out = [];
    if (is_array($hero)) {
        $out[] = $hero;
    }
    if ($faq_ids !== []) {
        $out[] = $faq_row('Frequently asked questions', $faq_ids);
    }
    foreach ($other as $row) {
        $out[] = $row;
    }
    if ($useful_links !== []) {
        $out[] = matrix_orlaith_useful_links_row($useful_links, 'Useful links');
    }
    $save_rows($post_id, $out);
};

$convert_question_sections(
    (int) (get_page_by_path('get-involved/service-user-participation/service-user-advisory-network-suan')?->ID ?? 0),
    'suan-faqs',
    'SUAN',
    'su-suan',
    [
        'Service User Participation' => 'service-users-and-visitors/service-user-participation',
        'SUAS' => 'get-involved/service-user-participation/service-user-and-supporters-council-suas',
        'Carers and Supporters' => 'service-users-and-visitors/carers-and-supporters',
        'FAQs' => 'service-users-and-visitors/frequently-asked-questions-faqs',
    ]
);

$convert_question_sections(
    (int) (get_page_by_path('get-involved/service-user-participation/service-user-and-supporters-council-suas')?->ID ?? 0),
    'suas-faqs',
    'SUAS',
    'su-suas',
    [
        'Service User Participation' => 'service-users-and-visitors/service-user-participation',
        'SUAN' => 'get-involved/service-user-participation/service-user-advisory-network-suan',
        'FCS Advisory Network' => 'service-users-and-visitors/service-user-participation/family-carers-and-supporters-advisory-network',
        'FAQs' => 'service-users-and-visitors/frequently-asked-questions-faqs',
    ]
);

// ---------------------------------------------------------------------------
// 10) Service User Experience Surveys -> accordion
// ---------------------------------------------------------------------------
$survey_id = (int) (get_page_by_path('service-users-and-visitors/feedback-and-comments/service-user-experience-survey')?->ID ?? 0);
if ($survey_id > 0) {
    $rows = get_field('flexible_content_blocks', $survey_id) ?: [];
    $new = [];
    foreach ($rows as $row) {
        $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
        if (($row['acf_fc_layout'] ?? '') === 'content' && strcasecmp($heading, 'Surveys') === 0) {
            $html = $externalise_links((string) $row['content']);
            $intro = '';
            if (preg_match('#^(.*?)(?=<h3\b)#is', $html, $im)) {
                $intro = trim($im[1]);
            }
            $items = [];
            foreach ($parse_qa($html) as [$q, $a]) {
                $items[$q] = $a;
            }
            if ($intro !== '') {
                $new[] = matrix_orlaith_content_row('Surveys', $intro, 'white');
            }
            $new[] = matrix_orlaith_accordion_row($items, 'default', $intro === '' ? 'Surveys' : '');
            continue;
        }
        $new[] = $row;
    }
    $save_rows($survey_id, $new);
}

// ---------------------------------------------------------------------------
// 11) Young adult mental health page + CPT — restore missing sections
// ---------------------------------------------------------------------------
$ya_common = '<p>Common mental health difficulties in young adults include:</p><ul>'
    . '<li>Anxiety</li><li>Bipolar disorder</li><li>Depression</li><li>Eating disorders</li>'
    . '<li>Personality disorders</li><li>Psychosis</li><li>Schizophrenia</li><li>Substance dependence.</li></ul>';
$ya_recovery = '<p>Recovery can sometimes feel slow and frustrating, and setbacks may occur. Difficulties can initially feel overwhelming. However, with the right support, most people recover and go on to live full and meaningful lives.</p>'
    . '<p>The skills developed during treatment and recovery can also support people long-term, including:</p>'
    . '<ul><li>Recognising early warning signs</li><li>Reducing the risk of relapse</li><li>Knowing when and how to seek help.</li></ul>';
$ya_treatment = '<p>It is also important to consider age-appropriate psychological, developmental and social factors when supporting young people in their recovery journey.</p>'
    . '<p>Our young adult services are designed to support people at this stage of life, helping them to develop the skills needed to manage their mental health and move forward in their recovery.</p>'
    . '<p>You can explore related supports including our '
    . '<a href="' . esc_url($home . '/what-we-offer/young-adult-programme/') . '">Young Adult Programme</a>, '
    . '<a href="' . esc_url($home . '/mental-health/depression/') . '">depression</a>, '
    . '<a href="' . esc_url($home . '/mental-health/anxiety/') . '">anxiety</a>, and '
    . '<a href="' . esc_url($home . '/getting-help/') . '">getting help</a> pages.</p>';
$ya_intro = '<p>Adolescence and early adulthood are a critical time for personal development. This is also the stage of life when around 75% of mental health difficulties first begin.</p>'
    . '<p>Mental health difficulties at this stage can have a significant impact on a young person’s ability to build independence, relationships and personal goals. However, most difficulties are treatable, and, with early support, many people make a good recovery.</p>';
$ya_resources = [
    'Mental health information' => '<ul>'
        . '<li><a href="' . esc_url($home . '/mental-health/anxiety/') . '">Anxiety</a></li>'
        . '<li><a href="' . esc_url($home . '/mental-health/depression/') . '">Depression</a></li>'
        . '<li><a href="' . esc_url($home . '/mental-health/bipolar-disorder/') . '">Bipolar disorder</a></li>'
        . '<li><a href="' . esc_url($home . '/mental-health/eating-disorders/') . '">Eating disorders</a></li>'
        . '<li><a href="' . esc_url($home . '/mental-health/personality-disorders/') . '">Personality disorders</a></li>'
        . '<li><a href="' . esc_url($home . '/mental-health/schizophrenia/') . '">Schizophrenia</a></li>'
        . '</ul>',
    'Services and programmes' => '<ul>'
        . '<li><a href="' . esc_url($home . '/what-we-offer/young-adult-programme/') . '">Young Adult Programme</a></li>'
        . '<li><a href="' . esc_url($home . '/service-users-and-visitors/young-adult-mental-health/') . '">Young adult mental health</a></li>'
        . '<li><a href="' . esc_url($home . '/getting-help/') . '">Getting help</a></li>'
        . '</ul>',
];

$build_ya_rows = static function (int $image_id) use ($ya_intro, $ya_common, $ya_recovery, $ya_treatment, $ya_resources): array {
    return [
        matrix_orlaith_hero_row('Young adult mental health', $ya_intro, $image_id),
        matrix_orlaith_content_row('Common mental health difficulties', $ya_common, 'white'),
        matrix_orlaith_content_row('Recovery and wellbeing as a young adult', $ya_recovery, 'cream'),
        matrix_orlaith_content_row('Treatment and supports', $ya_treatment, 'white'),
        matrix_orlaith_accordion_row($ya_resources, 'default', 'Useful resources'),
    ];
};

$ya_page = (int) (get_page_by_path('service-users-and-visitors/young-adult-mental-health')?->ID ?? 0);
if ($ya_page > 0) {
    $save_rows($ya_page, $build_ya_rows((int) get_post_thumbnail_id($ya_page)));
    matrix_orlaith_set_seo(
        $ya_page,
        'Young adult mental health | St Patrick’s Mental Health Services',
        'Learn about mental health in young adults, common conditions, and recovery, including anxiety, depression, psychosis and eating disorders.'
    );
}
$ya_cpt = get_posts(['post_type' => 'mental_health', 'name' => 'young-adults', 'post_status' => 'any', 'posts_per_page' => 1]);
if ($ya_cpt !== []) {
    $save_rows((int) $ya_cpt[0]->ID, $build_ya_rows((int) get_post_thumbnail_id($ya_cpt[0]->ID)));
}

// ---------------------------------------------------------------------------
// 12) Homecare adolescent: Getting started -> accordion
// ---------------------------------------------------------------------------
$hc_id = (int) (get_page_by_path('service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent')?->ID ?? 0);
if ($hc_id > 0) {
    $rows = get_field('flexible_content_blocks', $hc_id) ?: [];
    $new = [];
    foreach ($rows as $row) {
        $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
        if (($row['acf_fc_layout'] ?? '') === 'content' && stripos($heading, 'Getting started') !== false) {
            $html = $replace_live_links((string) $row['content']);
            $intro = '';
            if (preg_match('#^(.*?)(?=<h3\b)#is', $html, $im)) {
                $intro = trim($im[1]);
            }
            $items = [];
            foreach ($parse_qa($html) as [$q, $a]) {
                $items[$q] = $a;
            }
            if ($intro !== '') {
                $new[] = matrix_orlaith_content_row($heading, $intro, 'cream');
            }
            $new[] = matrix_orlaith_accordion_row($items, 'default', $intro === '' ? $heading : '');
            continue;
        }
        if (! empty($row['content'])) {
            $row['content'] = $replace_live_links((string) $row['content']);
        }
        $new[] = $row;
    }
    $save_rows($hc_id, $new);
}

// ---------------------------------------------------------------------------
// 13) Older adults useful resources
// ---------------------------------------------------------------------------
$oa = get_posts(['post_type' => 'mental_health', 'name' => 'older-adults', 'post_status' => 'any', 'posts_per_page' => 1]);
if ($oa !== []) {
    $oa_id = (int) $oa[0]->ID;
    $rows = get_field('flexible_content_blocks', $oa_id) ?: [];
    $new = [];
    $resources_html = '<p>Explore related information and supports for older adult mental health:</p><ul>'
        . '<li><a href="' . esc_url($home . '/service-users-and-visitors/older-adult-mental-health/') . '">Older adult mental health overview</a></li>'
        . '<li><a href="' . esc_url($home . '/what-we-offer/cft-for-older-adults/') . '">Compassion-Focused Therapy for Older Adults</a></li>'
        . '<li><a href="' . esc_url($home . '/what-we-offer/older-adult-formulation-group/') . '">Older Adult Formulation Group</a></li>'
        . '<li><a href="' . esc_url($home . '/what-we-offer/living-well-with-mild-cognitive-impairment/') . '">Living Well with Mild Cognitive Impairment</a></li>'
        . '<li><a href="' . esc_url($home . '/mental-health/depression/') . '">Depression</a></li>'
        . '<li><a href="' . esc_url($home . '/getting-help/') . '">Getting help</a></li>'
        . '</ul>';
    $replaced = false;
    foreach ($rows as $row) {
        $heading = wp_strip_all_tags((string) ($row['heading'] ?? ''));
        if (($row['acf_fc_layout'] ?? '') === 'content' && stripos($heading, 'Useful resources') !== false) {
            $new[] = matrix_orlaith_accordion_row([
                'Related services and information' => $resources_html,
            ], 'default', 'Useful resources');
            $replaced = true;
            continue;
        }
        $new[] = $row;
    }
    if (! $replaced) {
        $new[] = matrix_orlaith_accordion_row([
            'Related services and information' => $resources_html,
        ], 'default', 'Useful resources');
    }
    $save_rows($oa_id, $new);
}

WP_CLI::success('Service Users refactor notes applied.');
