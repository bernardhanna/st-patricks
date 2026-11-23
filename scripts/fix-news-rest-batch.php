<?php
/**
 * Finish the remaining news-article snags that do not need new client copy.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-news-rest-batch.php?key=fix-news-rest
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-news-rest') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$author = "St Patrick's Mental Health Services";
$home = home_url();

$replacements = [
    'https://www.stpatricks.ie/care-treatment/programmes-therapies/our-programmes-and-therapies/dialectical-behavioural-therapy' => $home . '/programmes-therapies/dialectical-behavioural-therapy/',
    'https://www.stpatricks.ie/care-treatment/programmes-therapies/our-programmes-and-therapies/compassion-focused-therapy' => $home . '/programmes-therapies/compassion-focused-therapy/',
    'https://www.stpatricks.ie/care-treatment/programmes-therapies/our-programmes-and-therapies/cognitive-behavioural-therapy' => $home . '/programmes-therapies/cognitive-behavioural-therapy/',
    'https://www.stpatricks.ie/media-centre/blogs-articles/2022/june/lgbtq-community-and-mental-health' => $home . '/lgbtq-community-and-mental-health/',
    'https://www.stpatricks.ie/media-centre/blogs-articles/2019/november/exploring-the-experience-of-and-treatment-of-self-harm' => $home . '/self-harm-causes-and-supports/',
    'https://www.stpatricks.ie/mental-health' => $home . '/service-users-and-visitors/about-mental-health/',
    'https://www.stpatricks.ie/getting-help/concerned-about-yourself-or-someone-you-know' => $home . '/getting-help/',
    'https://www.stpatricks.ie/getting-help' => $home . '/getting-help/',
];

$slugs = [
    'mental-health-recovery',
    'finding-meaning',
    'coronavirus-anxiety',
    'self-harm-awareness',
    'self-harm-causes-and-supports',
    'why-we-should-celebrate-mental-health-recovery',
    'recovery-and-me-how-cinema-has-helped-with-my-depression',
    'enjoying-wellbeing-and-healthy-aging-in-older-adulthood',
    'supporting-those-bereaved-by-suicide',
    'the-mirror-view-a-reflection-on-self-stigma',
];

foreach ($slugs as $slug) {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'post',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    echo "\n==== $slug ====\n";
    if ($posts === []) {
        echo "MISSING\n";
        continue;
    }
    $post = $posts[0];
    $id = (int) $post->ID;

    if (function_exists('update_field')) {
        update_field('post_author_name', $author, $id);
    } else {
        update_post_meta($id, 'post_author_name', $author);
    }
    echo 'author=' . matrix_get_blog_post_author_name($id) . "\n";

    $html = (string) $post->post_content;
    $orig = $html;
    if ($slug === 'coronavirus-anxiety') {
        $html = preg_replace(
            '#<a\b[^>]*href=["\']https://www2\.hse\.ie/conditions/coronavirus/coronavirus\.html["\'][^>]*>(.*?)</a>#is',
            '$1',
            $html
        ) ?? $html;
    }
    if ($slug === 'self-harm-awareness') {
        foreach ($replacements as $from => $to) {
            foreach ([$from, $from . '/'] as $variant) {
                $html = str_replace($variant, $to, $html);
            }
        }
    }
    if ($slug === 'supporting-those-bereaved-by-suicide') {
        $html = preg_replace('/<p>(?:&nbsp;|\s)*<\/p>\s*(?=<h2>Grief after suicide<\/h2>)/i', '', $html) ?? $html;
    }
    if ($html !== $orig) {
        wp_update_post(['ID' => $id, 'post_content' => $html]);
        echo "content updated\n";
    }

    $excerpt = trim((string) $post->post_excerpt);
    if (str_contains($excerpt, '...') || str_contains($excerpt, '…')) {
        $plain = trim((string) preg_replace('/\s+/', ' ', wp_strip_all_tags($html)));
        $stem = trim(str_replace(['...', '…'], '', $excerpt));
        $needle = substr($stem, -40);
        $pos = $needle !== '' ? stripos($plain, $needle) : false;
        $next = $excerpt;
        if ($pos !== false) {
            $start = $pos - (strlen($stem) - strlen($needle));
            $rest = substr($plain, max(0, $start));
            if (preg_match('/^(.{12,}?[.!?])(?:\s|$)/u', $rest, $match)) {
                $next = trim($match[1]);
            }
        }
        if ($next !== $excerpt && ! str_contains($next, '...')) {
            wp_update_post(['ID' => $id, 'post_excerpt' => $next]);
            echo "excerpt: $next\n";
        } else {
            echo "excerpt unchanged: $excerpt\n";
        }
    } else {
        echo 'excerpt ok: ' . mb_substr($excerpt, 0, 140) . "\n";
    }

    if (preg_match_all('#href=["\']([^"\']*stpatricks\.ie[^"\']*)["\']#i', $html, $old)) {
        foreach (array_unique($old[1]) as $href) {
            echo "  OLD $href\n";
        }
    }
    if ($slug === 'supporting-those-bereaved-by-suicide' && preg_match_all('/\[(i{1,3}|iv|v|vi{0,3}|ix|x{1,3}|xi{0,3})\]/i', $html, $marks)) {
        echo '  marks: ' . implode(' ', array_unique($marks[0])) . "\n";
    }
}

$exact_excerpts = [
    'enjoying-wellbeing-and-healthy-aging-in-older-adulthood' => '“We don’t stop playing because we grow old. We grow old because we stop playing.” Dr Declan Lyons, Catríona Lee and Masashi Terao discuss healthy ageing and wellbeing in older adulthood.',
    'the-mirror-view-a-reflection-on-self-stigma' => 'We are often highly critical of ourselves, both physically and mentally. We all need to remind ourselves we are unique, talented individuals with lots of abilities and skills.',
    'supporting-those-bereaved-by-suicide' => 'Bereavement by suicide is perhaps one of the most sensitive topics we can discuss. We share how we can talk to and support people experiencing grief following the death of a family member or friend by suicide.',
];

foreach ($exact_excerpts as $slug => $excerpt) {
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'post',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    if ($posts === []) {
        echo "MISSING excerpt $slug\n";
        continue;
    }
    wp_update_post(['ID' => (int) $posts[0]->ID, 'post_excerpt' => $excerpt]);
    echo "set excerpt $slug\n";
}

foreach ([
    'https://www.stpatricks.ie/media-centre/podcasts/2019/march/self-injury-awareness-day-conference-2019-podcasts',
    'https://www.stpatricks.ie/campaigns/shac2020',
    'https://www2.hse.ie/conditions/coronavirus/coronavirus.html',
] as $url) {
    $response = wp_remote_head($url, ['timeout' => 15, 'redirection' => 3]);
    $code = is_wp_error($response) ? $response->get_error_message() : (string) wp_remote_retrieve_response_code($response);
    echo "remote $code $url\n";
}
