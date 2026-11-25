<?php
/**
 * Verify FACT snag fixes on local/staging.
 *
 *   /wp-content/themes/matrix-starter/scripts/verify-fact-snag.php?key=verify-fact-snag
 */
if (! defined('ABSPATH')) {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $allowed = str_contains($host, 'localhost')
        || str_contains($host, 'matrix-test.com')
        || in_array($remote, ['127.0.0.1', '::1'], true);
    if (! $allowed || ($_GET['key'] ?? '') !== 'verify-fact-snag') {
        http_response_code(403);
        exit;
    }
    require dirname(__DIR__, 4) . '/wp-load.php';
    header('Content-Type: text/plain; charset=utf-8');
}

$posts = get_posts([
    'name' => 'focused-acceptance-and-commitment-therapy',
    'post_type' => 'programmes_therapies',
    'post_status' => 'any',
    'posts_per_page' => 1,
]);
if ($posts === []) {
    echo "MISS\n";
    exit;
}
$p = $posts[0];
$id = (int) $p->ID;
$content = (string) $p->post_content;
$listing = function_exists('get_field') ? (string) get_field('listing_summary', $id) : '';
$excerpt = (string) $p->post_excerpt;
$plain = static function (string $html): string {
    $text = html_entity_decode(wp_strip_all_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

    return trim($text);
};
$dup = 'Focused Acceptance and Commitment Therapy (FACT) is an online group therapy programme that runs for eight weeks.';

$mindfulness_anchor = (bool) preg_match('#<a\b[^>]*>\s*mindfulness\s*</a>#iu', $content);
$amp = (bool) preg_match('#Acceptance\s*&(?:amp;)?\s*Commitment\s*Therapy#i', $content . $listing . $excerpt);
$body_has_dup = str_contains(strtolower($plain($content)), strtolower($dup));
$listing_has = str_contains(strtolower($plain($listing . ' ' . $excerpt)), strtolower($dup));
$mindfulness_plain = (bool) preg_match('#\bmindfulness\b#i', $content);

echo "id={$id}\n";
echo 'permalink=' . get_permalink($id) . "\n";
echo 'mindfulness_anchor=' . ($mindfulness_anchor ? 'FAIL' : 'PASS') . "\n";
echo 'mindfulness_plain_text=' . ($mindfulness_plain ? 'PASS' : 'FAIL') . "\n";
echo 'ampersand_act=' . ($amp ? 'FAIL' : 'PASS') . "\n";
echo 'body_duplicate_intro=' . ($body_has_dup ? 'FAIL' : 'PASS') . "\n";
echo 'listing_or_excerpt_keeps_intro=' . ($listing_has ? 'PASS' : 'WARN') . "\n";
echo 'listing=' . substr($plain($listing), 0, 200) . "\n";
echo 'excerpt=' . substr($plain($excerpt), 0, 200) . "\n";
echo 'body_start=' . substr($plain($content), 0, 220) . "\n";

$ok = ! $mindfulness_anchor && $mindfulness_plain && ! $amp && ! $body_has_dup;
echo $ok ? "RESULT=PASS\n" : "RESULT=FAIL\n";
