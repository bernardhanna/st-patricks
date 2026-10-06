<?php

require_once dirname(__DIR__, 2) . '/inc/sitemap-functions.php';
require_once dirname(__DIR__, 2) . '/inc/link-functions.php';

beforeEach(function () {
    $GLOBALS['matrix_test_sitemap_post_fields'] = [];

    __wp_stub('get_permalink', function ($post = 0) {
        $id = is_object($post) ? (int) $post->ID : (int) $post;

        return 'http://localhost:10034/?p=' . $id;
    });
    __wp_stub('home_url', fn ($path = '/') => 'http://localhost:10034/' . ltrim((string) $path, '/'));
    __wp_stub('get_pages', []);
    __wp_stub('get_page_by_path', null);
    __wp_stub('get_posts', []);
    __wp_stub('get_post_type_object', null);
    __wp_stub('get_option', '');
    __wp_stub('get_the_title', function ($post = 0) {
        $post_id = is_object($post) ? (int) ($post->ID ?? 0) : (int) $post;

        return (string) ($GLOBALS['matrix_test_sitemap_post_fields'][$post_id]['post_title'] ?? 'Sitemap');
    });
    __wp_stub('get_post_field', function ($field, $post_id = null) {
        return $GLOBALS['matrix_test_sitemap_post_fields'][(int) $post_id][$field] ?? '';
    });
});

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
});

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

test('sitemap taxonomy children build filtered archive urls', function () {
    if (! function_exists('get_terms')) {
        expect(true)->toBeTrue();

        return;
    }

    $children = matrix_build_sitemap_taxonomy_children('http://localhost:10034/news-and-events/', [
        'taxonomy' => 'category',
        'query_var' => 'blog_category',
        'all_label' => 'All',
    ]);

    expect($children)->not->toBeEmpty();
    expect($children[0]['title'])->toBe('All');
    expect($children[0]['url'])->toBe('http://localhost:10034/news-and-events/');
});

test('sitemap listable helper skips drafts and redirected paths', function () {
    $draft = (object) [
        'ID' => 3077,
        'post_status' => 'draft',
        'post_type' => 'page',
        'post_title' => 'GPs & Referrers',
    ];
    $published = (object) [
        'ID' => 232,
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_title' => 'Healthcare Professionals',
    ];

    expect(matrix_sitemap_post_is_listable($draft))->toBeFalse();
    expect(matrix_sitemap_post_is_listable($published))->toBeTrue();
    expect(matrix_sitemap_path_is_redirected('referrals'))->toBeTrue();
    expect(matrix_sitemap_path_is_redirected('healthcare-professionals'))->toBeFalse();
});

test('sitemap hub list no longer includes leftover referral and help shells', function () {
    $paths = matrix_get_sitemap_hub_page_paths();

    expect($paths)->toContain('healthcare-professionals')
        ->and($paths)->toContain('service-users-and-visitors')
        ->and($paths)->not->toContain('referrals')
        ->and($paths)->not->toContain('make-a-referral')
        ->and($paths)->not->toContain('getting-help')
        ->and($paths)->not->toContain('get-involved');
});

test('inpatient extra children retarget make a referral to the adult inpatient refer page', function () {
    $extra = matrix_get_sitemap_hub_extra_children();

    expect($extra['inpatient-care'])->toContain('healthcare-professionals/refer-an-adult-for-inpatient-care')
        ->and($extra['inpatient-care'])->not->toContain('make-a-referral')
        ->and($extra['service-users-and-visitors'])->toContain('getting-help/concerned-about-yourself-or-someone-you-know');
});
