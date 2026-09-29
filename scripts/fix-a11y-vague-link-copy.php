<?php

/**
 * Fix Silktide vague/empty link copy called out in the a11y content CSV.
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/fix-a11y-vague-link-copy.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

/**
 * @param string $html
 * @param array<string, string> $replacements href => new link text
 */
function matrix_a11y_replace_link_texts(string $html, array $replacements): string
{
    if ($html === '' || $replacements === []) {
        return $html;
    }

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML(
        '<?xml encoding="utf-8" ?><div id="matrix-a11y-root">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOWARNING | LIBXML_NOERROR
    );
    libxml_clear_errors();

    if (! $loaded) {
        return $html;
    }

    $wrapper = $dom->getElementById('matrix-a11y-root');

    if (! $wrapper instanceof DOMElement) {
        return $html;
    }

    $changed = false;

    foreach ($dom->getElementsByTagName('a') as $anchor) {
        if (! $anchor instanceof DOMElement) {
            continue;
        }

        $href = trim($anchor->getAttribute('href'));
        $matched = null;

        foreach ($replacements as $needle => $label) {
            if ($href === $needle || str_contains($href, $needle) || str_ends_with(rtrim($href, '/'), rtrim($needle, '/'))) {
                $matched = $label;
                break;
            }
        }

        if ($matched === null) {
            continue;
        }

        $visible = trim(preg_replace('/\s+/u', ' ', $anchor->textContent) ?? '');

        if ($visible !== '' && ! matrix_is_vague_link_text($visible) && strcasecmp($visible, $matched) !== 0) {
            // Already descriptive enough.
            continue;
        }

        while ($anchor->firstChild) {
            $anchor->removeChild($anchor->firstChild);
        }

        $anchor->appendChild($dom->createTextNode($matched));
        $changed = true;
    }

    if (! $changed) {
        return $html;
    }

    $out = '';

    foreach ($wrapper->childNodes as $child) {
        $out .= $dom->saveHTML($child);
    }

    return $out;
}

/**
 * @param mixed $value
 * @param array<string, string> $replacements
 * @return array{0: mixed, 1: bool}
 */
function matrix_a11y_walk_replace($value, array $replacements): array
{
    if (is_string($value)) {
        if (! str_contains($value, '<a')) {
            return [$value, false];
        }

        $next = matrix_a11y_replace_link_texts($value, $replacements);

        return [$next, $next !== $value];
    }

    if (! is_array($value)) {
        return [$value, false];
    }

    $changed = false;

    foreach ($value as $key => $child) {
        [$next, $child_changed] = matrix_a11y_walk_replace($child, $replacements);
        $value[$key] = $next;
        $changed = $changed || $child_changed;
    }

    return [$value, $changed];
}

$jobs = [
    198 => [
        '/locations/st-patricks-university-hospital' => "St Patrick's University Hospital",
        '/locations/dean-clinic-st-patricks' => "Dean Clinic St Patrick's",
    ],
    199 => [
        '/recruitment-and-useful-information' => 'Recruitment and useful information',
        '/about-us/careers/recruitment-and-useful-information' => 'Recruitment and useful information',
    ],
    271 => [
        'climateandhealthalliance.wordpress.com/resources' => 'Climate and Health Alliance resources',
        'climateandhealthalliance.wordpress.com/about' => 'Climate and Health Alliance about page',
    ],
    275 => [
        'youtube.com/channel/UCOI_6n3TndtZlW34C4RCdQw' => 'SPMHS YouTube channel',
        '/healthcare-professionals/webinars-events' => 'Webinars and events',
    ],
    3945 => [
        'irishstatutebook.ie/eli/2001/act/25' => 'Mental Health Act 2001 on the Irish Statute Book',
    ],
    1187 => [
        'artsinhealth.ie' => 'Arts in Health Ireland',
        'iacat.ie' => 'Irish Association of Creative Arts Therapists',
        'thelancet.com/journals/lancet/article/PIIS0140-6736(19)32796-5' => 'The Lancet article on arts and health',
    ],
];

// Policies / safeguarding "here" links live on published policies pages too.
$policy_jobs = [
    'child-protection' => [
        'child-protection-statement-of-st-patrick-s-mental-health-services' => 'Child Protection and Welfare Statement',
        'child-safeguarding-statement' => 'Child Safeguarding Statement',
    ],
];

$updated_posts = 0;

foreach ($jobs as $post_id => $replacements) {
    $post = get_post($post_id);

    if (! $post instanceof WP_Post) {
        WP_CLI::warning("Post #{$post_id} not found");
        continue;
    }

    $changed_any = false;

    // post_content
    $content = (string) $post->post_content;
    $next_content = matrix_a11y_replace_link_texts($content, $replacements);

    if ($next_content !== $content) {
        wp_update_post([
            'ID' => $post_id,
            'post_content' => $next_content,
        ]);
        $changed_any = true;
    }

    // Flexible content blocks (ACF)
    if (function_exists('get_field') && function_exists('update_field')) {
        $blocks = get_field('flexible_content_blocks', $post_id);

        if (is_array($blocks)) {
            [$next_blocks, $blocks_changed] = matrix_a11y_walk_replace($blocks, $replacements);

            if ($blocks_changed) {
                update_field('flexible_content_blocks', $next_blocks, $post_id);
                $changed_any = true;
            }
        }
    }

    if ($changed_any) {
        $updated_posts++;
        WP_CLI::log("Updated #{$post_id} {$post->post_title}");
    } else {
        WP_CLI::log("No matching vague links on #{$post_id} {$post->post_title}");
    }
}

// Find and fix policies page(s) with "here" → child protection / safeguarding.
$policy_query = new WP_Query([
    'post_type' => ['page', 'post'],
    'post_status' => 'publish',
    'posts_per_page' => 50,
    's' => 'Child Safeguarding Statement',
]);

foreach ($policy_query->posts as $post) {
    if (! $post instanceof WP_Post) {
        continue;
    }

    $replacements = [
        'child-protection-statement-of-st-patrick-s-mental-health-services' => 'Child Protection and Welfare Statement',
        'child-safeguarding-statement' => 'Child Safeguarding Statement',
    ];

    $changed_any = false;
    $content = (string) $post->post_content;
    $next_content = matrix_a11y_replace_link_texts($content, $replacements);

    if ($next_content !== $content) {
        wp_update_post([
            'ID' => (int) $post->ID,
            'post_content' => $next_content,
        ]);
        $changed_any = true;
    }

    if (function_exists('get_field') && function_exists('update_field')) {
        $blocks = get_field('flexible_content_blocks', $post->ID);

        if (is_array($blocks)) {
            [$next_blocks, $blocks_changed] = matrix_a11y_walk_replace($blocks, $replacements);

            if ($blocks_changed) {
                update_field('flexible_content_blocks', $next_blocks, $post->ID);
                $changed_any = true;
            }
        }
    }

    if ($changed_any) {
        $updated_posts++;
        WP_CLI::log("Updated policy copy on #{$post->ID} {$post->post_title}");
    }
}

WP_CLI::success("Updated {$updated_posts} content records.");
