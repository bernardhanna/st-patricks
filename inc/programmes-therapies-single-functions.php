<?php

function matrix_get_programmes_therapies_single_defaults()
{
    return [
        'back_label' => 'Back to programmes',
    ];
}

/**
 * Convert workbook drafting labels (H2 / H3 Title) into real heading tags.
 *
 * Content editors use "H2 …" / "H3 …" as hierarchy guides in drafts; those
 * should never render as visible text.
 */
function matrix_convert_workbook_heading_guides(string $html): string
{
    $html = trim($html);

    if ($html === '' || ! preg_match('/\bH[1-6]\b/', $html)) {
        return $html;
    }

    return (string) preg_replace_callback(
        '#<p(?:\s[^>]*)?>\s*(.*?)\s*</p>#is',
        static function (array $matches): string {
            $inner = $matches[1];
            $text = trim(html_entity_decode(wp_strip_all_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

            if (! preg_match('/^H([1-6])\s*[:.\-]?\s+(.+)$/u', $text, $heading_match)) {
                return $matches[0];
            }

            $level = $heading_match[1];
            $title = trim($heading_match[2]);

            if ($title === '') {
                return $matches[0];
            }

            return '<h' . $level . '>' . esc_html($title) . '</h' . $level . '>';
        },
        $html
    );
}

function matrix_filter_workbook_heading_guides_in_content(string $content): string
{
    if ($content === '') {
        return '';
    }

    return matrix_convert_workbook_heading_guides($content);
}

if (function_exists('add_filter')) {
    add_filter('the_content', 'matrix_filter_workbook_heading_guides_in_content', 12);
}

function matrix_get_programmes_therapies_archive_url()
{
    $day_programmes = get_page_by_path('what-we-offer/day-programmes');

    if ($day_programmes instanceof WP_Post) {
        $url = get_permalink($day_programmes);

        if (is_string($url) && $url !== '') {
            return $url . '#select-programme-or-therapy';
        }
    }

    $index_page = get_page_by_path('programmes-therapies');

    if ($index_page instanceof WP_Post) {
        $url = get_permalink($index_page);

        if (is_string($url) && $url !== '') {
            return $url . '#select-programme-or-therapy';
        }
    }

    return home_url('/what-we-offer/day-programmes/#select-programme-or-therapy');
}

function matrix_get_programmes_therapies_intro($post_id = null)
{
    $post_id = (int) ($post_id ?: get_the_ID());

    if ($post_id < 1) {
        return '';
    }

    return matrix_get_programmes_therapies_post_summary($post_id);
}
