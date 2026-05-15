<?php

add_action('init', function () {
    register_extended_taxonomy('career_location', 'careers', [
        'hierarchical' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'career-location'],
    ], [
        'singular' => 'Location',
        'plural' => 'Locations',
        'slug' => 'career-location',
    ]);
});
