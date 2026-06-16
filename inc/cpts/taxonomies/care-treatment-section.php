<?php

add_action('init', function () {
    register_extended_taxonomy('care_treatment_section', 'care_treatment', [
        'meta_box' => 'simple',
        'show_in_rest' => true,
    ], [
        'singular' => 'Care & Treatment Section',
        'plural' => 'Care & Treatment Sections',
        'slug' => 'care-treatment-section',
    ]);
});
