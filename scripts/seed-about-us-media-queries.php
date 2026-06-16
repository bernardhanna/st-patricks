<?php

/**
 * Seed About Us > Media Queries (page 262) with content from stpatricks.ie/media-centre.
 * Preserves the existing 4-block layout and only updates copy, links, and spokespeople.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-about-us-media-queries.php
 */

require_once __DIR__ . '/lib/page-seed-conventions.php';

$post_id = (int) (get_page_by_path('about-us/media-queries')?->ID ?? 0);

if ($post_id === 0) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Could not find page at about-us/media-queries.');
    }

    exit(1);
}

if (! function_exists('matrix_seed_import_theme_image')) {
    function matrix_seed_import_theme_image(string $relative_path, string $title): int
    {
        $file = get_template_directory() . '/' . ltrim($relative_path, '/');

        if (! is_readable($file)) {
            return 0;
        }

        $filename = basename($file);
        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_source',
                    'value' => $relative_path,
                ],
            ],
        ]);

        if ($existing !== []) {
            return (int) $existing[0]->ID;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $upload = wp_upload_bits($filename, null, (string) file_get_contents($file));

        if (! empty($upload['error'])) {
            return 0;
        }

        $filetype = wp_check_filetype($filename, null);
        $attachment_id = wp_insert_attachment([
            'post_mime_type' => $filetype['type'] ?: 'image/png',
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'inherit',
        ], $upload['file']);

        if (is_wp_error($attachment_id) || ! $attachment_id) {
            return 0;
        }

        $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $metadata);
        update_post_meta($attachment_id, '_matrix_seed_source', $relative_path);

        return (int) $attachment_id;
    }
}

if (! function_exists('matrix_seed_import_remote_image')) {
    function matrix_seed_import_remote_image(string $url, string $title, string $cache_key): int
    {
        if ($url === '') {
            return 0;
        }

        $existing = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_figma_key',
                    'value' => $cache_key,
                ],
            ],
        ]);

        if ($existing !== []) {
            return (int) $existing[0]->ID;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $tmp = download_url($url, 30);

        if (is_wp_error($tmp)) {
            return 0;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $filename = $path ? basename($path) : 'figma-asset.jpg';

        if (! preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $filename)) {
            $filename .= '.jpg';
        }

        $file_array = [
            'name' => sanitize_file_name($filename),
            'tmp_name' => $tmp,
        ];

        $attachment_id = media_handle_sideload($file_array, 0, $title);

        if (is_wp_error($attachment_id)) {
            @unlink($tmp);

            return 0;
        }

        update_post_meta($attachment_id, '_matrix_seed_figma_key', $cache_key);
        update_post_meta($attachment_id, '_matrix_seed_figma_url', $url);

        return (int) $attachment_id;
    }
}

if (! function_exists('matrix_seed_resolve_image')) {
    function matrix_seed_resolve_image(string $figma_url, string $cache_key, string $title, string $theme_fallback = ''): int
    {
        $id = matrix_seed_import_remote_image($figma_url, $title, $cache_key);

        if ($id > 0) {
            return $id;
        }

        if ($theme_fallback !== '') {
            $id = matrix_seed_import_theme_image($theme_fallback, $title);

            if ($id > 0) {
                return $id;
            }
        }

        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => 1,
            'post_mime_type' => 'image',
            'orderby' => 'ID',
            'order' => 'DESC',
        ]);

        return $attachments !== [] ? (int) $attachments[0]->ID : 0;
    }
}

if (! function_exists('matrix_seed_ensure_team_category')) {
    function matrix_seed_ensure_team_category(string $slug, string $name): int
    {
        $term = get_term_by('slug', $slug, 'team_member_category');

        if ($term instanceof WP_Term) {
            return (int) $term->term_id;
        }

        $created = wp_insert_term($name, 'team_member_category', ['slug' => $slug]);

        if (is_wp_error($created)) {
            return 0;
        }

        return (int) ($created['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_seed_find_team_member_by_title')) {
    function matrix_seed_find_team_member_by_title(string $title): int
    {
        $matches = get_posts([
            'post_type' => 'team_members',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'title' => $title,
        ]);

        return $matches !== [] ? (int) $matches[0]->ID : 0;
    }
}

if (! function_exists('matrix_seed_ensure_spokesperson')) {
    function matrix_seed_ensure_spokesperson(
        string $title,
        string $job_title,
        string $profile_teaser,
        int $image_id,
        string $seed_key,
        int $spokespeople_term_id
    ): int {
        $existing = get_posts([
            'post_type' => 'team_members',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'meta_query' => [
                [
                    'key' => '_matrix_seed_key',
                    'value' => $seed_key,
                ],
            ],
        ]);

        if ($existing !== []) {
            $member_id = (int) $existing[0]->ID;
            wp_update_post([
                'ID' => $member_id,
                'post_title' => $title,
                'post_status' => 'publish',
            ]);
        } else {
            $member_id = $title === 'Team member name'
                ? 0
                : matrix_seed_find_team_member_by_title($title);

            if ($member_id < 1) {
                $member_id = wp_insert_post([
                    'post_type' => 'team_members',
                    'post_status' => 'publish',
                    'post_title' => $title,
                ]);

                if (is_wp_error($member_id) || ! $member_id) {
                    return 0;
                }
            }

            update_post_meta((int) $member_id, '_matrix_seed_key', $seed_key);
        }

        update_field('job_title', $job_title, $member_id);
        update_field('profile_teaser', $profile_teaser, $member_id);

        if ($image_id > 0) {
            set_post_thumbnail($member_id, $image_id);
        }

        if ($spokespeople_term_id > 0) {
            wp_set_object_terms($member_id, [$spokespeople_term_id], 'team_member_category', true);
        }

        return (int) $member_id;
    }
}

if (! function_exists('matrix_seed_mq_url')) {
    function matrix_seed_mq_url(string $path): string
    {
        $path = trim($path, '/');
        $post_id = url_to_postid(home_url('/' . $path . '/'));

        if ($post_id > 0) {
            return (string) get_permalink($post_id);
        }

        $page_id = matrix_seed_resolve_page_id_by_path($path);

        if ($page_id > 0) {
            return (string) get_permalink($page_id);
        }

        return home_url('/' . $path . '/');
    }
}

$home = home_url('/');
$about_us_url = home_url('/about-us/');
$contact_url = home_url('/contact-us/');
$press_releases_url = matrix_seed_mq_url('press-releases');

$figma = [
    'hero' => 'https://www.figma.com/api/mcp/asset/2fff4dfd-919a-406f-a0ae-57d778912fca',
    'responsible_reporting' => 'https://www.figma.com/api/mcp/asset/5ea65f6b-6d46-43a8-8276-d48269f7abc4',
    'portrait_a' => 'https://www.figma.com/api/mcp/asset/3da52277-6e39-4a22-ae4c-f17b2a9f2ddd',
    'portrait_b' => 'https://www.figma.com/api/mcp/asset/1b550473-c5ff-4a11-971d-51001791ec7a',
    'portrait_c' => 'https://www.figma.com/api/mcp/asset/94543d1c-af38-4d9d-a413-406541044c0f',
];

$theme_fallback = 'assets/images/about-mental-health-grid/overview.png';

$hero_image_id = matrix_seed_resolve_image(
    $figma['hero'],
    'media-queries-hero-2780-3711',
    'Media Queries hero',
    $theme_fallback
);

$responsible_reporting_image_id = matrix_seed_resolve_image(
    $figma['responsible_reporting'],
    'media-queries-responsible-reporting-2780-3749',
    'Responsible Reporting',
    $theme_fallback
);

$portrait_ids = [
    matrix_seed_resolve_image($figma['portrait_a'], 'media-queries-portrait-a', 'Spokesperson portrait A'),
    matrix_seed_resolve_image($figma['portrait_b'], 'media-queries-portrait-b', 'Spokesperson portrait B'),
    matrix_seed_resolve_image($figma['portrait_c'], 'media-queries-portrait-c', 'Spokesperson portrait C'),
];

$spokespeople_term_id = matrix_seed_ensure_team_category('spokespeople', 'Spokespeople');

$spokespersons = [
    [
        'title' => 'John Creedon',
        'job_title' => 'Director of Nursing',
        'profile_teaser' => '<p>John Creedon is Director of Nursing at St Patrick\'s Mental Health Services and can speak on nursing, inpatient care and service delivery.</p>',
        'seed_key' => 'media-queries-spokesperson-john-creedon',
        'image_index' => 0,
    ],
    [
        'title' => 'Paul Gilligan',
        'job_title' => 'CEO',
        'profile_teaser' => '<p>Paul Gilligan is Chief Executive Officer of St Patrick\'s Mental Health Services and can speak on organisational leadership and mental health policy.</p>',
        'seed_key' => 'media-queries-spokesperson-paul-gilligan',
        'image_index' => 1,
    ],
    [
        'title' => 'Professor Paul Fearon',
        'job_title' => 'Medical Director',
        'profile_teaser' => '<p>Professor Paul Fearon is Medical Director at St Patrick\'s Mental Health Services and can speak on clinical matters and mental health treatment.</p>',
        'seed_key' => 'media-queries-spokesperson-paul-fearon',
        'image_index' => 2,
    ],
    [
        'title' => 'David O\'Brien',
        'job_title' => 'Head of Communications',
        'profile_teaser' => '<p>David O\'Brien leads the Communications Department and is the primary contact for media enquiries and interview requests.</p>',
        'seed_key' => 'media-queries-spokesperson-david-obrien',
        'image_index' => 1,
    ],
    [
        'title' => 'Sarah Nolan',
        'job_title' => 'Clinical Programme Lead',
        'profile_teaser' => '<p>Sarah Nolan is a Clinical Programme Lead and can speak on programmes, therapies and recovery-focused care in SPMHS.</p>',
        'seed_key' => 'media-queries-spokesperson-sarah-nolan',
        'image_index' => 0,
    ],
];

foreach ($spokespersons as $spokesperson) {
    $image_id = $portrait_ids[$spokesperson['image_index']] ?? 0;

    matrix_seed_ensure_spokesperson(
        $spokesperson['title'],
        $spokesperson['job_title'],
        $spokesperson['profile_teaser'],
        $image_id,
        $spokesperson['seed_key'],
        $spokespeople_term_id
    );
}

$placeholder_posts = get_posts([
    'post_type' => 'team_members',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => '_matrix_seed_key',
            'value' => 'media-queries-spokesperson-placeholder-',
            'compare' => 'LIKE',
        ],
    ],
]);

foreach ($placeholder_posts as $placeholder_post) {
    wp_delete_post((int) $placeholder_post->ID, true);
}

$generic_placeholders = get_posts([
    'post_type' => 'team_members',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'title' => 'Team member name',
]);

foreach ($generic_placeholders as $generic_placeholder) {
    if ($spokespeople_term_id > 0 && has_term($spokespeople_term_id, 'team_member_category', $generic_placeholder)) {
        wp_delete_post((int) $generic_placeholder->ID, true);
    }
}

$duplicate_seed_posts = get_posts([
    'post_type' => 'team_members',
    'post_status' => 'any',
    'posts_per_page' => -1,
    'meta_query' => [
        [
            'key' => '_matrix_seed_key',
            'value' => 'media-queries-spokesperson-',
            'compare' => 'LIKE',
        ],
    ],
]);

foreach ($duplicate_seed_posts as $duplicate_post) {
    if ($duplicate_post->post_title === 'Team member name') {
        continue;
    }

    $duplicate_id = (int) $duplicate_post->ID;
    $canonical_id = matrix_seed_find_team_member_by_title($duplicate_post->post_title);

    if ($canonical_id > 0 && $canonical_id !== $duplicate_id) {
        wp_delete_post($duplicate_id, true);
    }
}

$hero_intro = 'For media queries, interview requests or comment on mental health issues, please contact our Communications Department on <a href="tel:012493540">01 249 3540</a> or email <a href="mailto:communications@stpatricks.ie">communications@stpatricks.ie</a>. Our spokespeople are available to support accurate, informed reporting on mental health.';

$hero_content = sprintf(
    '<p>%s</p><p><a class="btn inline-flex min-h-[36px] items-center justify-center rounded-[6px] bg-[#024B79] px-3 py-2 text-[14px] font-medium leading-[24px] text-white no-underline" href="%s">Contact Us</a></p>',
    $hero_intro,
    esc_url($contact_url)
);

$intro_paragraph = '<p><strong>We encourage responsible reporting on mental health that respects the dignity of people experiencing mental health difficulties.</strong></p>';
$body_paragraph = '<p>When reporting on mental health, we ask journalists to use person-first language, avoid sensationalist or stigmatising terms, and include appropriate help-seeking information where relevant. We are committed to working with media professionals to promote accurate, compassionate coverage of mental health issues in Ireland.</p>'
    . '<p>You can find further guidance through organisations such as <a href="https://seechange.ie/" target="_blank" rel="noopener noreferrer">See Change</a> and the <a href="https://www.headstrong.ie/" target="_blank" rel="noopener noreferrer">Headstrong media guidelines</a>.</p>';

$press_releases_term = get_term_by('slug', 'press-releases', 'category');
$press_releases_term_id = $press_releases_term instanceof WP_Term ? (int) $press_releases_term->term_id : 0;

$rows = get_field('flexible_content_blocks', $post_id);

if (! is_array($rows) || $rows === []) {
    if (class_exists('WP_CLI')) {
        WP_CLI::error('Media Queries page has no flexible content blocks to update.');
    }

    exit(1);
}

foreach ($rows as &$row) {
    $layout = $row['acf_fc_layout'] ?? '';

    if ($layout === 'hero_with_breadcrumbs') {
        $row['content'] = $hero_content;
    }

    if ($layout === 'team_members') {
        $row['posts_per_page'] = 9;
    }

    if ($layout === 'latest_posts') {
        $row['heading'] = 'Recent Press Releases';

        if ($press_releases_term_id > 0) {
            $row['selected_categories'] = [$press_releases_term_id];
        }

        $row['header_button_link'] = [
            'title' => 'View all press releases',
            'url' => $press_releases_url,
            'target' => '',
        ];
        $row['empty_state_message'] = 'No press releases are available yet.';
    }

    if ($layout === 'content' && ($row['heading'] ?? '') === 'Responsible Reporting') {
        $row['intro_text'] = $intro_paragraph;
        $row['content'] = $body_paragraph;

        if ($responsible_reporting_image_id > 0) {
            $row['image'] = $responsible_reporting_image_id;
        }
    }
}
unset($row);

update_field('hero_content_blocks', [], $post_id);
update_field('flexible_content_blocks', $rows, $post_id);
update_post_meta($post_id, '_matrix_seed_key', 'about-us-media-queries-content');

$saved_rows = get_field('flexible_content_blocks', $post_id);
$saved_count = is_array($saved_rows) ? count($saved_rows) : 0;

$spokespeople_count = $spokespeople_term_id > 0
    ? (int) (new WP_Query([
        'post_type' => 'team_members',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'tax_query' => [
            [
                'taxonomy' => 'team_member_category',
                'field' => 'term_id',
                'terms' => [$spokespeople_term_id],
            ],
        ],
    ]))->found_posts
    : 0;

if (class_exists('WP_CLI')) {
    WP_CLI::success(sprintf(
        'Updated Media Queries page (%d) with %d flexi blocks and %d spokespeople.',
        $post_id,
        $saved_count,
        $spokespeople_count
    ));
    WP_CLI::log('Press releases archive: ' . $press_releases_url);
}
