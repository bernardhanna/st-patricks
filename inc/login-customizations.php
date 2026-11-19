<?php
add_filter('login_color_palette', function () {
    return [
        'brand'    => '#0073aa',
        'trim'     => '#181818',
        'trim-alt' => '#282828',
    ];
});

add_filter('login_headertext', function () {
    return get_bloginfo('name');
});

/**
 * The password login heading is the site name, but core login CSS
 * shifts that text off screen and shows only the logo. Put the name
 * back under the logo so the heading has readable text.
 */
add_action('password_protected_login_head', function () {
    echo '<style>
        body.login-password-protected #password-protected-logo a {
            text-indent: 0;
            overflow: visible;
            height: auto;
            width: auto;
            max-width: 20rem;
            background-repeat: no-repeat;
            background-position: center top;
            background-size: 84px 84px;
            padding-top: 96px;
            font-size: 20px;
            font-weight: 600;
            line-height: 1.3;
            text-align: center;
            color: #08284B;
        }
    </style>';
});
