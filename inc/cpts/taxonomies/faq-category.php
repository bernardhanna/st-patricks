<?php

add_action('init', function () {
    register_extended_taxonomy('faq_category', 'faqs', [
        'hierarchical' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'faq-category'],
    ], [
        'singular' => 'FAQ Category',
        'plural' => 'FAQ Categories',
        'slug' => 'faq-category',
    ]);
});
