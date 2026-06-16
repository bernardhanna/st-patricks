<?php

if (! function_exists('matrix_get_mental_health_condition_paths')) {
    /**
     * Old-site paths for each mental health condition page.
     *
     * @return string[]
     */
    function matrix_get_mental_health_condition_paths(): array
    {
        return [
            'mental-health/addiction-dual-diagnosis',
            'mental-health/anxiety',
            'mental-health/bipolar-disorder',
            'mental-health/depression',
            'mental-health/eating-disorders',
            'mental-health/personality-disorders',
            'mental-health/schizophrenia-psychosis',
            'mental-health/young-adults',
            'mental-health/older-adults',
        ];
    }
}

if (! function_exists('matrix_mental_health_hub_url')) {
    function matrix_mental_health_hub_url(): string
    {
        $hub = get_page_by_path('mental-health');

        if ($hub instanceof WP_Post) {
            return (string) get_permalink($hub);
        }

        return (string) home_url('/mental-health/');
    }
}

if (! function_exists('matrix_mental_health_condition_url')) {
    function matrix_mental_health_condition_url(string $slug): string
    {
        $slug = sanitize_title($slug);
        $posts = get_posts([
            'post_type' => 'mental_health',
            'name' => $slug,
            'post_status' => 'publish',
            'posts_per_page' => 1,
        ]);

        if ($posts !== [] && $posts[0] instanceof WP_Post) {
            return (string) get_permalink($posts[0]);
        }

        return (string) home_url('/mental-health/' . $slug . '/');
    }
}

if (! function_exists('matrix_mental_health_slug_from_old_path')) {
    function matrix_mental_health_slug_from_old_path(string $old_path): string
    {
        $old_path = trim($old_path, '/');
        $parts = explode('/', $old_path);

        return sanitize_title((string) end($parts));
    }
}
