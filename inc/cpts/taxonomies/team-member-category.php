<?php

add_action('init', function () {
    register_extended_taxonomy('team_member_category', 'team_members', [
        'hierarchical' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'team-member-category'],
    ], [
        'singular' => 'Team Member Category',
        'plural' => 'Team Member Categories',
        'slug' => 'team-member-category',
    ]);
});
