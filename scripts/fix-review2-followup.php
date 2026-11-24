<?php
/**
 * Review 2 follow-up for items the first batch missed.
 *
 *   /wp-content/themes/matrix-starter/scripts/fix-review2-followup.php?key=fix-review2b
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $allowed = str_contains($host, 'localhost') || str_contains($host, 'matrix-test.com');
    if (! $allowed || ($_GET['key'] ?? '') !== 'fix-review2b') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$log = [];

$patch_all_strings = static function (int $post_id, callable $replacer) use (&$log): int {
    $hits = 0;
    if ($post_id < 1) {
        return 0;
    }

    $content = (string) get_post_field('post_content', $post_id);
    $next = $replacer($content);
    if ($next !== $content) {
        wp_update_post(['ID' => $post_id, 'post_content' => $next]);
        $hits++;
    }

    if (! function_exists('get_field') || ! function_exists('update_field')) {
        return $hits;
    }

    $rows = get_field('flexible_content_blocks', $post_id);
    if (! is_array($rows)) {
        return $hits;
    }

    $walk = static function (&$node) use (&$walk, $replacer, &$hits): void {
        if (is_string($node)) {
            $next = $replacer($node);
            if ($next !== $node) {
                $node = $next;
                $hits++;
            }
            return;
        }
        if (! is_array($node)) {
            return;
        }
        foreach ($node as $k => &$v) {
            $walk($v);
        }
        unset($v);
    };

    $walk($rows);
    if ($hits > 0) {
        update_field('flexible_content_blocks', $rows, $post_id);
        clean_post_cache($post_id);
    }

    return $hits;
};

// Contact numbers
$contact_id = (int) (get_page_by_path('healthcare-professionals/contact-numbers')?->ID ?? 0);
$n = $patch_all_strings($contact_id, static function (string $html): string {
    $from = 'The Clinical Governance Office manages feedback and complaints. Healthcare professionals can contact the office directly at <a href="mailto:clinicalgovernance@stpatricks.ie">clinicalgovernance@stpatricks.ie</a>.';
    $to = 'The Clinical Governance Office manages feedback and complaints. Healthcare professionals and service users can contact the office directly at <a href="mailto:clinicalgovernance@stpatricks.ie">clinicalgovernance@stpatricks.ie</a>.';
    return str_replace($from, $to, $html);
});
$log[] = "contact-numbers hits=$n";

// Training centre button label + URL
$training_id = (int) (get_page_by_path('healthcare-professionals/training-centre')?->ID ?? 0);
$playlist = 'https://www.youtube.com/playlist?list=PL9Qr7kXsp_qRJM-4TAIIwlUG5CLzrrzV9';
$n = $patch_all_strings($training_id, static function (string $html) use ($playlist): string {
    $next = str_replace('SPMHS on YouTube', 'GP Webinars on YouTube', $html);
    // Point any remaining channel-style CTA that still says GP Webinars / YouTube toward playlist if it's a bare youtube home.
    $next = preg_replace(
        '#(href=["\'])https?://(www\.)?youtube\.com/?(?:[@c/][^"\']*)?(["\'])#i',
        '$1' . $playlist . '$3',
        $next
    ) ?? $next;
    return $next;
});
$log[] = "training-centre hits=$n";

// Day programmes mailto
$day_id = (int) (get_page_by_path('service-users-and-visitors/attending-day-programmes')?->ID ?? 0);
$n = $patch_all_strings($day_id, static function (string $html): string {
    if (str_contains($html, 'mailto:suits@stpatricks.ie')) {
        return $html;
    }
    return preg_replace(
        '#(?<!mailto:|">|\'>)suits@stpatricks\.ie#i',
        '<a href="mailto:suits@stpatricks.ie">suits@stpatricks.ie</a>',
        $html
    ) ?? $html;
});
$log[] = "attending-day-programmes hits=$n";

// Adolescent homecare FAQs button — dump matching titles
$home_id = (int) (get_page_by_path('service-users-and-visitors/your-care-with-willow-grove/your-time-in-homecare-as-an-adolescent')?->ID ?? 0);
$n = $patch_all_strings($home_id, static function (string $html): string {
    $next = $html;
    $next = str_replace('Frequently asked questions', 'Frequently Asked Questions (FAQs)', $next);
    $next = str_replace('Frequently Asked Questions</', 'Frequently Asked Questions (FAQs)</', $next);
    $next = str_replace('>FAQs</', '>Frequently Asked Questions (FAQs)</', $next);
    // Avoid double suffix
    $next = str_replace('Frequently Asked Questions (FAQs) (FAQs)', 'Frequently Asked Questions (FAQs)', $next);
    return $next;
});
$log[] = "adolescent-homecare hits=$n";

// Present strategic Learn more wording for Extending / National / Partnering
$present_id = (int) (get_page_by_path('about-us/our-present-and-future')?->ID ?? 0);
$n = $patch_all_strings($present_id, static function (string $html): string {
    $next = $html;
    $next = preg_replace(
        '#(<a([^>]*href=["\'][^"\']*extending[^"\']*["\'][^>]*)>)([^<]*Extending[^<]*)(</a>)#i',
        '$1Learn more about extending and enhancing our services here$4',
        $next
    ) ?? $next;
    $next = preg_replace(
        '#(<a([^>]*href=["\'][^"\']*national-centre[^"\']*["\'][^>]*)>)([^<]*National Centre[^<]*)(</a>)#i',
        '$1Learn more here$4',
        $next
    ) ?? $next;
    $next = preg_replace(
        '#(<a([^>]*href=["\'][^"\']*partnering[^"\']*["\'][^>]*)>)([^<]*Partnering[^<]*)(</a>)#i',
        '$1Learn more here$4',
        $next
    ) ?? $next;
    return $next;
});
$log[] = "present-links hits=$n";

// Broader byline sweep: any post whose author field contains bernard
$q = new WP_Query([
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 200,
    'meta_query' => [
        [
            'key' => 'post_author_name',
            'value' => 'bernard',
            'compare' => 'LIKE',
        ],
    ],
    'fields' => 'ids',
]);
$org = "St Patrick's Mental Health Services";
$count = 0;
foreach ($q->posts as $pid) {
    update_field('post_author_name', $org, (int) $pid);
    $count++;
}
// Also catch display via WP author nicename bernard with empty ACF
$q2 = new WP_Query([
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 200,
    'author_name' => 'bernard',
    'fields' => 'ids',
]);
foreach ($q2->posts as $pid) {
    $cur = (string) get_field('post_author_name', (int) $pid);
    if ($cur === '' || stripos($cur, 'bernard') !== false) {
        update_field('post_author_name', $org, (int) $pid);
        $count++;
    }
}
$log[] = "bylines bernard sweep=$count";

echo implode("\n", $log) . "\nDONE\n";
