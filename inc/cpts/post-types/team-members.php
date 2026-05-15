<?php

add_action('init', function () {
    register_extended_post_type('team_members', [
        'menu_icon' => 'dashicons-groups',
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'team-members'],
        'show_in_rest' => true,
    ], [
        'singular' => 'Team Member',
        'plural' => 'Team Members',
        'slug' => 'team-members',
    ]);
});
