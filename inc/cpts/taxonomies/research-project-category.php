<?php

add_action('init', function () {
    register_extended_taxonomy('research_project_category', 'research_projects', [
        'hierarchical' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'research-project-category'],
    ], [
        'singular' => 'Research Project Category',
        'plural' => 'Research Project Categories',
        'slug' => 'research-project-category',
    ]);
});
