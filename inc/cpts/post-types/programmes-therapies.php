<?php

add_action('init', function () {
    register_extended_post_type('programmes_therapies', [
        'menu_icon' => 'dashicons-heart',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'has_archive' => false,
        'rewrite' => ['slug' => 'programmes-therapies'],
        'show_in_rest' => true,
    ], [
        'singular' => 'Programme or Therapy',
        'plural' => 'Programmes and Therapies',
        'slug' => 'programmes-therapies',
    ]);
});
