<?php

require_once dirname(__DIR__, 2) . '/inc/sitemap-functions.php';

if (! function_exists('get_permalink')) {
    function get_permalink($post = 0)
    {
        $id = is_object($post) ? (int) $post->ID : (int) $post;

        return 'http://localhost:10034/?p=' . $id;
    }
}

if (! function_exists('home_url')) {
    function home_url($path = '/')
    {
        return 'http://localhost:10034/' . ltrim((string) $path, '/');
    }
}

if (! function_exists('esc_attr')) {
    function esc_attr($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (! function_exists('esc_url')) {
    function esc_url($value)
    {
        return (string) $value;
    }
}

if (! function_exists('esc_html')) {
    function esc_html($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

test('sitemap page tree nests children from page objects', function () {
    $about = (object) ['ID' => 194, 'post_parent' => 0, 'post_name' => 'about-us', 'post_title' => 'About Us'];
    $overview = (object) ['ID' => 216, 'post_parent' => 194, 'post_name' => 'overview', 'post_title' => 'Overview'];

    $tree = matrix_build_sitemap_page_tree_from_pages([$about, $overview]);

    expect($tree)->toHaveCount(1);
    expect($tree[0]['title'])->toBe('About Us');
    expect($tree[0]['children'][0]['title'])->toBe('Overview');
});

test('sitemap list renderer outputs nested accessible links', function () {
    $html = matrix_render_sitemap_list([
        [
            'title' => 'About Us',
            'url' => 'http://localhost:10034/about-us/',
            'children' => [
                [
                    'title' => 'Overview',
                    'url' => 'http://localhost:10034/about-us/overview/',
                    'children' => [],
                ],
            ],
        ],
    ]);

    expect($html)->toContain('About Us');
    expect($html)->toContain('Overview');
    expect($html)->toContain('role="list"');
    expect($html)->toContain('border-l');
});

if (! function_exists('get_pages')) {
    function get_pages($args = [])
    {
        return [];
    }
}

if (! function_exists('get_page_by_path')) {
    function get_page_by_path($path)
    {
        return null;
    }
}

if (! function_exists('get_posts')) {
    function get_posts($args = [])
    {
        return [];
    }
}

if (! function_exists('get_post_type_object')) {
    function get_post_type_object($post_type)
    {
        return null;
    }
}

if (! function_exists('get_option')) {
    function get_option($option)
    {
        return '';
    }
}

if (! function_exists('apply_filters')) {
    function apply_filters($tag, $value)
    {
        return $value;
    }
}

if (! function_exists('get_the_title')) {
    function get_the_title($post = 0)
    {
        $post_id = is_object($post) ? (int) ($post->ID ?? 0) : (int) $post;

        return (string) ($GLOBALS['matrix_test_sitemap_post_fields'][$post_id]['post_title'] ?? 'Sitemap');
    }
}

if (! function_exists('get_post_field')) {
    function get_post_field($field, $post_id)
    {
        return $GLOBALS['matrix_test_sitemap_post_fields'][$post_id][$field] ?? '';
    }
}

test('sitemap page view model includes breadcrumbs and default intro', function () {
    $GLOBALS['matrix_test_sitemap_post_fields'][213] = [
        'post_title' => 'Sitemap',
        'post_excerpt' => 'Find every page quickly.',
    ];

    $view = matrix_prepare_sitemap_page(213);

    expect($view['heading'])->toBe('Sitemap');
    expect($view['intro'])->toBe('Find every page quickly.');
    expect($view['breadcrumbs'][0]['title'])->toBe('Home');
    expect($view['current_crumb_label'])->toBe('Sitemap');
});
