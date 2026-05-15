<?php

add_action('init', function () {
    register_extended_post_type('faqs', [
        'menu_icon' => 'dashicons-editor-help',
        'supports' => ['title', 'editor', 'page-attributes'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'faqs'],
        'show_in_rest' => true,
    ], [
        'singular' => 'FAQ',
        'plural' => 'FAQs',
        'slug' => 'faqs',
    ]);
});
