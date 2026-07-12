<?php

if (! function_exists('matrix_get_newsletter_subtext_html')) {
    function matrix_get_newsletter_subtext_html(string $subtext): string
    {
        if (trim($subtext) === '') {
            return '';
        }

        $plain_text = trim((string) preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($subtext), ENT_QUOTES, 'UTF-8')));

        if (stripos($subtext, '<a') === false && strcasecmp($plain_text, 'For healthcare newsletter click here') === 0) {
            $subtext = sprintf(
                '<p>For healthcare newsletter <a href="%s">click here</a></p>',
                esc_url(home_url('/campaigns/subscribe-to-our-gp-enewsletter/'))
            );
        }

        return matrix_kses_rich_text($subtext);
    }
}
