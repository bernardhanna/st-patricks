<?php

// Register FAQs custom post type
add_action('init', function() {
    register_extended_post_type('Brands', [
        'menu_icon' => 'dashicons-admin-users',
        'supports' => ['title', 'editor'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'our-brands'],
        'show_in_rest' => true, // Enable Gutenberg support
    ], [
        'singular' => 'Brand',
        'plural'   => 'Brands',
        'slug'     => 'our-brands',
    ]);
});
