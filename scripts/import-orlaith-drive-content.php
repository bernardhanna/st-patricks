<?php

/**
 * Import Orlaith's August Drive drafts, apply editorial-flag WP fixes,
 * generate gathering form links (including Team Members), and update the tracker xlsx.
 *
 * Usage:
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-orlaith-drive-content.php
 *   wp eval-file wp-content/themes/matrix-starter/scripts/import-orlaith-drive-content.php dry-run
 *
 * Safety: never deletes pages. NEWS-113 is trashed only because the client marked it "Can delete".
 */

if (! defined('ABSPATH')) {
    exit(1);
}

if (! class_exists('WP_CLI')) {
    exit(1);
}

require_once get_template_directory() . '/scripts/lib/page-seed-conventions.php';
require_once get_template_directory() . '/scripts/lib/orlaith-drive-links.php';
require_once get_template_directory() . '/scripts/lib/orlaith-page-helpers.php';
require_once get_template_directory() . '/scripts/lib/orlaith-depression-layout.php';
require_once get_template_directory() . '/scripts/lib/orlaith-anxiety-layout.php';
require_once get_template_directory() . '/scripts/lib/orlaith-carers-layout.php';
require_once get_template_directory() . '/scripts/lib/orlaith-suas-layout.php';
require_once get_template_directory() . '/scripts/lib/orlaith-rebuild-august-pages.php';
require_once get_template_directory() . '/inc/mental-health-functions.php';

$dry_run = in_array('dry-run', array_map('strval', $GLOBALS['argv'] ?? []), true);
$theme = get_template_directory();
$library = $theme . '/old/content/SPMHS-Content-Gathering-Library/02-Page-content';
$xlsx_path = $theme . '/old/content/St Patricks Content Migration  and gathering.xlsx';
$staging_home = 'https://st-patricks.s1.matrix-test.com';

$to_staging = static function (string $url) use ($staging_home): string {
    if ($url === '') {
        return '';
    }
    $parts = wp_parse_url($url);
    $path = isset($parts['path']) ? (string) $parts['path'] : '/';
    $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';

    return rtrim($staging_home, '/') . $path . $query;
};

if (! function_exists('matrix_orlaith_normalize_text')) {
    function matrix_orlaith_normalize_text(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\x{00A0}\x{202F}\x{2007}\x{2009}]/u', ' ', $text);

        return preg_replace('/\s+/u', ' ', $text) ?? $text;
    }
}

if (! function_exists('matrix_orlaith_clean_heading')) {
    function matrix_orlaith_clean_heading(string $text): string
    {
        $text = matrix_orlaith_normalize_text(wp_strip_all_tags($text));
        $text = preg_replace('/^H[1-6]:\s*/i', '', $text);

        return trim((string) $text);
    }
}

if (! function_exists('matrix_orlaith_is_skip_line')) {
    function matrix_orlaith_is_skip_line(string $text): bool
    {
        $plain = matrix_orlaith_normalize_text(matrix_orlaith_clean_heading($text));
        if ($plain === '') {
            return true;
        }
        if (preg_match('/^(Page name|Service users and visitors section|Healthcare Professionals folder|About Us folder)/i', $plain)) {
            return true;
        }
        if (preg_match('/section\s+[–-]\s*Level\s*[0-9]/i', $plain)) {
            return true;
        }
        if (preg_match('/^Meta\s*(title|description)/i', $plain)) {
            return true;
        }

        return false;
    }
}

if (! function_exists('matrix_orlaith_heading_match')) {
    /**
     * @return array{0:int,1:string}|null
     */
    function matrix_orlaith_heading_match(string $html): ?array
    {
        if (preg_match('#<(?:p|h[1-6])[^>]*>\s*(?:<strong>)?\s*H([1-6]):\s*(?:</strong>\s*<strong>)?(.*?)</(?:p|h[1-6])>#is', $html, $m)) {
            $inner = preg_replace('#<br\s*/?>.*$#is', '', $m[2]);

            return [(int) $m[1], matrix_orlaith_clean_heading((string) $inner)];
        }

        return null;
    }
}

if (! function_exists('matrix_orlaith_flexi_match')) {
    function matrix_orlaith_flexi_match(string $html): string
    {
        $plain = strtolower(matrix_orlaith_normalize_text(wp_strip_all_tags($html)));
        if (! preg_match('/flexi\s*blocks?|flexiblock/i', $plain)) {
            return '';
        }
        if (str_contains($plain, 'form')) {
            return 'form';
        }
        if (str_contains($plain, 'faq') || str_contains($plain, 'accordion')) {
            return 'accordion';
        }
        if (str_contains($plain, 'video')) {
            return 'video';
        }
        if (str_contains($plain, 'useful') || str_contains($plain, 'stories')) {
            return 'useful';
        }

        return $plain;
    }
}

if (! function_exists('matrix_orlaith_youtube_urls')) {
    /**
     * @return list<string>
     */
    function matrix_orlaith_youtube_urls(string $html): array
    {
        preg_match_all('#https?://(?:www\.)?(?:youtube\.com/watch\?[^"<\s]+|youtu\.be/[^"<\s]+)#i', $html, $m);

        return array_values(array_unique($m[0] ?? []));
    }
}

if (! function_exists('matrix_orlaith_parse_docx')) {
    /**
     * @return array{h1:string,intro:string,blocks:array<int,array<string,mixed>>,notes:list<string>}
     */
    function matrix_orlaith_parse_docx(string $docx_path): array
    {
        $tmp = sys_get_temp_dir() . '/orlaith-' . md5($docx_path) . '.html';
        $cmd = 'pandoc ' . escapeshellarg($docx_path) . ' -t html --wrap=none -o ' . escapeshellarg($tmp);
        exec($cmd, $out, $code);
        if ($code !== 0 || ! is_readable($tmp)) {
            return ['h1' => '', 'intro' => '', 'blocks' => [], 'notes' => ['pandoc failed for ' . basename($docx_path)]];
        }
        $html = (string) file_get_contents($tmp);
        $html = preg_replace('/[\x{00A0}\x{202F}\x{2007}\x{2009}]/u', ' ', $html);
        $html = preg_replace('#<img[^>]+src="media/[^"]+"[^>]*>#i', '', $html);
        $html = preg_replace('#<li>\s*<p>(.*?)</p>\s*</li>#is', '<li>$1</li>', $html);
        $html = preg_replace('#</?mark>#i', '', $html);
        $html = preg_replace('#</?u>#i', '', $html);
        $html = preg_replace_callback('#<h2[^>]*>(.*?)</h2>#is', static function ($m) {
            $plain = matrix_orlaith_clean_heading($m[1]);
            if (preg_match('/^H[1-6]:/i', wp_strip_all_tags($m[1]))) {
                return $m[0];
            }
            if (strlen($plain) > 90) {
                return '<p>' . $m[1] . '</p>';
            }

            return $m[0];
        }, $html);
        $html = preg_replace(
            '#<p>\s*<strong>H1:</strong>\s*<strong>(.*?)</strong>\s*<br\s*/?>\s*(.*?)</p>#is',
            '<p><strong>H1: $1</strong></p><p>$2</p>',
            $html
        );

        $chunks = preg_split('/(?=<(?:p|h[1-6])\b)/i', $html) ?: [];
        $h1 = '';
        $intro_parts = [];
        $blocks = [];
        $notes = [];
        $mode = 'preamble';
        $current = null;

        $flush = static function () use (&$blocks, &$current): void {
            if (! is_array($current)) {
                return;
            }
            if (($current['type'] ?? '') === 'content') {
                $current['html'] = trim((string) ($current['html'] ?? ''));
                if ($current['heading'] !== '' || $current['html'] !== '') {
                    $blocks[] = $current;
                }
            } elseif (($current['type'] ?? '') === 'accordion') {
                if (! empty($current['items'])) {
                    $blocks[] = $current;
                }
            } elseif (($current['type'] ?? '') === 'videos' && ! empty($current['urls'])) {
                $blocks[] = $current;
            }
            $current = null;
        };

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }
            $plain = wp_strip_all_tags($chunk);
            $flexi = matrix_orlaith_flexi_match($chunk);
            if ($flexi !== '') {
                if (str_contains($flexi, 'form')) {
                    $notes[] = 'Registration / Gravity form mentioned in draft — not created automatically.';
                    $mode = 'skip_form';
                    $flush();
                    continue;
                }
                if (str_contains($flexi, 'faq') || str_contains($flexi, 'accordion')) {
                    $flush();
                    $current = ['type' => 'accordion', 'heading' => '', 'items' => [], 'item' => null];
                    $mode = 'accordion';
                    continue;
                }
                if (str_contains($flexi, 'video')) {
                    $flush();
                    $current = ['type' => 'videos', 'heading' => 'Video resources', 'urls' => matrix_orlaith_youtube_urls($chunk)];
                    $mode = 'videos';
                    continue;
                }
                if (str_contains($flexi, 'useful') || str_contains($flexi, 'stories')) {
                    $flush();
                    $mode = 'preamble';
                    continue;
                }
            }
            if (preg_match('/Video embed:\s*(https?:\/\/\S+)/i', $plain, $vm)) {
                $flush();
                $blocks[] = ['type' => 'videos', 'heading' => 'Video', 'urls' => [$vm[1]]];
                continue;
            }
            if ($mode === 'skip_form') {
                continue;
            }
            if (matrix_orlaith_is_skip_line($plain)) {
                continue;
            }

            $heading = matrix_orlaith_heading_match($chunk);
            if ($heading) {
                [$level, $title] = $heading;
                if ($title === '') {
                    continue;
                }
                if ($level === 1 || ($h1 === '' && $level === 0 && $mode === 'preamble')) {
                    $h1 = $title;
                    $mode = 'intro';
                    continue;
                }
                if ($level <= 2 || ($level === 0 && $mode !== 'accordion')) {
                    $flush();
                    $current = ['type' => 'content', 'heading' => $title, 'heading_tag' => 'h2', 'html' => ''];
                    $mode = 'content';
                    continue;
                }
                if ($mode === 'accordion' && $level >= 3) {
                    if (is_array($current['item'] ?? null)) {
                        $current['items'][] = $current['item'];
                    }
                    $current['item'] = ['title' => $title, 'html' => ''];
                    continue;
                }
                if ($mode === 'content' && is_array($current) && $level >= 3) {
                    $tag = 'h' . min(6, $level);
                    $current['html'] .= '<' . $tag . '>' . esc_html($title) . '</' . $tag . '>';
                    continue;
                }
            }

            $youtube = matrix_orlaith_youtube_urls($chunk);
            if ($mode === 'videos' && $youtube !== [] && is_array($current)) {
                $current['urls'] = array_values(array_unique(array_merge($current['urls'], $youtube)));
                continue;
            }

            $body = trim($chunk);
            if ($body === '') {
                continue;
            }
            if ($mode === 'intro') {
                $intro_parts[] = $body;
                continue;
            }
            if ($mode === 'accordion' && is_array($current)) {
                if (! is_array($current['item'] ?? null)) {
                    $current['item'] = ['title' => '', 'html' => ''];
                }
                $current['item']['html'] .= $body;
                continue;
            }
            if ($mode === 'content' && is_array($current)) {
                $current['html'] .= $body;
            }
        }

        if (is_array($current) && ($current['type'] ?? '') === 'accordion' && is_array($current['item'] ?? null)) {
            $current['items'][] = $current['item'];
            unset($current['item']);
        }
        $flush();

        return [
            'h1' => $h1,
            'intro' => implode('', $intro_parts),
            'blocks' => $blocks,
            'notes' => $notes,
        ];
    }
}

if (! function_exists('matrix_orlaith_import_image')) {
    function matrix_orlaith_import_image(string $file, string $title, int $parent_id = 0): int
    {
        if (! is_readable($file) || ! preg_match('/\.(jpe?g|png|gif|webp)$/i', $file)) {
            return 0;
        }
        $key = 'drive:' . md5($file);
        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_key' => '_matrix_drive_source',
            'meta_value' => $key,
            'fields' => 'ids',
        ]);
        if ($existing !== []) {
            return (int) $existing[0];
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = wp_tempnam(basename($file));
        if (! $tmp || ! copy($file, $tmp)) {
            return 0;
        }
        $attachment_id = media_handle_sideload([
            'name' => sanitize_file_name(basename($file)),
            'tmp_name' => $tmp,
        ], $parent_id, $title);
        if (is_wp_error($attachment_id)) {
            @unlink($tmp);

            return 0;
        }
        update_post_meta((int) $attachment_id, '_matrix_drive_source', $key);

        return (int) $attachment_id;
    }
}

if (! function_exists('matrix_orlaith_first_image')) {
    function matrix_orlaith_first_image(string $dir): string
    {
        $files = glob($dir . '/*.{png,jpg,jpeg,webp,gif}', GLOB_BRACE) ?: [];

        return $files[0] ?? '';
    }
}

if (! function_exists('matrix_orlaith_hero')) {
    function matrix_orlaith_hero(string $title, string $intro_html, int $image_id): array
    {
        $has_image = $image_id > 0;

        return [
            'acf_fc_layout' => 'hero_with_breadcrumbs',
            'layout_style' => $has_image ? 'image_split' : 'title_accent',
            'show_breadcrumbs' => 1,
            'breadcrumb_source' => 'auto',
            'current_crumb_label' => $title,
            'heading_tag' => 'h1',
            'heading' => $title,
            'content' => $intro_html,
            'hero_image' => $has_image ? $image_id : '',
            'primary_button' => [
                'title' => '',
                'url' => '',
                'target' => '',
            ],
            'text_max_width' => 'default',
            'heading_max_width' => 'default',
            'background_color' => '#C6ECF4',
            'breadcrumb_background_color' => '#F1F8F9',
            'heading_color' => '#08284B',
            'text_color' => '#08284B',
        ];
    }
}

if (! function_exists('matrix_orlaith_content_block')) {
    function matrix_orlaith_content_block(string $heading, string $html, string $tag = 'h2', int $image_id = 0): array
    {
        $row = [
            'acf_fc_layout' => 'content',
            'heading' => $heading,
            'heading_tag' => $tag,
            'accent_position' => 'below_heading',
            'intro_text' => '',
            'content' => $html,
            'background_type' => 'white',
        ];
        if ($image_id > 0) {
            $row['image'] = $image_id;
            $row['layout_style'] = 'image_left';
        }

        return $row;
    }
}

if (! function_exists('matrix_orlaith_flexi_from_parse')) {
    /**
     * @param array{h1:string,intro:string,blocks:array<int,array<string,mixed>>} $parsed
     * @return list<array<string,mixed>>
     */
    function matrix_orlaith_flexi_from_parse(array $parsed, string $title, int $image_id): array
    {
        $rows = [matrix_orlaith_hero($parsed['h1'] !== '' ? $parsed['h1'] : $title, $parsed['intro'], $image_id)];
        foreach ($parsed['blocks'] as $block) {
            $type = (string) ($block['type'] ?? '');
            if ($type === 'content') {
                $rows[] = matrix_orlaith_content_block(
                    (string) ($block['heading'] ?? ''),
                    (string) ($block['html'] ?? ''),
                    (string) ($block['heading_tag'] ?? 'h2')
                );
            } elseif ($type === 'accordion') {
                $items = [];
                foreach ($block['items'] ?? [] as $item) {
                    $item_title = trim((string) ($item['title'] ?? ''));
                    if ($item_title === '') {
                        continue;
                    }
                    $items[] = [
                        'title' => $item_title,
                        'starts_open' => 0,
                        'content_rows' => [[
                            'row_type' => 'text',
                            'icon_key' => '',
                            'icon' => '',
                            'content' => (string) ($item['html'] ?? ''),
                        ]],
                    ];
                }
                if ($items !== []) {
                    $rows[] = [
                        'acf_fc_layout' => 'content_accordion',
                        'layout_style' => 'default',
                        'items' => $items,
                    ];
                }
            } elseif ($type === 'videos') {
                $slides = [];
                foreach ($block['urls'] ?? [] as $url) {
                    $slides[] = [
                        'poster_image' => '',
                        'video_source_type' => 'embed_url',
                        'video_embed_url' => $url,
                        'local_video_file' => '',
                        'caption' => '',
                        'cta_link' => '',
                    ];
                }
                if ($slides !== []) {
                    $rows[] = [
                        'acf_fc_layout' => 'video_showcase',
                        'heading_tag' => 'h2',
                        'heading' => (string) ($block['heading'] ?? 'Videos'),
                        'intro' => '',
                        'layout_style' => count($slides) > 1 ? 'feature_slider' : 'feature_single',
                        'slides' => $slides,
                    ];
                }
            }
        }

        return $rows;
    }
}

if (! function_exists('matrix_orlaith_ensure_page')) {
    function matrix_orlaith_ensure_page(string $title, string $slug, int $parent_id, string $post_type = 'page', string $status = 'draft'): int
    {
        if ($post_type === 'page' && $parent_id > 0) {
            $parent = get_post($parent_id);
            $path = ($parent instanceof WP_Post ? get_page_uri($parent) . '/' : '') . $slug;
            $existing = get_page_by_path($path);
            if ($existing instanceof WP_Post && $existing->post_type === 'page') {
                wp_update_post([
                    'ID' => $existing->ID,
                    'post_title' => $title,
                    'post_parent' => $parent_id,
                    'post_status' => $status,
                ]);

                return (int) $existing->ID;
            }
        }

        $found = get_posts([
            'post_type' => $post_type,
            'name' => $slug,
            'post_status' => ['publish', 'draft', 'pending', 'private'],
            'posts_per_page' => 1,
            'post_parent' => $post_type === 'page' ? $parent_id : 0,
        ]);
        if ($found !== []) {
            wp_update_post([
                'ID' => $found[0]->ID,
                'post_title' => $title,
                'post_status' => $status,
            ]);

            return (int) $found[0]->ID;
        }

        $id = wp_insert_post([
            'post_type' => $post_type,
            'post_status' => $status,
            'post_title' => $title,
            'post_name' => $slug,
            'post_parent' => $post_type === 'page' ? $parent_id : 0,
            'post_content' => '',
        ], true);

        return is_wp_error($id) ? 0 : (int) $id;
    }
}

if (! function_exists('matrix_orlaith_save_flexi')) {
    /**
     * @param list<array<string,mixed>> $rows
     */
    function matrix_orlaith_save_flexi(int $post_id, array $rows, bool $builder): void
    {
        if ($post_id <= 0 || ! function_exists('update_field')) {
            return;
        }
        update_field('hero_content_blocks', [], $post_id);
        update_field('flexible_content_blocks', $rows, $post_id);
        update_post_meta($post_id, '_matrix_orlaith_drive_import', gmdate('c'));
        if (class_exists('Matrix_Flexible_Pages')) {
            Matrix_Flexible_Pages::set_flexible_page($post_id, $builder);
        }
    }
}

$parent_service_users = matrix_seed_resolve_page_id_by_path('service-users-and-visitors');
$parent_about = matrix_seed_resolve_page_id_by_path('about-us');
$parent_hcp = matrix_seed_resolve_page_id_by_path('healthcare-professionals');
$parent_participation = matrix_seed_resolve_page_id_by_path('service-users-and-visitors/service-user-participation');
$parent_feedback = matrix_seed_resolve_page_id_by_path('service-users-and-visitors/feedback-and-comments');
$parent_policies = matrix_seed_resolve_page_id_by_path('about-us/policies-and-publications');
$willow_id = matrix_seed_resolve_page_id_by_path('service-users-and-visitors/your-care-with-willow-grove');
if ($willow_id === 0) {
    $willow_id = matrix_seed_resolve_page_id_by_path('service-users-and-visitors/your-stay-in-hospital-as-an-adolescent');
}
$depression_id = (int) (get_posts([
    'post_type' => 'mental_health',
    'name' => 'depression',
    'post_status' => 'any',
    'posts_per_page' => 1,
    'fields' => 'ids',
])[0] ?? 0);
$anxiety_id = (int) (get_posts([
    'post_type' => 'mental_health',
    'name' => 'anxiety',
    'post_status' => 'any',
    'posts_per_page' => 1,
    'fields' => 'ids',
])[0] ?? 0);
$psychosis_id = (int) (get_posts([
    'post_type' => 'mental_health',
    'name' => 'schizophrenia-psychosis',
    'post_status' => 'any',
    'posts_per_page' => 1,
    'fields' => 'ids',
])[0] ?? 0);

$specs = [
    [
        'sheet_id' => 'PAGE-145',
        'section' => 'Service Users',
        'title' => 'Carers and Supporters',
        'slug' => 'carers-and-supporters',
        'parent' => $parent_service_users,
        'folder' => $library . '/Service Users/Carers and Supporters',
        'docx' => 'Carers and Supporters.docx',
        'status' => 'publish',
        'mode' => 'Builder',
        'action' => 'Client drafting',
        'note' => 'New page from Drive (August 2026).',
    ],
    [
        'sheet_id' => 'PAGE-146',
        'section' => 'Service Users',
        'title' => 'Service User and Supporters Council',
        'slug' => 'service-user-and-supporters-council',
        'parent' => $parent_participation,
        'folder' => $library . '/Service Users/Service User and Supporters Council (SUAS)',
        'docx' => 'Service User and Supporters Council (SUAS).docx',
        'status' => 'publish',
        'mode' => 'Builder',
        'action' => 'Client drafting',
        'note' => 'New sub-page of Service User Participation.',
    ],
    [
        'sheet_id' => 'PAGE-147',
        'section' => 'Service Users',
        'title' => 'Service User Advisory Network',
        'slug' => 'service-user-advisory-network',
        'parent' => $parent_participation,
        'folder' => $library . '/Service Users/Service User Advisory Network',
        'docx' => 'Service User Advisory Network (SUAN) page.docx',
        'status' => 'publish',
        'mode' => 'Builder',
        'action' => 'Client drafting',
        'note' => 'SUAN registration form still needed (called out in the draft).',
    ],
    [
        'sheet_id' => 'PAGE-148',
        'section' => 'Service Users',
        'title' => 'Family, Carers and Supporters Advisory Network',
        'slug' => 'family-carers-and-supporters-advisory-network',
        'parent' => $parent_participation,
        'folder' => $library . '/Service Users/Family, Carers and Supporters Advisory Network',
        'docx' => 'Family, Carers and Supporters Advisory Network page.docx',
        'status' => 'publish',
        'mode' => 'Builder',
        'action' => 'Client drafting',
        'note' => 'New sub-page of Service User Participation.',
    ],
    [
        'sheet_id' => 'PAGE-149',
        'section' => 'Service Users',
        'title' => 'Service User Experience Survey',
        'slug' => 'service-user-experience-survey',
        'parent' => $parent_feedback,
        'folder' => $library . '/Service Users/Service User Experience Surveys',
        'docx' => 'Service User Experience Surveys.docx',
        'status' => 'publish',
        'mode' => 'Builder',
        'action' => 'Client drafting',
        'note' => 'New sub-page of Feedback and Comments.',
    ],
    [
        'sheet_id' => 'PAGE-150',
        'section' => 'Service Users',
        'title' => 'Your stay in hospital as an adolescent',
        'slug' => 'your-stay-in-hospital-as-an-adolescent',
        'parent' => 0,
        'folder' => $library . '/Service Users/Your stay in hospital as an adolescent',
        'docx' => 'Your stay as an adolescent inpatient Matrix.docx',
        'status' => 'publish',
        'mode' => 'Builder',
        'action' => 'Client drafting',
        'note' => 'Child of renamed Your care with Willow Grove.',
        'after_willow_rename' => true,
    ],
    [
        'sheet_id' => 'PAGE-151',
        'section' => 'Service Users',
        'title' => 'Your time in homecare as an adolescent',
        'slug' => 'your-time-in-homecare-as-an-adolescent',
        'parent' => 0,
        'folder' => $library . '/Service Users/Your time in homecare as an adolescent',
        'docx' => 'Your time in adolescent homecare Matrix.docx',
        'status' => 'publish',
        'mode' => 'Builder',
        'action' => 'Client drafting',
        'note' => 'Child of Your care with Willow Grove.',
        'after_willow_rename' => true,
    ],
    [
        'sheet_id' => 'PAGE-152',
        'section' => 'Service Users',
        'title' => 'Information for your family',
        'slug' => 'information-for-your-family',
        'parent' => 0,
        'folder' => $library . '/Service Users/Information for your family',
        'docx' => 'Information for your family Matrix.docx',
        'status' => 'publish',
        'mode' => 'Builder',
        'action' => 'Client drafting',
        'note' => 'Child of Your care with Willow Grove.',
        'after_willow_rename' => true,
    ],
    [
        'sheet_id' => 'PAGE-153',
        'section' => 'Healthcare Professionals',
        'title' => 'Involuntary Admissions',
        'slug' => 'involuntary-admissions',
        'parent' => $parent_hcp,
        'folder' => $library . '/Healthcare Professionals/Involuntary admissions',
        'docx' => 'Involuntary admissions.docx',
        'status' => 'publish',
        'mode' => 'Builder',
        'action' => 'Client drafting',
        'note' => 'New page; no images in Drive.',
    ],
    [
        'sheet_id' => 'PAGE-154',
        'section' => 'About Us',
        'title' => 'Safeguarding',
        'slug' => 'safeguarding',
        'parent' => $parent_policies,
        'folder' => $library . '/About Us/Safeguarding',
        'docx' => 'Safeguarding.docx',
        'status' => 'publish',
        'mode' => 'Builder',
        'action' => 'Client drafting',
        'note' => 'New sub-page of Policies and Publications.',
    ],
    [
        'sheet_id' => 'PAGE-155',
        'section' => 'About Us',
        'title' => 'Payment',
        'slug' => 'payment',
        'parent' => $parent_about,
        'folder' => $library . '/About Us/Payment',
        'docx' => 'Payment.docx',
        'status' => 'publish',
        'mode' => 'Builder',
        'action' => 'Client drafting',
        'note' => 'Content page under About Us. Make a Payment can stay an external link; this page sits beside it in the IA.',
    ],
];

$created = [];
$flag_updates = [];

WP_CLI::log('Parents: SU=' . $parent_service_users . ' About=' . $parent_about . ' HCP=' . $parent_hcp . ' Participation=' . $parent_participation . ' Feedback=' . $parent_feedback . ' Policies=' . $parent_policies . ' Willow=' . $willow_id);

if (! $dry_run && $willow_id > 0) {
    wp_update_post([
        'ID' => $willow_id,
        'post_title' => 'Your care with Willow Grove',
        'post_name' => 'your-care-with-willow-grove',
    ]);
    $willow_docx = $library . '/Service Users/Your care with Willow Grove/Your time in Willow Grove - inpatient and homecare Matrix.docx';
    $parsed = matrix_orlaith_parse_docx($willow_docx);
    $img = matrix_orlaith_first_image($library . '/Service Users/Your care with Willow Grove');
    $img_id = $img !== '' ? matrix_orlaith_import_image($img, 'Your care with Willow Grove', $willow_id) : 0;
    $rows = matrix_orlaith_flexi_from_parse($parsed, 'Attending Willow Grove', $img_id);
    matrix_orlaith_save_flexi($willow_id, $rows, false);
    matrix_orlaith_rebuild_willow_parent_layout($willow_id);
    $created['PAGE-114'] = [
        'id' => $willow_id,
        'title' => 'Your care with Willow Grove',
        'section' => 'Service Users',
        'mode' => 'Fixed',
        'action' => 'Client drafting',
        'note' => 'Renamed from Your stay in hospital as an adolescent. Drive copy imported.',
        'existing' => true,
    ];
    WP_CLI::log('Updated Willow Grove parent ' . $willow_id);
} elseif ($willow_id > 0) {
    WP_CLI::log('[dry-run] Would rename page ' . $willow_id . ' to Your care with Willow Grove');
}

if (! $dry_run && $psychosis_id > 0) {
    wp_update_post([
        'ID' => $psychosis_id,
        'post_title' => 'Psychosis',
    ]);
    $flexi = get_field('flexible_content_blocks', $psychosis_id);
    if (is_array($flexi)) {
        foreach ($flexi as &$row) {
            if (($row['acf_fc_layout'] ?? '') === 'hero_with_breadcrumbs' && (($row['heading'] ?? '') !== '')) {
                $row['heading'] = 'Psychosis';
                $row['current_crumb_label'] = 'Psychosis';
            }
        }
        unset($row);
        update_field('flexible_content_blocks', $flexi, $psychosis_id);
    }
    matrix_orlaith_rebuild_psychosis_rename_cleanup($psychosis_id);
    $created['PAGE-128'] = [
        'id' => $psychosis_id,
        'title' => 'Psychosis',
        'section' => 'Service Users',
        'mode' => 'Fixed',
        'action' => 'Client drafting',
        'note' => 'Renamed from Schizophrenia and psychosis. New Schizophrenia page is PAGE-156.',
        'existing' => true,
        'post_type' => 'mental_health',
    ];
    WP_CLI::log('Renamed mental_health ' . $psychosis_id . ' to Psychosis');
}

if (! $dry_run && $depression_id > 0) {
    matrix_orlaith_rebuild_depression_layout($depression_id);
    if (class_exists('Matrix_Flexible_Pages')) {
        Matrix_Flexible_Pages::set_flexible_page($depression_id, false);
    }
    $created['PAGE-124'] = [
        'id' => $depression_id,
        'title' => 'Depression',
        'section' => 'Service Users',
        'mode' => 'Fixed',
        'action' => 'Migrate',
        'note' => 'Drive copy imported. Hero uses the depression featured image; symptoms and treatment use distinct photos. Useful links moved to the bottom.',
        'existing' => true,
        'post_type' => 'mental_health',
    ];
    WP_CLI::log('Updated Depression ' . $depression_id);
}

if (! $dry_run && $anxiety_id > 0) {
    matrix_orlaith_rebuild_anxiety_layout($anxiety_id);
    if (class_exists('Matrix_Flexible_Pages')) {
        Matrix_Flexible_Pages::set_flexible_page($anxiety_id, false);
    }
    $created['PAGE-ANXIETY'] = [
        'id' => $anxiety_id,
        'title' => 'Anxiety',
        'section' => 'Service Users',
        'mode' => 'Fixed',
        'action' => 'Migrate',
        'note' => 'Hero uses the anxiety featured image; causes and treatment use distinct photos. Useful links moved to the bottom. Continue to removed.',
        'existing' => true,
        'post_type' => 'mental_health',
    ];
    WP_CLI::log('Updated Anxiety ' . $anxiety_id);
}

$schizophrenia_id = 0;
if (! $dry_run) {
    $schizophrenia_id = matrix_orlaith_ensure_page('Schizophrenia', 'schizophrenia', 0, 'mental_health', 'publish');
    $docx = $library . '/Service Users/Schizophrenia/Schizophrenia.docx';
    $parsed = matrix_orlaith_parse_docx($docx);
    $rows = matrix_orlaith_flexi_from_parse($parsed, 'Schizophrenia', 0);
    if ($depression_id > 0) {
        $dep_flexi = get_field('flexible_content_blocks', $depression_id);
        if (is_array($dep_flexi)) {
            foreach ($dep_flexi as $row) {
                if (($row['acf_fc_layout'] ?? '') === 'useful_links') {
                    array_splice($rows, 1, 0, [$row]);
                    break;
                }
            }
        }
    }
    matrix_orlaith_save_flexi($schizophrenia_id, $rows, true);
    matrix_orlaith_rebuild_schizophrenia_layout($schizophrenia_id);
    $created['PAGE-156'] = [
        'id' => $schizophrenia_id,
        'title' => 'Schizophrenia',
        'section' => 'Service Users',
        'mode' => 'Builder',
        'action' => 'Client drafting',
        'note' => 'New mental health condition page split out from Psychosis. No images in Drive.',
        'post_type' => 'mental_health',
    ];
    WP_CLI::log('Created/updated Schizophrenia ' . $schizophrenia_id);
}

if (! $dry_run && $willow_id > 0) {
    foreach ($specs as &$spec) {
        if (! empty($spec['after_willow_rename'])) {
            $spec['parent'] = $willow_id;
        }
    }
    unset($spec);
}

foreach ($specs as $spec) {
    $docx = $spec['folder'] . '/' . $spec['docx'];
    if (! is_readable($docx)) {
        WP_CLI::warning('Missing docx: ' . $docx);
        continue;
    }
    if ($dry_run) {
        WP_CLI::log('[dry-run] ' . $spec['sheet_id'] . ' ' . $spec['title']);
        continue;
    }
    $post_id = matrix_orlaith_ensure_page($spec['title'], $spec['slug'], (int) $spec['parent'], 'page', $spec['status']);
    if ($post_id <= 0) {
        WP_CLI::warning('Failed to create ' . $spec['title']);
        continue;
    }
    $parsed = matrix_orlaith_parse_docx($docx);
    $img = matrix_orlaith_first_image($spec['folder']);
    $img_id = $img !== '' ? matrix_orlaith_import_image($img, $spec['title'], $post_id) : 0;
    $rows = matrix_orlaith_flexi_from_parse($parsed, $spec['title'], $img_id);
    matrix_orlaith_save_flexi($post_id, $rows, $spec['mode'] === 'Builder');
    if ($spec['sheet_id'] === 'PAGE-145') {
        matrix_orlaith_rebuild_carers_layout($post_id);
    }
    if ($spec['sheet_id'] === 'PAGE-146') {
        matrix_orlaith_rebuild_suas_layout($post_id);
    }
    if ($spec['sheet_id'] === 'PAGE-147') {
        matrix_orlaith_rebuild_suan_layout($post_id);
    }
    if ($spec['sheet_id'] === 'PAGE-148') {
        matrix_orlaith_rebuild_fcs_layout($post_id);
    }
    if ($spec['sheet_id'] === 'PAGE-149') {
        matrix_orlaith_rebuild_survey_layout($post_id);
    }
    if ($spec['sheet_id'] === 'PAGE-150') {
        matrix_orlaith_rebuild_adolescent_stay_layout($post_id);
    }
    if ($spec['sheet_id'] === 'PAGE-151') {
        matrix_orlaith_rebuild_adolescent_homecare_layout($post_id);
    }
    if ($spec['sheet_id'] === 'PAGE-152') {
        matrix_orlaith_rebuild_family_info_layout($post_id);
    }
    if ($spec['sheet_id'] === 'PAGE-153') {
        matrix_orlaith_rebuild_involuntary_layout($post_id);
    }
    if ($spec['sheet_id'] === 'PAGE-154') {
        matrix_orlaith_rebuild_safeguarding_layout($post_id);
    }
    if ($spec['sheet_id'] === 'PAGE-155') {
        matrix_orlaith_rebuild_payment_layout($post_id);
    }
    if ($parsed['notes'] !== []) {
        $spec['note'] .= ' ' . implode(' ', $parsed['notes']);
    }
    $created[$spec['sheet_id']] = [
        'id' => $post_id,
        'title' => $spec['title'],
        'section' => $spec['section'],
        'mode' => $spec['mode'],
        'action' => $spec['action'],
        'note' => $spec['note'],
        'post_type' => 'page',
    ];
    WP_CLI::log($spec['sheet_id'] . ' -> ' . $post_id . ' ' . $spec['title']);
}

if (! $dry_run && $schizophrenia_id > 0) {
    $mh_page = get_page_by_path('mental-health');
    if ($mh_page instanceof WP_Post) {
        $flexi = get_field('flexible_content_blocks', $mh_page->ID);
        if (is_array($flexi)) {
            foreach ($flexi as &$row) {
                if (($row['acf_fc_layout'] ?? '') !== 'about_links_grid' || ! isset($row['links']) || ! is_array($row['links'])) {
                    continue;
                }
                $new_links = [];
                $inserted = false;
                foreach ($row['links'] as $card) {
                    $title = (string) ($card['title'] ?? '');
                    if ($title === 'Schizophrenia & Psychosis' || $title === 'Schizophrenia and psychosis') {
                        $card['title'] = 'Psychosis';
                        if (is_array($card['link'] ?? null)) {
                            $card['link']['title'] = 'Psychosis';
                            $card['link']['url'] = get_permalink($psychosis_id) ?: ($card['link']['url'] ?? '');
                        }
                        $new_links[] = $card;
                        $sch_card = $card;
                        $sch_card['title'] = 'Schizophrenia';
                        if (is_array($sch_card['link'] ?? null)) {
                            $sch_card['link']['title'] = 'Schizophrenia';
                            $sch_card['link']['url'] = get_permalink($schizophrenia_id) ?: '';
                        }
                        $new_links[] = $sch_card;
                        $inserted = true;
                    } else {
                        $new_links[] = $card;
                    }
                }
                if ($inserted) {
                    $row['links'] = $new_links;
                }
            }
            unset($row);
            update_field('flexible_content_blocks', $flexi, $mh_page->ID);
            WP_CLI::log('Updated Mental Health hub cards on ' . $mh_page->ID);
        }
    }
}

if (! $dry_run) {
    $nursing = get_page_by_path('nursing-in-st-patrick-s-mental-health-services', OBJECT, 'post');
    if ($nursing instanceof WP_Post) {
        wp_update_post([
            'ID' => $nursing->ID,
            'post_title' => 'Mental health nursing: A rewarding career',
        ]);
        $flag_updates[] = 'NEWS-122: renamed H1/title to “Mental health nursing: A rewarding career”.';
    }
    foreach ([3442, 3444] as $gp_id) {
        $gp = get_post($gp_id);
        if ($gp instanceof WP_Post && str_starts_with($gp->post_title, 'GP blog: ')) {
            wp_update_post([
                'ID' => $gp_id,
                'post_title' => trim(substr($gp->post_title, strlen('GP blog: '))),
            ]);
            $flag_updates[] = 'Removed “GP blog:” prefix from post ' . $gp_id . '.';
        }
    }
    $anxiety = get_post(1217);
    if ($anxiety instanceof WP_Post) {
        $clean = trim(wp_strip_all_tags((string) $anxiety->post_excerpt));
        wp_update_post([
            'ID' => 1217,
            'post_excerpt' => $clean,
        ]);
        $flag_updates[] = 'NEWS-087: stripped HTML from excerpt.';
    }
    $family = get_post(3446);
    if ($family instanceof WP_Post && ! str_contains((string) $family->post_content, 'na_pWyKKh0Q')) {
        $embed = '<p><iframe width="560" height="315" src="https://www.youtube.com/embed/na_pWyKKh0Q" title="Family therapy" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></p>';
        wp_update_post([
            'ID' => 3446,
            'post_content' => rtrim((string) $family->post_content) . "\n" . $embed,
        ]);
        $flag_updates[] = 'GP-010: embedded YouTube video na_pWyKKh0Q.';
    }
    $self_harm = get_page_by_path('exploring-the-experience-of-and-treatment-of-self-harm', OBJECT, 'post');
    if ($self_harm instanceof WP_Post && $self_harm->post_status !== 'trash') {
        wp_trash_post($self_harm->ID);
        $flag_updates[] = 'NEWS-113: moved to trash (client: broadcast no longer available).';
    }
    $home_flexi = get_field('flexible_content_blocks', 235);
    if (is_array($home_flexi)) {
        foreach ($home_flexi as &$row) {
            if (($row['acf_fc_layout'] ?? '') !== 'video_showcase' || empty($row['slides']) || ! is_array($row['slides'])) {
                continue;
            }
            $kept = [];
            foreach ($row['slides'] as $slide) {
                $url = (string) ($slide['video_embed_url'] ?? '');
                $has_poster = ! empty($slide['poster_image']);
                if ($url === '' || str_contains($url, 'ysz5S6PUM-U')) {
                    continue;
                }
                if (! $has_poster && $url === '') {
                    continue;
                }
                $kept[] = $slide;
            }
            $before = count($row['slides']);
            if ($kept !== [] && count($kept) < $before) {
                $row['slides'] = $kept;
                $flag_updates[] = 'PAGE-099: removed ' . ($before - count($kept)) . ' blank/placeholder video slides.';
            }
        }
        unset($row);
        update_field('flexible_content_blocks', $home_flexi, 235);
    }
    $teen_flexi = get_field('flexible_content_blocks', 217);
    if (is_array($teen_flexi)) {
        foreach ($teen_flexi as &$row) {
            if (($row['acf_fc_layout'] ?? '') === 'video_showcase') {
                $row['heading_tag'] = 'h2';
            }
        }
        unset($row);
        update_field('flexible_content_blocks', $teen_flexi, 217);
        $flag_updates[] = 'PAGE-098: video slider heading set to H2.';
    }
}

if ($dry_run) {
    WP_CLI::success('Dry run complete — no pages or xlsx written.');
    return;
}

if (! class_exists('Matrix_Export')) {
    WP_CLI::error('matrix-content-gathering plugin is not loaded.');
}

$autoload = WP_PLUGIN_DIR . '/matrix-content-gathering/vendor/autoload.php';
if (! is_readable($autoload)) {
    WP_CLI::error('PhpSpreadsheet missing.');
}
require_once $autoload;

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($xlsx_path);

$header_map = static function ($sheet): array {
    $headers = [];
    for ($col = 1; $col <= 40; $col++) {
        $val = trim((string) $sheet->getCellByColumnAndRow($col, 1)->getValue());
        if ($val !== '') {
            $headers[$val] = $col;
        }
    }

    return $headers;
};

$items = $spreadsheet->getSheetByName('Items');
$item_headers = $items ? $header_map($items) : [];
$col = static function (string $name, int $fallback) use (&$item_headers, $items) {
    if (isset($item_headers[$name])) {
        return $item_headers[$name];
    }
    $idx = empty($item_headers) ? $fallback : (max($item_headers) + 1);
    $item_headers[$name] = $idx;
    $items->setCellValueByColumnAndRow($idx, 1, $name);

    return $idx;
};

$all_page_ids = [];
if ($items) {
    $highest = (int) $items->getHighestRow();
    $last_data = 1;
    $id_col = $col('ID', 1);
    $title_col = $col('Title', 3);
    $section_col = $col('Menu / Section', 2);
    $status_col = $col('Client status', 4);
    $action_col = $col('Migration action', 5);
    $notes_col = $col('Editorial notes', 9);
    $wp_col = $col('WP post ID', 13);
    $wp_status_col = $col('WP status', 14);
    $stg_col = $col('Staging URL', 15);
    $recon_col = $col('Reconciliation', 16);
    $owner_col = $col('Owner', 17);
    $form_col = $col('Form Link', 19);
    $mode_col = $col('Form mode', 20);
    $drive_folder_col = $col('Drive folder', 21);
    $drive_word_col = $col('Drive Word', 22);
    $drive_links = matrix_orlaith_drive_links();

    $row_by_id = [];
    for ($row = 2; $row <= $highest; $row++) {
        $sid = trim((string) $items->getCellByColumnAndRow($id_col, $row)->getValue());
        if ($sid === '') {
            continue;
        }
        $last_data = $row;
        $row_by_id[$sid] = $row;
        $pid = (int) $items->getCellByColumnAndRow($wp_col, $row)->getValue();
        if ($pid > 0) {
            $all_page_ids[] = $pid;
        }
    }

    $title_updates = [
        'PAGE-114' => 'Your care with Willow Grove',
        'PAGE-128' => 'Psychosis',
    ];
    foreach ($title_updates as $sid => $new_title) {
        if (! isset($row_by_id[$sid])) {
            continue;
        }
        $r = $row_by_id[$sid];
        $items->setCellValueByColumnAndRow($title_col, $r, $new_title);
        if (isset($created[$sid])) {
            $pid = (int) $created[$sid]['id'];
            $items->setCellValueByColumnAndRow($wp_col, $r, $pid);
            $items->setCellValueByColumnAndRow($wp_status_col, $r, get_post_status($pid));
            $items->setCellValueByColumnAndRow($stg_col, $r, $to_staging((string) get_permalink($pid)));
            $items->setCellValueByColumnAndRow($notes_col, $r, $created[$sid]['note']);
            $all_page_ids[] = $pid;
        }
    }

    foreach ($created as $sid => $info) {
        if (isset($row_by_id[$sid])) {
            $r = $row_by_id[$sid];
            $items->setCellValueByColumnAndRow($wp_col, $r, $info['id']);
            $items->setCellValueByColumnAndRow($wp_status_col, $r, get_post_status($info['id']));
            $items->setCellValueByColumnAndRow($stg_col, $r, $to_staging((string) get_permalink($info['id'])));
            $items->setCellValueByColumnAndRow($mode_col, $r, $info['mode']);
            $items->setCellValueByColumnAndRow($notes_col, $r, $info['note']);
            $all_page_ids[] = (int) $info['id'];
            continue;
        }
        $last_data++;
        $r = $last_data;
        $items->setCellValueByColumnAndRow($id_col, $r, $sid);
        $items->setCellValueByColumnAndRow($section_col, $r, $info['section']);
        $items->setCellValueByColumnAndRow($title_col, $r, $info['title']);
        $items->setCellValueByColumnAndRow($status_col, $r, 'SPMHS drafting');
        $items->setCellValueByColumnAndRow($action_col, $r, $info['action']);
        $items->setCellValueByColumnAndRow($notes_col, $r, $info['note']);
        $items->setCellValueByColumnAndRow($wp_col, $r, $info['id']);
        $items->setCellValueByColumnAndRow($wp_status_col, $r, get_post_status($info['id']));
        $items->setCellValueByColumnAndRow($stg_col, $r, $to_staging((string) get_permalink($info['id'])));
        $items->setCellValueByColumnAndRow($recon_col, $r, 'Client drafting');
        $items->setCellValueByColumnAndRow($owner_col, $r, 'Client');
        $items->setCellValueByColumnAndRow($mode_col, $r, $info['mode']);
        $row_by_id[$sid] = $r;
        $all_page_ids[] = (int) $info['id'];
    }

    foreach ($drive_links as $sid => $drive) {
        if (! isset($row_by_id[$sid])) {
            continue;
        }
        $r = $row_by_id[$sid];
        $items->setCellValueByColumnAndRow($drive_folder_col, $r, $drive['drive_folder']);
        $items->setCellValueByColumnAndRow($drive_word_col, $r, $drive['drive_word']);
        if ($drive['drive_folder'] !== '') {
            $items->getCellByColumnAndRow($drive_folder_col, $r)->getHyperlink()->setUrl($drive['drive_folder']);
        }
        if ($drive['drive_word'] !== '') {
            $items->getCellByColumnAndRow($drive_word_col, $r)->getHyperlink()->setUrl($drive['drive_word']);
        }
    }
}

$set = $spreadsheet->getSheetByName('Set Pages');
if ($set && $willow_id > 0) {
    $highest = (int) $set->getHighestRow();
    for ($row = 2; $row <= $highest; $row++) {
        $name = (string) $set->getCell('A' . $row)->getValue();
        $pid = (int) $set->getCell('E' . $row)->getValue();
        if ($pid === $willow_id || str_contains($name, 'Adolescent')) {
            $set->setCellValue('A' . $row, 'Your care with Willow Grove');
            $set->setCellValue('B' . $row, $to_staging((string) get_permalink($willow_id)));
            $set->setCellValue('E' . $row, $willow_id);
        }
        if ($pid > 0) {
            $all_page_ids[] = $pid;
        }
    }
}

$all_page_ids = array_values(array_unique(array_filter(array_map('intval', $all_page_ids))));
$page_token = Matrix_Export::create_client_link($all_page_ids, [
    'expires_days' => 0,
    'custom_instructions' => 'St Patrick\'s content gathering — August 2026 Drive import. Use the page dropdown. New pages are in Builder mode.',
    'requires_approval' => false,
    'strict_mode' => false,
    'ai_mode' => false,
]);
$page_base = $to_staging(Matrix_Export::get_client_link_url($page_token));
$form_for = static function (int $pid) use ($page_base, $to_staging): string {
    if ($pid <= 0) {
        return '';
    }

    return $to_staging(add_query_arg('matrix_page', $pid, $page_base));
};

if ($items) {
    $highest = (int) $items->getHighestRow();
    $form_col = $item_headers['Form Link'] ?? 19;
    $wp_col = $item_headers['WP post ID'] ?? 13;
    for ($row = 2; $row <= $highest; $row++) {
        $pid = (int) $items->getCellByColumnAndRow($wp_col, $row)->getValue();
        if ($pid > 0 && get_post($pid)) {
            $items->setCellValueByColumnAndRow($form_col, $row, $form_for($pid));
        }
    }
}
if ($set) {
    $highest = (int) $set->getHighestRow();
    for ($row = 2; $row <= $highest; $row++) {
        $pid = (int) $set->getCell('E' . $row)->getValue();
        if ($pid > 0 && get_post($pid)) {
            $set->setCellValue('C' . $row, $form_for($pid));
        }
    }
}

$team_posts = get_posts([
    'post_type' => 'team_members',
    'post_status' => ['publish', 'draft'],
    'numberposts' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
]);
$team_ids = array_map(static fn ($p) => (int) $p->ID, $team_posts);
foreach ($team_ids as $tid) {
    Matrix_Flexible_Pages::set_flexible_page($tid, false);
}
$team_token = Matrix_Export::create_client_link($team_ids, [
    'expires_days' => 0,
    'custom_instructions' => 'Team member forms — edit name, job title, biography, and profile photo. Placeholder rows named “Team member name” should be replaced with real people.',
    'requires_approval' => false,
    'strict_mode' => false,
    'ai_mode' => false,
]);
$team_base = $to_staging(Matrix_Export::get_client_link_url($team_token));

$existing_team_sheet = $spreadsheet->getSheetByName('Team Members');
if ($existing_team_sheet) {
    $team_sheet = $existing_team_sheet;
    $team_sheet->removeRow(1, max(1, (int) $team_sheet->getHighestRow()));
} else {
    $team_sheet = $spreadsheet->createSheet();
    $team_sheet->setTitle('Team Members');
}
$team_headers = ['ID', 'Name', 'Job title', 'Categories', 'WP post ID', 'WP status', 'Staging URL', 'Has photo?', 'Form Link', 'Notes'];
foreach ($team_headers as $i => $h) {
    $team_sheet->setCellValueByColumnAndRow($i + 1, 1, $h);
}
$n = 2;
$tm_index = 1;
foreach ($team_posts as $member) {
    $job = (string) (get_field('job_title', $member->ID) ?: '');
    $cats = wp_get_object_terms($member->ID, 'team_member_category', ['fields' => 'names']);
    $cats = is_wp_error($cats) ? [] : $cats;
    $placeholder = strcasecmp($member->post_title, 'Team member name') === 0;
    $team_sheet->setCellValueByColumnAndRow(1, $n, sprintf('TEAM-%03d', $tm_index));
    $team_sheet->setCellValueByColumnAndRow(2, $n, $member->post_title);
    $team_sheet->setCellValueByColumnAndRow(3, $n, $job);
    $team_sheet->setCellValueByColumnAndRow(4, $n, implode(', ', $cats));
    $team_sheet->setCellValueByColumnAndRow(5, $n, $member->ID);
    $team_sheet->setCellValueByColumnAndRow(6, $n, $member->post_status);
    $team_sheet->setCellValueByColumnAndRow(7, $n, $to_staging((string) get_permalink($member->ID)));
    $team_sheet->setCellValueByColumnAndRow(8, $n, get_post_thumbnail_id($member->ID) ? 'Y' : 'N');
    $team_sheet->setCellValueByColumnAndRow(9, $n, $to_staging(add_query_arg('matrix_page', $member->ID, $team_base)));
    $team_sheet->setCellValueByColumnAndRow(10, $n, $placeholder ? 'Placeholder — replace name, role, biography, and photo.' : 'Edit details and image via Form Link.');
    $n++;
    $tm_index++;
}

$flags = $spreadsheet->getSheetByName('Editorial flags');
if ($flags) {
    $flags->setCellValue('G1', 'Matrix update (2026-08-17)');
    $flag_map = [
        'PAGE-141' => 'Leave for later as requested. Sitemap is generated by the site; no client draft needed.',
        'PAGE-142' => 'Leave for later as requested. Matrix will supply accessibility statement copy.',
        'NEWS-113' => 'Trashed on staging — client confirmed the broadcast is no longer available.',
        'NEWS-122' => 'Title/H1 updated to “Mental health nursing: A rewarding career”.',
        'NEWS-087' => 'HTML stripped from the excerpt.',
        'GP-005' => 'Removed “GP blog:” from the post title.',
        'GP-007' => 'Removed “GP blog:” from the post title.',
        'GP-010' => 'YouTube video embedded on the clinician insight post.',
        'PAGE-099' => 'Blank/placeholder video slides removed from the Refer to St Patrick’s at Home slider.',
        'PAGE-098' => 'Video slider heading tag set to H2 so heading styles apply.',
        'NEWS-079' => 'Still needs Matrix help linking Ukrainian files that will not open in the form. Files live under Media library › Ukrainian resources.',
        'NEWS-015' => 'Still needs Matrix to attach Annual Report files — form cannot upload arbitrary PDFs to a news post easily. Drive copies are in 03-Media-library/Files and documents/Reports.',
        'NEWS-003' => 'Still needs the amended CFT scale file uploaded to the news post.',
        'NEWS-018' => 'Title/H1 cannot be changed in the client form; send the intended new title and Matrix will update it.',
        'NEWS-017' => 'Title/H1 cannot be changed in the client form; send the intended new title and Matrix will update it.',
        'NEWS-022' => 'Title/H1 cannot be changed in the client form; send the intended new title and Matrix will update it.',
        'NEWS-124' => 'Left on editorial hold for client review before publish.',
    ];
    $highest = (int) $flags->getHighestRow();
    $last_flag = 1;
    for ($row = 2; $row <= min($highest, 50); $row++) {
        $sid = trim((string) $flags->getCell('A' . $row)->getValue());
        if ($sid === '') {
            continue;
        }
        $last_flag = $row;
        if (isset($flag_map[$sid])) {
            $flags->setCellValue('G' . $row, $flag_map[$sid]);
        }
    }
    $extra = $last_flag + 1;
    $flags->setCellValue('A' . $extra, 'PAGE-147');
    $flags->setCellValue('B' . $extra, 'Service User Advisory Network');
    $flags->setCellValue('C' . $extra, 'Service Users');
    $flags->setCellValue('D' . $extra, 'Other');
    $flags->setCellValue('E' . $extra, 'Draft asks for a SUAN registration form. Gravity Forms is not on this site yet — Matrix to add.');
    $flags->setCellValue('G' . $extra, 'Logged 2026-08-17. Page content imported; form still outstanding.');
}

$summary = $spreadsheet->getSheetByName('Summary');
if ($summary) {
    $summary->setCellValue('A24', 'Content gathering forms');
    $summary->setCellValue('B24', 'Drive import ' . gmdate('Y-m-d H:i') . ' UTC');
    $summary->setCellValue('A25', 'Form token (pages)');
    $summary->setCellValue('B25', $page_token);
    $summary->setCellValue('A26', 'Base form URL (pages)');
    $summary->setCellValue('B26', $page_base);
    $summary->setCellValue('A27', 'Team Members form token');
    $summary->setCellValue('B27', $team_token);
    $summary->setCellValue('A28', 'Team Members base form URL');
    $summary->setCellValue('B28', $team_base);
    $summary->setCellValue('A29', 'New Drive pages this run');
    $summary->setCellValue('B29', count($created));
}

$instructions = $spreadsheet->getSheetByName('Instructions');
if ($instructions) {
    $instructions->setCellValue('A20', '8. Team Members tab: each row has a Form Link to edit that person’s name, job title, biography, and photo.');
    $instructions->setCellValue('A21', '9. New pages from the August Drive drop are PAGE-145 to PAGE-156 (drafts) plus renamed Willow Grove / Psychosis.');
    $instructions->setCellValue('A22', '10. Accessibility, sitemap, and cookies can wait — Matrix will supply those later.');
}

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save($xlsx_path);

$csv_path = $theme . '/old/content/ORLAITH-AUGUST-NEW-PAGES.csv';
$fh = fopen($csv_path, 'w');
$drive_links = matrix_orlaith_drive_links();
fputcsv($fh, ['Sheet ID', 'Section', 'Title', 'WP post ID', 'Status', 'Form mode', 'Staging URL', 'Drive folder', 'Drive Word', 'Form Link', 'Notes']);
foreach ($created as $sid => $info) {
    $drive = $drive_links[$sid] ?? ['drive_folder' => '', 'drive_word' => ''];
    fputcsv($fh, [
        $sid,
        $info['section'],
        $info['title'],
        $info['id'],
        get_post_status($info['id']),
        $info['mode'],
        $to_staging((string) get_permalink($info['id'])),
        $drive['drive_folder'],
        $drive['drive_word'],
        $form_for((int) $info['id']),
        $info['note'],
    ]);
}
fclose($fh);

file_put_contents(
    $theme . '/old/content/matrix-export-client-links-snapshot.json',
    wp_json_encode([
        'page_token' => $page_token,
        'page_base' => $page_base,
        'team_token' => $team_token,
        'team_base' => $team_base,
        'created' => $created,
        'flag_updates' => $flag_updates,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

WP_CLI::success('Imported ' . count($created) . ' Drive pages/updates.');
WP_CLI::success('Pages form: ' . $page_base);
WP_CLI::success('Team form: ' . $team_base);
WP_CLI::log('Updated ' . $xlsx_path);
foreach ($flag_updates as $line) {
    WP_CLI::log('Flag: ' . $line);
}
