<?php

/**
 * Fix top bar, footer, and primary mega-menu URLs (labels unchanged).
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-navigation-links.php
 */

if (! function_exists('matrix_seed_resolve_url_by_title')) {
    /**
     * @param array<string, string> $map Normalized title => absolute URL
     */
    function matrix_seed_resolve_url_by_title(string $title, array $map): ?string
    {
        $key = strtolower(trim($title));

        return $map[$key] ?? null;
    }
}

if (! function_exists('matrix_seed_patch_link_repeaters')) {
    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, string>          $map
     * @return array<int, array<string, mixed>>
     */
    function matrix_seed_patch_link_repeaters(array $rows, array $map): array
    {
        foreach ($rows as $index => $row) {
            if (! is_array($row['link'] ?? null)) {
                continue;
            }

            $title = (string) ($row['link']['title'] ?? '');
            $url = matrix_seed_resolve_url_by_title($title, $map);

            if ($url !== null) {
                $rows[$index]['link']['url'] = $url;
            }
        }

        return $rows;
    }
}

$home = home_url('/');

$url_map = [
    // Top bar
    'news and events' => $home . 'news-and-events/',
    'news & events' => $home . 'news-and-events/',
    'blog' => $home . 'news-and-events/',
    'make a payment' => $home . 'service-users-and-visitors/make-a-payment-external-link-to-stripe/',
    'your portal (login)' => $home . 'your-portal/',
    'contact us' => $home . 'contact-us/',

    // Footer — About us
    'overview' => $home . 'about-us/overview/',
    'research' => $home . 'about-us/research/',
    'advocacy' => $home . 'about-us/advocacy/',
    'our history & future' => $home . 'about-us/our-present-and-future/',

    // Footer — Quick links
    'your portal' => $home . 'your-portal/',
    'faqs (healthcare)' => $home . 'healthcare-professionals/frequently-asked-questions/',
    'faqs (service users)' => $home . 'service-users-and-visitors/frequently-asked-questions-faqs/',
    'directions & parking' => $home . 'directions-and-parking/',

    // Footer — Media
    'blog' => $home . 'blog/',
    'events' => $home . 'healthcare-professionals/webinars-events/',
    'support us' => $home . 'about-us/support-us/',
    'media queries' => $home . 'about-us/media-queries/',

    // Footer — Careers
    'vacancies' => $home . 'careers/',
    'staff' => $home . 'recruitment-and-useful-information/staff-wellbeing/',
    'recruitment process' => $home . 'recruitment-and-useful-information/',

    // Footer — Legal
    'cookie & privacy policy' => $home . 'cookie-privacy-policy/',
    'data protection policy' => $home . 'data-protection-policy/',
    'sitemap' => $home . 'sitemap/',
    'accessibility' => $home . 'accessibility/',
];

$nav_button_map = [
    'looking for help?' => $home . 'contact-us/',
    'make a referral' => $home . 'make-a-referral-cta/',
    'access your portal' => $home . 'your-portal/',
];

// --- Top bar ---
$topbar_links = get_field('topbar_links', 'option');

if (is_array($topbar_links)) {
    update_field('topbar_links', matrix_seed_patch_link_repeaters($topbar_links, $url_map), 'option');
}

// --- Footer columns ---
foreach (
    [
        'about_links' => $url_map,
        'quick_links' => $url_map,
        'media_links' => $url_map,
        'careers_links' => $url_map,
        'legal_links' => $url_map,
    ] as $field => $map
) {
    $rows = get_field($field, 'option');

    if (is_array($rows)) {
        update_field($field, matrix_seed_patch_link_repeaters($rows, $map), 'option');
    }
}

// --- Navbar CTA buttons (ACF options) ---
$nav_settings = get_field('navigation_settings_start', 'option');

if (! is_array($nav_settings)) {
    $nav_settings = [];
}

foreach (['looking_help_button', 'referral_button', 'dropdown_cta_button'] as $key) {
    if (! is_array($nav_settings[$key] ?? null)) {
        continue;
    }

    $title = (string) ($nav_settings[$key]['title'] ?? '');
    $url = matrix_seed_resolve_url_by_title($title, $nav_button_map);

    if ($url !== null) {
        $nav_settings[$key]['url'] = $url;
    }
}

update_field('navigation_settings_start', $nav_settings, 'option');

// Rename legacy footer "Blog" label to match the News and events page.
$media_links = get_field('media_links', 'option');

if (is_array($media_links)) {
    foreach ($media_links as $index => $row) {
        if (! is_array($row['link'] ?? null)) {
            continue;
        }

        $title_key = strtolower(trim((string) ($row['link']['title'] ?? '')));

        if ($title_key === 'blog') {
            $media_links[$index]['link']['title'] = 'News and events';
            $media_links[$index]['link']['url'] = $home . 'news-and-events/';
        }
    }

    update_field('media_links', $media_links, 'option');
}

if (class_exists('WP_CLI')) {
    WP_CLI::success('Updated top bar, footer, navbar buttons, and media links.');
}

echo "Navigation links updated.\n";
