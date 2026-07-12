<?php

if (! function_exists('matrix_get_newsletter_subtext_class_names')) {
    function matrix_get_newsletter_subtext_class_names(): string
    {
        return 'w-full text-base font-medium leading-7 text-center text-white wp_editor [&_a]:text-[#7ED0E0] [&_a:hover]:underline';
    }
}

if (! function_exists('matrix_prepare_newsletter_subtext')) {
    function matrix_prepare_newsletter_subtext(string $html): string
    {
        if ($html === '' || stripos($html, '<a') !== false || stripos($html, 'click here') === false) {
            return $html;
        }

        $url = esc_url(home_url('/campaigns/subscribe-to-our-gp-enewsletter/'));
        $linked = preg_replace_callback(
            '/\bclick here\b/i',
            static function (array $matches) use ($url): string {
                return '<a href="' . $url . '">' . $matches[0] . '</a>';
            },
            $html,
            1
        );

        return is_string($linked) ? $linked : $html;
    }
}
