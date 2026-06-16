<?php

add_action('init', function () {
    register_extended_post_type('mental_health', [
        'menu_icon' => 'dashicons-heart',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'has_archive' => false,
        'rewrite' => ['slug' => 'mental-health', 'with_front' => false],
        'show_in_rest' => true,
    ], [
        'singular' => 'Mental Health Condition',
        'plural' => 'Mental Health Conditions',
        'slug' => 'mental-health',
    ]);
});
