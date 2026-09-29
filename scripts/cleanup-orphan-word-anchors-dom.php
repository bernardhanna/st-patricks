<?php

/**
 * Remove remaining Word footnote/comment anchors via the HTML link processor.
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/cleanup-orphan-word-anchors-dom.php
 */

if (! defined('ABSPATH')) {
    exit(1);
}

global $wpdb;

$ids = $wpdb->get_col(
    "SELECT ID FROM {$wpdb->posts}
     WHERE post_status = 'publish'
       AND (
         post_content LIKE '%#_msocom%'
         OR post_content LIKE '%#_ednref%'
         OR post_content LIKE '%#_ftnref%'
         OR post_content LIKE '%#_edn%'
         OR post_content LIKE '%#_ftn%'
       )"
);

$updated = 0;

foreach ($ids as $id) {
    $post = get_post((int) $id);

    if (! $post instanceof WP_Post) {
        continue;
    }

    $original = (string) $post->post_content;
    $processed = matrix_process_external_links_in_html($original);

    // Drop target/rel/new-tab side effects from storage: keep only orphan removal
    // by comparing whether orphan hrefs remain.
    $still_has_orphan = (bool) preg_match('/#_?(?:msocom|ednref|ftnref|edn|ftn)/i', $processed);

    if ($still_has_orphan) {
        WP_CLI::warning("#{$id} still has orphan fragments after process");
        continue;
    }

    if ($processed === $original || ! preg_match('/#_?(?:msocom|ednref|ftnref|edn|ftn)/i', $original)) {
        continue;
    }

    // Re-strip only orphan anchors from original so we do not persist new-tab spans.
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML(
        '<?xml encoding="utf-8" ?><div id="root">' . $original . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOWARNING | LIBXML_NOERROR
    );
    libxml_clear_errors();

    if (! $loaded) {
        continue;
    }

    $wrapper = $dom->getElementById('root');

    if (! $wrapper instanceof DOMElement) {
        continue;
    }

    foreach (iterator_to_array($dom->getElementsByTagName('a')) as $anchor) {
        if (! $anchor instanceof DOMElement || ! $anchor->parentNode) {
            continue;
        }

        $href = trim($anchor->getAttribute('href'));

        if (! matrix_is_orphan_word_anchor_href($href)) {
            continue;
        }

        while ($anchor->firstChild) {
            $anchor->parentNode->insertBefore($anchor->firstChild, $anchor);
        }

        $anchor->parentNode->removeChild($anchor);
    }

    $clean = '';

    foreach ($wrapper->childNodes as $child) {
        $clean .= $dom->saveHTML($child);
    }

    if ($clean === $original) {
        continue;
    }

    $result = wp_update_post([
        'ID' => (int) $id,
        'post_content' => $clean,
    ], true);

    if (is_wp_error($result)) {
        WP_CLI::warning("#{$id}: " . $result->get_error_message());
        continue;
    }

    $updated++;
    WP_CLI::log("DOM-cleaned #{$id} {$post->post_title}");
}

WP_CLI::success("Updated {$updated} of " . count($ids) . ' matching posts.');
