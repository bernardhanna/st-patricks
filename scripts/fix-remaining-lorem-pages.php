<?php

/**
 * Remove placeholder/lorem copy from the 5 remaining published Lorem ipsum sheet pages.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/fix-remaining-lorem-pages.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

/**
 * @param array<string, mixed> $row
 */
function matrix_fix_lorem_is_placeholder_text(string $text): bool
{
    return (bool) preg_match(
        '/lorem ipsum|draft content for client gathering|replace this copy|page context goes here|videos and images section as requested|title,\s*slider/i',
        $text
    );
}

/**
 * @return array{title:string,excerpt:string,url:string}|null
 */
function matrix_fix_lorem_programme_blurb(string $title): ?array
{
    $found = get_posts([
        'post_type' => 'programmes_therapies',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        's' => $title,
    ]);

    if ($found === []) {
        $found = get_posts([
            'post_type' => 'programmes_therapies',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ]);
        $found = array_values(array_filter($found, static function ($p) use ($title) {
            return strcasecmp($p->post_title, $title) === 0
                || stripos($p->post_title, $title) !== false
                || stripos($title, $p->post_title) !== false;
        }));
    }

    if ($found === []) {
        return null;
    }

    $p = $found[0];
    $excerpt = trim((string) $p->post_excerpt);
    if ($excerpt === '') {
        $excerpt = wp_trim_words(wp_strip_all_tags((string) $p->post_content), 40);
    }

    return [
        'title' => $p->post_title,
        'excerpt' => $excerpt,
        'url' => (string) get_permalink($p),
    ];
}

function matrix_fix_lorem_save_flexi(int $post_id, array $rows): void
{
    delete_field('flexible_content_blocks', $post_id);
    update_field('flexible_content_blocks', $rows, $post_id);
}

// ---------------------------------------------------------------------------
// Pharmacists
// ---------------------------------------------------------------------------
$pharmacists_id = 3572;
$flexi = get_field('flexible_content_blocks', $pharmacists_id);
if (is_array($flexi)) {
    foreach ($flexi as $i => $row) {
        if (($row['acf_fc_layout'] ?? '') !== 'hero_with_breadcrumbs') {
            continue;
        }
        $flexi[$i]['heading'] = 'Pharmacists';
        $flexi[$i]['heading_tag'] = 'h1';
        $flexi[$i]['content'] = '<p>Pharmacists work with our medical and nursing colleagues to promote safe and effective use of medicines. They discuss the most appropriate medicines for you, check prescriptions are safe, and provide information about new medicines that have been prescribed.</p>';
    }
    matrix_fix_lorem_save_flexi($pharmacists_id, $flexi);
    WP_CLI::success('Pharmacists hero copy updated');
}

// ---------------------------------------------------------------------------
// Refer pages
// ---------------------------------------------------------------------------
$refer_heroes = [
    201 => [
        'heading' => 'Refer an Adult to Inpatient Care',
        'content' => '<p>St Patrick\'s Mental Health Services provides specialist inpatient mental healthcare for adults. Use the options below to refer a patient via Healthlink or by completing our adult referral form.</p>',
        'video_heading' => 'Our inpatient services',
        'video_intro' => '<p>Learn more about adult inpatient care at St Patrick\'s Mental Health Services, including what to expect for referrers and service users.</p>',
    ],
    247 => [
        'heading' => 'Refer to Outpatient Care',
        'content' => '<p>Refer adults to outpatient assessment and treatment through our Dean Clinics. Referrals can be made electronically via Healthlink or your practice management system, or by submitting our referral form by Healthmail.</p>',
        'video_heading' => 'Outpatient care',
        'video_intro' => '<p>Find out more about outpatient mental healthcare through our Dean Clinics and how referrals are managed.</p>',
    ],
    253 => [
        'heading' => 'Refer to a Day Programme',
        'content' => '<p>We run a number of day programmes to support people in their mental health recovery. Use the options below to refer a patient to a programme accepting referrals from GPs and other mental healthcare professionals.</p>',
        'video_heading' => 'Day programmes',
        'video_intro' => '<p>Explore how our day programmes support recovery and how healthcare professionals can make a referral.</p>',
    ],
];

foreach ($refer_heroes as $post_id => $copy) {
    $flexi = get_field('flexible_content_blocks', $post_id);
    if (! is_array($flexi)) {
        WP_CLI::warning("No flexi on {$post_id}");
        continue;
    }

    foreach ($flexi as $i => $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '');

        if ($layout === 'hero_with_breadcrumbs') {
            $flexi[$i]['heading'] = $copy['heading'];
            $flexi[$i]['current_crumb_label'] = $copy['heading'];
            $flexi[$i]['heading_tag'] = 'h1';
            $flexi[$i]['content'] = $copy['content'];
        }

        if ($layout === 'video_showcase') {
            if (matrix_fix_lorem_is_placeholder_text((string) ($row['heading'] ?? '') . ' ' . (string) ($row['intro'] ?? ''))) {
                $flexi[$i]['heading'] = $copy['video_heading'];
                $flexi[$i]['intro'] = $copy['video_intro'];
            }
        }

        if ($layout === 'what_we_offer' && ! empty($row['services']) && is_array($row['services'])) {
            foreach ($row['services'] as $si => $service) {
                $title = trim((string) ($service['service_title'] ?? ''));
                $desc = (string) ($service['service_description'] ?? '');
                if ($title === '' || ! matrix_fix_lorem_is_placeholder_text($desc)) {
                    continue;
                }
                $blurb = matrix_fix_lorem_programme_blurb($title);
                if ($blurb === null) {
                    $flexi[$i]['services'][$si]['service_description'] = '<p>' . esc_html($title) . ' at St Patrick\'s Mental Health Services.</p>';
                    continue;
                }
                $flexi[$i]['services'][$si]['service_description'] = '<p>' . esc_html($blurb['excerpt']) . '</p>';
                $flexi[$i]['services'][$si]['service_link'] = [
                    'title' => $blurb['title'],
                    'url' => $blurb['url'],
                    'target' => '',
                ];
            }
        }

        if ($layout === 'faqs' && ! empty($row['faqs']) && is_array($row['faqs'])) {
            foreach ($row['faqs'] as $fi => $faq) {
                $answer = (string) ($faq['answer'] ?? $faq['content'] ?? '');
                if (matrix_fix_lorem_is_placeholder_text($answer)) {
                    // Drop placeholder FAQ rows rather than leave lorem.
                    unset($flexi[$i]['faqs'][$fi]);
                }
            }
            if (isset($flexi[$i]['faqs'])) {
                $flexi[$i]['faqs'] = array_values($flexi[$i]['faqs']);
            }
        }
    }

    matrix_fix_lorem_save_flexi($post_id, $flexi);
    WP_CLI::success('Updated refer page #' . $post_id . ' ' . get_permalink($post_id));
}

// ---------------------------------------------------------------------------
// Research — reseed from proper builder (category cards, no lorem manuals)
// ---------------------------------------------------------------------------
require_once get_template_directory() . '/scripts/lib/research-page-seed-data.php';
$research_id = (int) (get_page_by_path('about-us/research')?->ID ?? 265);
$home = home_url('/');
$research_rows = matrix_build_research_page_flexi_rows([
    [
        'breadcrumb_link' => [
            'title' => 'Home',
            'url' => $home,
            'target' => '',
        ],
    ],
    [
        'breadcrumb_link' => [
            'title' => 'About Us',
            'url' => home_url('/about-us/'),
            'target' => '',
        ],
    ],
]);
matrix_fix_lorem_save_flexi($research_id, $research_rows);
WP_CLI::success('Reseeded Research page #' . $research_id);

// Verify no placeholder strings remain on live HTML
foreach ([3572, 201, 247, 253, $research_id] as $id) {
    $html = (string) file_get_contents((string) get_permalink($id));
    $bad = preg_match(
        '/lorem ipsum|Draft content for client gathering|Replace this copy|Page context goes here|Videos and images section as requested|Title, slider/i',
        $html
    );
    WP_CLI::log(($bad ? 'STILL HAS PLACEHOLDER' : 'clean') . ' #' . $id . ' ' . get_permalink($id));
}

echo "Done.\n";
