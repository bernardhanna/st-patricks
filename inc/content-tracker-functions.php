<?php

/**
 * Content migration tracker — staging URLs, editorial flags, admin list styling.
 */

if (! defined('MATRIX_CONTENT_STAGING_URL')) {
    define('MATRIX_CONTENT_STAGING_URL', 'https://st-patricks.s1.matrix-test.com');
}

if (! function_exists('matrix_content_staging_permalink')) {
    function matrix_content_staging_permalink(int $post_id): string
    {
        if ($post_id <= 0) {
            return '';
        }

        $local = get_permalink($post_id);

        if (! is_string($local) || $local === '') {
            return MATRIX_CONTENT_STAGING_URL . '/?p=' . $post_id;
        }

        $path = (string) wp_parse_url($local, PHP_URL_PATH);
        $query = (string) wp_parse_url($local, PHP_URL_QUERY);

        if ($path !== '' && $path !== '/') {
            return rtrim(MATRIX_CONTENT_STAGING_URL, '/') . $path;
        }

        if ($query !== '') {
            return rtrim(MATRIX_CONTENT_STAGING_URL, '/') . '/?' . $query;
        }

        return rtrim(MATRIX_CONTENT_STAGING_URL, '/') . '/?p=' . $post_id;
    }
}

if (! function_exists('matrix_content_parse_sheet_date')) {
    /**
     * Parse client sheet dates: d.m.yy, dd.mm.yy, dd Month yyyy.
     */
    function matrix_content_parse_sheet_date(string $date_text, string $old_path = ''): ?string
    {
        $date_text = trim($date_text);

        if ($date_text !== '') {
            if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{2,4})$/', $date_text, $m)) {
                $day = (int) $m[1];
                $month = (int) $m[2];
                $year = (int) $m[3];

                if ($year < 100) {
                    $year += $year >= 70 ? 1900 : 2000;
                }

                if (checkdate($month, $day, $year)) {
                    return sprintf('%04d-%02d-%02d 09:00:00', $year, $month, $day);
                }
            }

            if (preg_match('/(\d{1,2})\s+([A-Za-z]+),?\s+(\d{4})/', $date_text, $m)) {
                $timestamp = strtotime($m[1] . ' ' . $m[2] . ' ' . $m[3]);

                if ($timestamp !== false) {
                    return gmdate('Y-m-d H:i:s', $timestamp);
                }
            }

            if (preg_match('/^(Spring|Summer|Autumn|Fall|Winter)\s+(\d{4})$/i', $date_text, $m)) {
                $season = strtolower($m[1]);
                $year = (int) $m[2];
                $month = match ($season) {
                    'spring' => 3,
                    'summer' => 6,
                    'autumn', 'fall' => 9,
                    'winter' => 12,
                    default => 1,
                };

                return sprintf('%04d-%02d-%02d 09:00:00', $year, $month, 1);
            }
        }

        $old_path = trim($old_path, '/');

        if ($old_path !== '' && preg_match('#st-patricks-mental-health-services-enewsletter/(spring|summer|autumn|winter|fall)-(\d{4})/#i', $old_path, $m)) {
            $season = strtolower($m[1]);
            $year = (int) $m[2];
            $month = match ($season) {
                'spring' => 3,
                'summer' => 6,
                'autumn', 'fall' => 9,
                'winter' => 12,
                default => 1,
            };

            return sprintf('%04d-%02d-%02d 09:00:00', $year, $month, 1);
        }

        if ($old_path !== '' && preg_match('#st-patricks-mental-health-services-enewsletter/([a-z]+)-(\d{4})/#i', $old_path, $m)) {
            $timestamp = strtotime('1 ' . $m[1] . ' ' . $m[2]);

            if ($timestamp !== false) {
                return gmdate('Y-m-d H:i:s', $timestamp);
            }
        }

        if ($old_path !== '' && preg_match('#st-patricks-mental-health-services-enewsletter/([a-z]+)(\d{4})/#i', $old_path, $m)) {
            $timestamp = strtotime('1 ' . $m[1] . ' ' . $m[2]);

            if ($timestamp !== false) {
                return gmdate('Y-m-d H:i:s', $timestamp);
            }
        }

        return null;
    }
}

if (! function_exists('matrix_content_set_editorial_flag')) {
    function matrix_content_set_editorial_flag(int $post_id, string $action, string $notes): void
    {
        if ($post_id <= 0) {
            return;
        }

        if ($action === '' || $action === 'None') {
            delete_post_meta($post_id, '_matrix_content_editorial_action');
            delete_post_meta($post_id, '_matrix_content_editorial_note');

            return;
        }

        update_post_meta($post_id, '_matrix_content_editorial_action', $action);
        update_post_meta($post_id, '_matrix_content_editorial_note', $notes);
    }
}

if (! function_exists('matrix_content_editorial_flag_label')) {
    function matrix_content_editorial_flag_label(int $post_id): string
    {
        $action = (string) get_post_meta($post_id, '_matrix_content_editorial_action', true);
        $note = (string) get_post_meta($post_id, '_matrix_content_editorial_note', true);

        if ($action === '') {
            return '';
        }

        return $note !== '' ? $action . ': ' . $note : $action;
    }
}

add_filter('manage_post_posts_columns', static function (array $columns): array {
    $out = [];

    foreach ($columns as $key => $label) {
        $out[$key] = $label;

        if ($key === 'title') {
            $out['matrix_content_flag'] = __('Content flag', 'matrix-starter');
        }
    }

    return $out;
});

add_action('manage_post_posts_custom_column', static function (string $column, int $post_id): void {
    if ($column !== 'matrix_content_flag') {
        return;
    }

    $label = matrix_content_editorial_flag_label($post_id);

    if ($label === '') {
        echo '—';

        return;
    }

    printf(
        '<span class="matrix-content-flag-pill" title="%s">%s</span>',
        esc_attr($label),
        esc_html((string) get_post_meta($post_id, '_matrix_content_editorial_action', true))
    );
}, 10, 2);

add_filter('post_class', static function (array $classes, array $css_class, int $post_id): array {
    if ($post_id > 0 && get_post_meta($post_id, '_matrix_content_editorial_action', true)) {
        $classes[] = 'matrix-has-editorial-flag';
    }

    return $classes;
}, 10, 3);

add_filter('admin_body_class', static function (string $classes): string {
    global $pagenow;

    if ($pagenow === 'edit.php' && ($_GET['post_type'] ?? 'post') === 'post') {
        $classes .= ' matrix-content-tracker-posts';
    }

    return $classes;
});

add_action('admin_head', static function (): void {
    global $pagenow;

    if ($pagenow !== 'edit.php' || ($_GET['post_type'] ?? 'post') !== 'post') {
        return;
    }

    echo '<style>
        .matrix-content-flag-pill{
            display:inline-block;
            padding:2px 8px;
            border-radius:999px;
            background:#FEF3C7;
            color:#92400E;
            font-size:11px;
            font-weight:600;
            max-width:180px;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
        }
        body.matrix-content-tracker-posts .wp-list-table tr:has(.matrix-content-flag-pill){
            background-color:#FFFBEB !important;
        }
        body.matrix-content-tracker-posts .wp-list-table tr:has(.matrix-content-flag-pill):nth-child(odd){
            background-color:#FEF9E7 !important;
        }
    </style>';
});
