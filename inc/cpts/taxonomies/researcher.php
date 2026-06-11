<?php

add_action('init', function () {
    register_extended_taxonomy('researcher', 'research_projects', [
        'hierarchical' => false,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'researcher'],
    ], [
        'singular' => 'Researcher',
        'plural' => 'Researchers',
        'slug' => 'researcher',
    ]);
});
