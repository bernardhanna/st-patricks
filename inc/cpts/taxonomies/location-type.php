<?php

add_action('init', function () {
    register_extended_taxonomy('location_type', 'locations', [
        'meta_box' => 'simple',
        'show_in_rest' => true,
    ], [
        'singular' => 'Location Type',
        'plural' => 'Location Types',
        'slug' => 'location-type',
    ]);
});
