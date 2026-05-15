<?php

add_action('init', function () {
    register_extended_post_type('careers', [
        'menu_icon' => 'dashicons-id-alt',
        'supports' => ['title', 'editor', 'excerpt'],
        'has_archive' => false,
        'rewrite' => ['slug' => 'careers'],
        'show_in_rest' => true,
    ], [
        'singular' => 'Career',
        'plural' => 'Careers',
        'slug' => 'careers',
    ]);
});
