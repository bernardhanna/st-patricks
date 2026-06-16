<?php

/**
 * Seed Accessibility page with a WCAG 2.1 accessibility statement.
 *
 * The old live page at stpatricks.ie/accessibility had no body content; this seeds
 * a standard statement aligned with Irish public-sector practice, including a
 * placeholder region for an accessibility score widget (e.g. Silktide).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-accessibility.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';

if (! function_exists('matrix_seed_accessibility_hero')) {
    function matrix_seed_accessibility_hero(string $title, string $intro): array
    {
        $config = matrix_get_utility_page_hero_config($title, $intro);

        return array_merge($config, [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'show_breadcrumbs' => 1,
            'manual_breadcrumbs' => [
                [
                    'breadcrumb_link' => [
                        'title' => 'Home',
                        'url' => home_url('/'),
                        'target' => '',
                    ],
                ],
            ],
        ]);
    }
}

$post_id = (int) (get_page_by_path('accessibility')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at accessibility.');
    }

    exit(1);
}

$contact_url = home_url('/contact-us/');
$sitemap_url = home_url('/sitemap/');
$privacy_url = home_url('/cookie-privacy-policy/');

$hero_intro = 'St Patrick\'s Mental Health Services (SPMHS) is committed to making this website accessible to as many people as possible, including people with disabilities.';

$commitment_body = '<p>We are committed to improving the accessibility of stpatricks.ie in line with the European Accessibility Act and Irish accessibility regulations (S.I. No. 358/2020).</p>'
    . '<p>Our aim is to meet Web Content Accessibility Guidelines (WCAG) 2.1 Level AA standard. This accessibility statement applies to this website. It does not apply to third-party websites or services that we link to.</p>'
    . '<p>We recognise that accessibility is an ongoing effort. We review our website regularly and work to address barriers when they are identified.</p>';

$compliance_body = '<p>We strive to ensure that the main content on this website is accessible. However, some content may not yet fully meet WCAG 2.1 Level AA, including:</p>'
    . '<ul>'
    . '<li>PDF documents published before September 2018, which may be less accessible than HTML pages for people using assistive technologies</li>'
    . '<li>Some embedded videos or audio published before September 2020</li>'
    . '<li>Some tables, images, or forms that may not yet include full alternative text or correctly defined headers</li>'
    . '<li>Third-party content such as social media embeds or external tools, which are governed by the accessibility policies of those providers</li>'
    . '</ul>'
    . '<p>We are working to improve these areas over time. If you encounter content that is not accessible, please let us know using the contact details below.</p>';

$testing_body = '<p>We use the WCAG 2.1 Level AA guidelines to assess the accessibility of this website. Our approach includes:</p>'
    . '<ul>'
    . '<li>Regular automated and manual accessibility checks</li>'
    . '<li>Review of new templates and features before publication</li>'
    . '<li>Training for content editors on accessible publishing practices</li>'
    . '<li>Ongoing monitoring and remediation of issues identified through testing or user feedback</li>'
    . '</ul>'
    . '<p>We will continue to test and improve the accessibility of this website as it develops.</p>';

$improvements_body = '<p>We are taking the following steps to improve accessibility on this website:</p>'
    . '<ul>'
    . '<li>Publishing key information as accessible HTML where possible, rather than PDF alone</li>'
    . '<li>Ensuring new content uses clear headings, meaningful link text, and sufficient colour contrast</li>'
    . '<li>Improving keyboard navigation and screen reader compatibility across templates</li>'
    . '<li>Addressing issues identified through accessibility audits and user feedback</li>'
    . '</ul>';

$score_body = '<h2>Website accessibility score</h2>'
    . '<p>We monitor the accessibility of this website and publish our latest accessibility score here following each audit.</p>'
    . '<div id="accessibility-score" class="flex flex-col items-center justify-center gap-3 p-8 my-8 text-center border border-dashed border-[#6FC9C0] rounded-2xl bg-[#F1F8F9] text-[#08284B]" aria-label="Accessibility score">'
    . '<p class="mb-0 text-lg font-semibold">Accessibility score</p>'
    . '<p class="mb-0 max-w-xl text-base">Our latest audit score will appear in this section. If you need accessibility support in the meantime, please contact us using the details below.</p>'
    . '</div>'
    . '<p>If your organisation uses an accessibility monitoring tool such as Silktide, the embed code for the live score can be added to this section in the WordPress editor.</p>';

$feedback_body = '<p>If you have difficulty accessing any part of this website, or if you have feedback on how we can improve accessibility, we would like to hear from you.</p>'
    . '<p>Please contact us by calling <strong><a href="tel:012493200">01 249 3200</a></strong> or by visiting our <a href="' . esc_url($contact_url) . '">Contact us page</a>.</p>'
    . '<p>For information on how we handle personal data, please see our <a href="' . esc_url($privacy_url) . '">Cookie &amp; Privacy policy</a>. You can also view our <a href="' . esc_url($sitemap_url) . '">Sitemap</a> to find pages across the site.</p>';

$flexi_rows = [
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Statement of commitment',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $commitment_body,
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'Compliance status',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $compliance_body,
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'How we test this website',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $testing_body,
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FFFFFF',
    ],
    [
        'acf_fc_layout' => 'content',
        'heading' => 'How we are improving accessibility',
        'heading_tag' => matrix_page_seed_heading(2),
        'accent_position' => 'below_heading',
        'content' => $improvements_body,
        'column_layout' => 'one_column',
        'layout_style' => 'image_left',
        'text_width' => 'wide',
        'background_type' => 'color',
        'background_color' => '#FBFAF7',
    ],
    [
        'acf_fc_layout' => 'wysiwyg',
        'text_content' => $score_body,
    ],
    [
        'acf_fc_layout' => 'content_cta',
        'heading_tag' => matrix_page_seed_heading(2),
        'heading' => 'Feedback and contact',
        'body' => $feedback_body,
        'button_link' => [
            'title' => 'Contact us',
            'url' => $contact_url,
            'target' => '',
        ],
        'background_type' => 'color',
        'background_color' => '#C6ECF4',
    ],
];

wp_update_post([
    'ID' => $post_id,
    'post_content' => '',
    'post_title' => 'Accessibility',
]);

update_field('hero_content_blocks', [
    matrix_seed_accessibility_hero('Accessibility', $hero_intro),
], $post_id);
update_field('flexible_content_blocks', $flexi_rows, $post_id);

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

if (class_exists('WP_CLI')) {
    if ($saved_count === count($flexi_rows)) {
        WP_CLI::success(sprintf(
            'Seeded Accessibility page (%d) with %d flexi blocks.',
            $post_id,
            $saved_count
        ));
    } else {
        WP_CLI::warning(sprintf(
            'Updated page %d but expected %d blocks, found %d.',
            $post_id,
            count($flexi_rows),
            $saved_count
        ));
    }

    WP_CLI::log('Page: ' . get_permalink($post_id));
}
