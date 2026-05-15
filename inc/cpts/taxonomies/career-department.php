<?php

add_action('init', function () {
    register_extended_taxonomy('career_department', 'careers', [
        'hierarchical' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'career-department'],
    ], [
        'singular' => 'Department',
        'plural' => 'Departments',
        'slug' => 'career-department',
    ]);
});
