<?php

add_action('init', function () {
    register_extended_taxonomy('webinar_type', 'webinars', [
        'hierarchical' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'webinar-type'],
    ], [
        'singular' => 'Webinar Type',
        'plural' => 'Webinar Types',
        'slug' => 'webinar-type',
    ]);
});
