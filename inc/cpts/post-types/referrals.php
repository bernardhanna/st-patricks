<?php

add_action('init', function () {
    register_extended_post_type('referrals', [
        'menu_icon' => 'dashicons-clipboard',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'has_archive' => false,
        'rewrite' => ['slug' => 'referrals', 'with_front' => false],
        'show_in_rest' => true,
    ], [
        'singular' => 'Referral',
        'plural' => 'Referrals',
        'slug' => 'referrals',
    ]);
});
