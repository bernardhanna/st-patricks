<?php

add_action('init', function () {
    register_extended_taxonomy('programme_therapy_type', 'programmes_therapies', [
        'hierarchical' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'programme-therapy-type'],
    ], [
        'singular' => 'Programme or Therapy Type',
        'plural' => 'Programme or Therapy Types',
        'slug' => 'programme-therapy-type',
    ]);
});
