<?php
/**
 * Batch-close remaining News "To do" rows: org byline + common link rewrites.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-news-todo-batch.php?key=fix-news-todo
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-news-todo') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$author = "St Patrick's Mental Health Services";
$home = untrailingslashit(home_url('/'));

$slugs = [
    'founders-day-conference-unlocking-potential-of-later-life',
    'mental-health-at-work-supports-for-employees-and-employers',
    'new-partnership-aims-to-strengthen-training-and-research-links-in-mental-health-pharmacy',
    'annual-report-and-outcomes-report-2023',
    'new-digital-transformation-strategy',
    'assisted-decision-making-capacity-act-one-year-on',
    'migrant-and-refugee-mental-health-founders-day',
    'finding-ways-to-ease-pressure-at-christmas-a-service-user-experience',
    'your-portal-personal-health-record',
    'understanding-dialectical-behaviour-therapy-at-spmhs',
    'freedom-from-anxiety-service-user-experience',
    'mental-health-medication-choices',
    'ending-mental-health-stigma-at-work',
    'mental-health-information-in-ukrainian',
    'self-harm-causes-and-supports',
    'reflections-on-mental-health-recovery',
    'assisted-decision-making-capacity-act',
    'promoting-medication-safety',
    'speakagainststigma',
    'mental-health-recovery',
    'coronavirus-anxiety',
    'self-harm-awareness',
    'recovery-and-me-how-cinema-has-helped-with-my-depression',
    'enjoying-wellbeing-and-healthy-aging-in-older-adulthood',
    'supporting-those-bereaved-by-suicide',
    'staying-well-advice-from-a-service-user-on-how-to-maintain-wellness-outside-of-hospital',
    'nursing-in-st-patrick-s-mental-health-services',
    'looking-after-maternal-mental-health',
    'how-should-we-talk-to-someone-with-depression',
    'memories-light-the-corners-of-my-mind',
    'social-anxiety-strategies-for-coping',
    'what-is-depression',
    'burnout-stress-syndrome',
    'depression-in-older-adults',
    'establishing-a-physical-health-monitoring-service-for-service-users-prescribed-psychotropic-medications',
    'understanding-and-managing-anxiety-disorders-presentations-in-the-primary-care-setting',
];

$replacements = [
    'https://www.stpatricks.ie/care-treatment/programmes-therapies/our-programmes-and-therapies/dialectical-behavioural-therapy' => $home . '/programmes-therapies/dialectical-behavioural-therapy/',
    'https://www.stpatricks.ie/care-treatment/programmes-therapies/our-programmes-and-therapies/compassion-focused-therapy' => $home . '/programmes-therapies/compassion-focused-therapy/',
    'https://www.stpatricks.ie/care-treatment/programmes-therapies/our-programmes-and-therapies/cognitive-behavioural-therapy' => $home . '/programmes-therapies/cognitive-behavioural-therapy/',
    'https://www.stpatricks.ie/care-treatment/programmes-therapies/our-programmes-and-therapies/cognitive-behavioural-therapy-for-insomnia-cbt-i' => $home . '/programmes-therapies/cognitive-behavioural-therapy-for-insomnia-cbt-i/',
    'https://www.stpatricks.ie/care-treatment/programmes-therapies/our-programmes-and-therapies/acceptance-commitment-therapy-act' => $home . '/programmes-therapies/acceptance-commitment-therapy-act/',
    'https://www.stpatricks.ie/mental-health' => $home . '/service-users-and-visitors/about-mental-health/',
    'https://www.stpatricks.ie/getting-help/concerned-about-yourself-or-someone-you-know' => $home . '/getting-help/',
    'https://www.stpatricks.ie/getting-help' => $home . '/getting-help/',
    'https://www.stpatricks.ie/about-us/multidisciplinary-teams' => $home . '/about-us/our-team/',
    'https://www.stpatricks.ie/care-treatment/your-portal/about-your-portal' => $home . '/your-portal/about-your-portal/',
    'https://www.stpatricks.ie/care-treatment/your-portal/register' => $home . '/your-portal/register-for-your-portal/',
    'http://st-patricks.s1.matrix-test.com/media-centre/' => $home . '/',
    'https://st-patricks.s1.matrix-test.com/media-centre/' => $home . '/',
    'http://st-patricks.s1.matrix-test.com/care-treatment/' => $home . '/',
    'https://st-patricks.s1.matrix-test.com/care-treatment/' => $home . '/',
];

$resolve_media_centre = static function (string $html) use ($home): string {
    // /media-centre/blogs-articles/.../slug → /slug/
    $html = preg_replace_callback(
        '#https?://[^"\'\s]*/media-centre/(?:blogs-articles|news|podcasts)/\d{4}/[a-z]+/([a-z0-9\-]+)/?#i',
        static function (array $m) use ($home): string {
            return $home . '/' . $m[1] . '/';
        },
        $html
    ) ?? $html;

    // /media-centre/news/YYYY/month/slug
    $html = preg_replace_callback(
        '#https?://[^"\'\s]*/media-centre/news/\d{4}/[a-z]+/([a-z0-9\-]+)/?#i',
        static function (array $m) use ($home): string {
            return $home . '/' . $m[1] . '/';
        },
        $html
    ) ?? $html;

    return $html;
};

$ok = [];
$miss = [];
$notes = [];

foreach ($slugs as $slug) {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'post',
        'post_status' => ['publish', 'draft', 'private'],
        'posts_per_page' => 1,
    ]);
    echo "==== $slug ====\n";
    if ($posts === []) {
        echo "MISSING\n";
        $miss[] = $slug;
        continue;
    }
    $post = $posts[0];
    $id = (int) $post->ID;
    $actions = [];

    if (function_exists('update_field')) {
        update_field('post_author_name', $author, $id);
    } else {
        update_post_meta($id, 'post_author_name', $author);
    }
    $actions[] = 'byline';

    $html = (string) $post->post_content;
    $orig = $html;

    foreach ($replacements as $from => $to) {
        foreach ([$from, rtrim($from, '/') . '/', rtrim($from, '/')] as $variant) {
            if (str_contains($html, $variant)) {
                $html = str_replace($variant, $to, $html);
            }
        }
    }
    $html = $resolve_media_centre($html);

    // Related Links heading inside content
    $html = preg_replace('/\bRelated Links\b/', 'Related links', $html) ?? $html;

    if ($html !== $orig) {
        wp_update_post([
            'ID' => $id,
            'post_content' => $html,
        ]);
        $actions[] = 'content';
    }

    // Related posts titles / sidebar meta if present
    $related = get_field('related_posts_heading', $id);
    if (is_string($related) && str_contains($related, 'Related Links')) {
        update_field('related_posts_heading', str_replace('Related Links', 'Related links', $related), $id);
        $actions[] = 'related-heading';
    }

    clean_post_cache($id);
    $byline = function_exists('matrix_get_blog_post_author_name')
        ? matrix_get_blog_post_author_name($id)
        : (string) get_field('post_author_name', $id);
    $permalink = get_permalink($id);
    echo 'id=' . $id . ' byline=' . $byline . ' actions=' . implode(',', $actions) . ' url=' . $permalink . "\n";
    $ok[] = [
        'slug' => $slug,
        'id' => $id,
        'url' => $permalink,
        'byline' => $byline,
        'actions' => $actions,
    ];
    $notes[$slug] = 'byline=' . $byline . '; ' . implode(',', $actions);
}

echo "\nOK=" . count($ok) . " MISS=" . count($miss) . "\n";
if ($miss !== []) {
    echo 'MISSING: ' . implode(', ', $miss) . "\n";
}
echo "DONE\n";
