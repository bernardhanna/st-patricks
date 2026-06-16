<?php

add_action('init', function () {
    register_extended_post_type('get_involved', [
        'menu_icon' => 'dashicons-groups',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'has_archive' => false,
        'rewrite' => ['slug' => 'get-involved'],
        'show_in_rest' => true,
    ], [
        'singular' => 'Get Involved',
        'plural' => 'Get Involved',
        'slug' => 'get-involved',
    ]);
});
