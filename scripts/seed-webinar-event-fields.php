<?php

/**
 * Seed Eventbrite CTA fields on all webinar posts.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/seed-webinar-event-fields.php
 */

$webinars = get_posts([
    'post_type' => 'webinars',
    'post_status' => 'publish',
    'numberposts' => -1,
    'orderby' => 'ID',
    'order' => 'ASC',
]);

if ($webinars === []) {
    if (class_exists('WP_CLI')) {
        WP_CLI::warning('No published webinar posts found.');
    }

    exit(0);
}

$external_url = 'https://www.eventbrite.ie/';
$button_label = 'Link to an Eventbrite';
$cta_summary = '<p>Join us for this upcoming webinar. Book your place through Eventbrite using the button below.</p>';

foreach ($webinars as $index => $post) {
    if (! $post instanceof WP_Post) {
        continue;
    }

    update_field('event_external_url', $external_url, $post->ID);
    update_field('event_external_button_label', $button_label, $post->ID);
    update_field('event_cta_summary', $cta_summary, $post->ID);
    update_field('event_link_external_from_archive', $index === 0 ? 1 : 0, $post->ID);

    if (class_exists('WP_CLI')) {
        WP_CLI::log(sprintf(
            'Updated webinar %d (%s)%s',
            $post->ID,
            $post->post_name,
            $index === 0 ? ' [archive cards link externally]' : ''
        ));
    }
}

if (class_exists('WP_CLI')) {
    WP_CLI::success('Seeded Eventbrite fields for ' . count($webinars) . ' webinar posts.');
}
