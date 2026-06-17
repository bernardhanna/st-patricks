<?php

$event_posts = get_posts([
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => 6,
    'category_name' => 'events',
    'orderby' => 'date',
    'order' => 'DESC',
]);

if ($event_posts === []) {
    WP_CLI::warning('No published posts found in the events category.');

    return;
}

$external_url = 'https://www.eventbrite.ie/';
$cta_summary = '<p>Join us for this upcoming event. Book your place through Eventbrite using the button below.</p>';

foreach ($event_posts as $index => $post) {
    if (! $post instanceof WP_Post) {
        continue;
    }

    update_field('event_external_url', $external_url, $post->ID);
    update_field('event_external_button_label', 'Link to an Eventbrite', $post->ID);
    update_field('event_cta_summary', $cta_summary, $post->ID);
    update_field('event_link_external_from_archive', $index === 0 ? 1 : 0, $post->ID);

    WP_CLI::log(sprintf(
        'Updated event fields on post %d (%s)%s',
        $post->ID,
        $post->post_name,
        $index === 0 ? ' [archive cards link externally]' : ''
    ));
}

WP_CLI::success('Seeded event post fields for ' . count($event_posts) . ' events posts.');
