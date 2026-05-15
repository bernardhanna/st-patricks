<?php

add_action('init', function () {
    register_extended_taxonomy('delivery_format', 'programmes_therapies', [
        'hierarchical' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'delivery-format'],
    ], [
        'singular' => 'Delivery Format',
        'plural' => 'Delivery Formats',
        'slug' => 'delivery-format',
    ]);
});
