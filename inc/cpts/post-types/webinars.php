<?php

add_action('init', function () {
    register_extended_post_type('webinars', [
        'menu_icon' => 'dashicons-video-alt3',
        'supports' => ['title', 'editor', 'thumbnail'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'webinars'],
        'show_in_rest' => true,
    ], [
        'singular' => 'Webinar',
        'plural' => 'Webinars',
        'slug' => 'webinars',
    ]);
});
