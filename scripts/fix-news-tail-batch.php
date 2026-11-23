<?php
/**
 * Close the news-tail snags that already have a live page or file.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-news-tail-batch.php?key=fix-news-tail
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-news-tail') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$author = "St Patrick's Mental Health Services";
$home = home_url();
$mindfulness_pdf = $home . '/wp-content/uploads/2026/11/spmhs-mindfulness.pdf';
$practical_page = $home . '/a-practical-approach-to-a-healthy-mind-resilience-coping-skills/';
$panic_page = $home . '/symptoms-of-panic-attacks-and-what-to-do/';
$dementia_page = $home . '/family-lecture-series-diagnosis-of-dementia/';
$work_stress_page = $home . '/work-related-stress-how-to-manage-it/';
$carers_guide = $home . '/wp-content/uploads/2026/09/carers-and-supporters-guide-2024.pdf';
$study_url = 'https://repository.stpatricks.ie/items/ea9c6888-0b6b-41a2-91b0-ccdefa20eec9';
$help_url = $home . '/getting-help/';

if (! function_exists('matrix_fix_news_tail_replace_anchor')) {
    function matrix_fix_news_tail_replace_anchor(string $html, string $text, string $url): string
    {
        $pattern = '#<a\b[^>]*>\s*' . preg_quote($text, '#') . '\s*</a>#i';

        return (string) preg_replace(
            $pattern,
            '<a href="' . esc_url($url) . '">' . $text . '</a>',
            $html,
            1
        );
    }
}

$jobs = [
    'vulnerability-hacking-social-media-and-eating-disorders' => ['author' => true],
    'what-is-depression' => ['author' => true, 'mindfulness' => true],
    'burnout-stress-syndrome' => ['practical' => true],
    'depression-in-older-adults' => ['dementia' => true, 'panicky' => true],
    'antipsychotic-induced-weight-gain' => ['study' => true, 'help' => true],
    'family-therapy-in-mental-health-recovery' => ['panic' => true, 'children' => true, 'guide' => true],
    'compassion-focused-therapy-in-gp-surgery' => ['work' => true],
    'early-diagnosis-of-bipolar-in-primary-care' => ['heading' => true],
];

foreach ($jobs as $slug => $flags) {
    echo "\n==== $slug ====\n";
    $posts = get_posts([
        'name' => $slug,
        'post_type' => 'post',
        'post_status' => 'any',
        'posts_per_page' => 1,
    ]);
    if ($posts === []) {
        echo "MISSING\n";
        continue;
    }

    $post = $posts[0];
    $id = (int) $post->ID;

    if (! empty($flags['author'])) {
        if (function_exists('update_field')) {
            update_field('post_author_name', $author, $id);
        } else {
            update_post_meta($id, 'post_author_name', $author);
        }
        echo 'author=' . matrix_get_blog_post_author_name($id) . "\n";
    }

    $html = (string) $post->post_content;
    $orig = $html;

    if (! empty($flags['mindfulness'])) {
        $html = (string) preg_replace(
            '#href=(["\'])[^"\']*/mindfulness/?\1#',
            'href=$1' . esc_url($mindfulness_pdf) . '$1',
            $html
        );
    }

    if (! empty($flags['practical'])) {
        $html = (string) preg_replace(
            '#href=(["\'])[^"\']*a-practical-approach-to-a-healthy-mind-resilience-coping-skills-1\.jpg[^"\']*\1#',
            'href=$1' . esc_url($practical_page) . '$1',
            $html
        );
    }

    if (! empty($flags['dementia'])) {
        $html = matrix_fix_news_tail_replace_anchor($html, 'dementia', $dementia_page);
    }

    if (! empty($flags['panicky'])) {
        $html = matrix_fix_news_tail_replace_anchor($html, 'Feeling panicky', $panic_page);
    }

    if (! empty($flags['panic'])) {
        $html = matrix_fix_news_tail_replace_anchor($html, 'panic attacks', $panic_page);
    }

    if (! empty($flags['children'])) {
        $html = (string) preg_replace(
            '#<a\b[^>]*>\s*depression in children and young people\s*</a>#i',
            'depression in children and young people',
            $html,
            1
        );
    }

    if (! empty($flags['guide'])) {
        $html = matrix_fix_news_tail_replace_anchor($html, 'download the guide for free here', $carers_guide);
    }

    if (! empty($flags['work'])) {
        $html = matrix_fix_news_tail_replace_anchor($html, 'work role and demands', $work_stress_page);
    }

    if (! empty($flags['study'])) {
        $html = str_replace('https://repository.stpatricks.ie/home', $study_url, $html);
    }

    if (! empty($flags['help']) && ! str_contains($html, '>seeking further help<')) {
        $html = (string) preg_replace(
            '#seeking further help#',
            '<a href="' . esc_url($help_url) . '">seeking further help</a>',
            $html,
            1
        );
    }

    if (! empty($flags['heading'])) {
        $html = (string) preg_replace(
            '#<h2>\s*Cognitive impairment associated with number of mood episodes:\s*</h2>\s*<h3>\s*Importance of early diagnosis and treatment\s*</h3>#i',
            '<h2>Cognitive impairment associated with number of mood episodes: importance of early diagnosis and treatment</h2>',
            $html,
            1
        );
    }

    if ($html === $orig) {
        echo "content unchanged\n";
        continue;
    }

    wp_update_post([
        'ID' => $id,
        'post_content' => $html,
    ]);
    echo "content updated\n";
}
