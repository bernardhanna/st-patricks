<?php

add_action('init', function () {
    register_extended_post_type('care_treatment', [
        'menu_icon' => 'dashicons-heart',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'has_archive' => false,
        'rewrite' => ['slug' => 'care-treatment'],
        'show_in_rest' => true,
    ], [
        'singular' => 'Care & Treatment',
        'plural' => 'Care & Treatment',
        'slug' => 'care-treatment',
    ]);
});
