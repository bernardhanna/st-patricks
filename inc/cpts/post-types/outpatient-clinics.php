<?php

add_action('init', function () {
    register_extended_post_type('outpatient_clinics', [
        'menu_icon' => 'dashicons-building',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'has_archive' => false,
        'rewrite' => ['slug' => 'outpatient-clinics'],
        'show_in_rest' => true,
    ], [
        'singular' => 'Outpatient Clinic',
        'plural' => 'Outpatient Clinics',
        'slug' => 'outpatient-clinics',
    ]);
});
