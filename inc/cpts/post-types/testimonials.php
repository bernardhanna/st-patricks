<?php

// Register FAQs custom post type
add_action('init', function() {
    register_extended_post_type('testimonials', [
        'menu_icon' => 'dashicons-format-quote',
        'supports' => ['title', 'editor'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'kudos'],
        'show_in_rest' => true, // Enable Gutenberg support
    ], [
        'singular' => 'Testimonial',
        'plural'   => 'Testimonials',
        'slug'     => 'kudos',
    ]);
});
