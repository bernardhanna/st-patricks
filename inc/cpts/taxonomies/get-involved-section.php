<?php

add_action('init', function () {
    register_extended_taxonomy('get_involved_section', 'get_involved', [
        'meta_box' => 'simple',
        'show_in_rest' => true,
    ], [
        'singular' => 'Get Involved Section',
        'plural' => 'Get Involved Sections',
        'slug' => 'get-involved-section',
    ]);
});
