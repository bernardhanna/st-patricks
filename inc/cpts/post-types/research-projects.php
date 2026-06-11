<?php

add_action('init', function () {
    register_extended_post_type('research_projects', [
        'menu_icon' => 'dashicons-search',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'research-projects'],
        'show_in_rest' => true,
    ], [
        'singular' => 'Research Project',
        'plural' => 'Research Projects',
        'slug' => 'research-projects',
    ]);
});
