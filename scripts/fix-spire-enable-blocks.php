<?php

/**
 * Re-enable SPIRE flexible content rows hidden by content-gathering "Disable this block",
 * and repair known REC-contaminated leftover rows using migrate-spire copy.
 *
 * Disabled flags live in option matrix_export_disabled_blocks and are honoured by
 * load_flexible_content_templates(), so disabled rows never render on the front end.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/fix-spire-enable-blocks.php
 *
 * Web (admins only): /wp-admin/?matrix_fix_spire_enable=1
 */

if (! defined('ABSPATH')) {
    exit(1);
}

$is_cli = class_exists('WP_CLI');
$allow_web = defined('MATRIX_SPIRE_ENABLE_BLOCKS_ALLOW_WEB') && MATRIX_SPIRE_ENABLE_BLOCKS_ALLOW_WEB;

if (! $is_cli && ! $allow_web) {
    exit(1);
}

$log = static function (string $message) use ($is_cli): void {
    if ($is_cli) {
        WP_CLI::log($message);

        return;
    }

    echo esc_html($message) . "<br>\n";
};

$page = get_page_by_path('spire') ?: get_page_by_path('research/spire');

if (! $page instanceof WP_Post) {
    $page = get_page_by_path('research-library-spire');
}

if (! $page instanceof WP_Post) {
    $message = 'Could not find SPIRE page.';

    if ($is_cli) {
        WP_CLI::error($message);
    }

    wp_die(esc_html($message));
}

$post_id = (int) $page->ID;
$option_key = defined('MATRIX_EXPORT_DISABLED_BLOCKS_OPTION')
    ? MATRIX_EXPORT_DISABLED_BLOCKS_OPTION
    : 'matrix_export_disabled_blocks';
$flex_field = 'flexible_content_blocks';

$disabled_map = get_option($option_key, []);
$before = [];

if (is_array($disabled_map) && isset($disabled_map[$post_id][$flex_field]) && is_array($disabled_map[$post_id][$flex_field])) {
    $before = array_values(array_map('intval', $disabled_map[$post_id][$flex_field]));
}

$log(sprintf('SPIRE page #%d (%s)', $post_id, (string) get_permalink($post_id)));
$log('Disabled flexi indices before: ' . (string) wp_json_encode($before));

if ($before === []) {
    $log('No disabled SPIRE flexi rows to clear.');
} else {
    unset($disabled_map[$post_id][$flex_field]);

    if (isset($disabled_map[$post_id]) && $disabled_map[$post_id] === []) {
        unset($disabled_map[$post_id]);
    }

    update_option($option_key, is_array($disabled_map) ? $disabled_map : [], false);
    $log('Cleared disabled flexi indices for SPIRE.');
}

$rows = function_exists('get_field') ? get_field($flex_field, $post_id) : null;

if (! is_array($rows)) {
    $message = 'SPIRE flexible_content_blocks empty after enable.';

    if ($is_cli) {
        WP_CLI::error($message);
    }

    wp_die(esc_html($message));
}

$about_research_url = home_url('/about-us/research/');
$research_projects_url = home_url('/research-projects/');
$ethics_url = home_url('/research-ethics-committee/');
$spire_external_url = 'https://repository.stpatricks.ie/home';
$spire_url = (string) get_permalink($post_id);
$content_changed = false;

foreach ($rows as $index => $row) {
    if (! is_array($row)) {
        continue;
    }

    $layout = (string) ($row['acf_fc_layout'] ?? '');
    $heading = trim((string) ($row['heading'] ?? ''));

    // Referral CTA was left with a Research Ethics "Meeting dates" heading.
    if ($layout === 'content_cta' && strcasecmp($heading, 'Meeting dates') === 0) {
        $rows[$index]['heading'] = 'Referrals';
        $content_changed = true;
        $log(sprintf('Row %d: renamed Meeting dates → Referrals', $index));
    }

    // Truncated video heading from incomplete content gathering.
    if ($layout === 'video_showcase' && preg_match('/^Research and training on$/i', $heading)) {
        $rows[$index]['heading'] = 'Research and training on video';
        $content_changed = true;
        $log(sprintf('Row %d: completed video showcase heading', $index));
    }

    // REC "Sharing your research" body landed on SPIRE; restore migrate copy.
    if ($layout === 'content' && strcasecmp($heading, 'Sharing your research') === 0) {
        $rows[$index]['heading'] = 'Continue to research projects';
        $rows[$index]['intro_text'] = '<p>Explore current and past research projects from St Patrick\'s Mental Health Services.</p>';
        $rows[$index]['content'] = '<p>Browse studies across depression, ECT, eating disorders, pharmacy research, and more.</p>';
        $rows[$index]['primary_button'] = [
            'title' => 'View research projects',
            'url' => $research_projects_url,
            'target' => '',
        ];
        $rows[$index]['secondary_button'] = [
            'title' => 'Research Ethics Committee',
            'url' => $ethics_url,
            'target' => '',
        ];
        $content_changed = true;
        $log(sprintf('Row %d: replaced Sharing your research with Continue to research projects', $index));
    }

    // Useful links often kept an empty second link after content gathering.
    if ($layout === 'useful_links') {
        $links = is_array($row['links'] ?? null) ? $row['links'] : [];
        $valid = 0;

        foreach ($links as $link_row) {
            $link = is_array($link_row['link'] ?? null) ? $link_row['link'] : null;

            if (is_array($link) && ! empty($link['url']) && trim((string) ($link['title'] ?? '')) !== '') {
                $valid++;
            }
        }

        if ($valid < 2) {
            $rows[$index]['heading'] = $heading !== '' ? $heading : 'Useful Links';
            $rows[$index]['links'] = [
                ['link' => ['title' => 'Research Repository', 'url' => $spire_url, 'target' => '']],
                ['link' => ['title' => 'Research Projects', 'url' => $research_projects_url, 'target' => '']],
                ['link' => ['title' => 'Research Ethics Committee', 'url' => $ethics_url, 'target' => '']],
                ['link' => ['title' => 'About our research', 'url' => $about_research_url, 'target' => '']],
                ['link' => ['title' => 'Visit SPIRE', 'url' => $spire_external_url, 'target' => '_blank']],
            ];
            $content_changed = true;
            $log(sprintf('Row %d: restored research useful links (%d valid before)', $index, $valid));
        }
    }
}

if ($content_changed) {
    update_field($flex_field, $rows, $post_id);
    $log('Saved repaired SPIRE flexible_content_blocks.');
}

clean_post_cache($post_id);
$rows = get_field($flex_field, $post_id);

if (is_array($rows)) {
    foreach ($rows as $index => $row) {
        $layout = (string) ($row['acf_fc_layout'] ?? '?');
        $heading = trim((string) ($row['heading'] ?? ''));
        $disabled = function_exists('matrix_export_is_block_disabled')
            && matrix_export_is_block_disabled($post_id, $flex_field, (int) $index);
        $log(sprintf(
            '  %d. %s | %s | disabled=%s',
            $index,
            $layout,
            $heading !== '' ? $heading : '(no heading)',
            $disabled ? 'YES' : 'no'
        ));
    }
}

if ($is_cli) {
    WP_CLI::success('SPIRE disabled-block flags cleared.');
}
