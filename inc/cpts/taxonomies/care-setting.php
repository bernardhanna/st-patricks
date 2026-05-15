<?php

add_action('init', function () {
    register_extended_taxonomy('care_setting', 'programmes_therapies', [
        'hierarchical' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'care-setting'],
    ], [
        'singular' => 'Care Setting',
        'plural' => 'Care Settings',
        'slug' => 'care-setting',
    ]);
});
