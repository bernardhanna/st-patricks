<?php

/**
 * Import research projects from stpatricks.ie into the research_projects CPT.
 *
 * Run: wp eval-file wp-content/themes/matrix-starter/scripts/migrate-import-research-projects.php
 */

require_once get_template_directory() . '/inc/migrate-functions.php';

if (! class_exists('WP_CLI')) {
    exit(1);
}

if (! function_exists('matrix_migrate_research_project_catalog')) {
    /**
     * @return array<int, array<string, string>>
     */
    function matrix_migrate_research_project_catalog(): array
    {
        return [
            [
                'slug' => 'the-keep-well-study',
                'title' => 'The KEEP-WELL Study',
                'excerpt' => 'The KEEP-WELL Trial aimed to assess the use of ketamine in preventing relapse following electroconvulsive therapy (ECT).',
                'image' => '/media/1541/st-patricks-mental-health-services-research-department.jpg',
                'category' => 'past',
            ],
            [
                'slug' => 'the-kindred-trial',
                'title' => 'The KINDRED Trial',
                'excerpt' => 'The KINDRED Trial aimed to compare the use of ketamine and midazolam in preventing relapse of depression.',
                'image' => '/media/1541/st-patricks-mental-health-services-research-department.jpg',
                'category' => 'past',
            ],
            [
                'slug' => 'the-effect-dep-trial',
                'title' => 'The EFFECT-DEP Trial',
                'excerpt' => 'The EFFECT-Dep Trial aimed to enhance the effectiveness of electroconvulsive therapy (ECT) in severe depression.',
                'image' => '/media/1541/st-patricks-mental-health-services-research-department.jpg',
                'category' => 'past',
            ],
            [
                'slug' => 'amber-dep-autobiographical-memory-and-depression',
                'title' => 'AMBER-Dep: Autobiographical Memory and Depression',
                'excerpt' => 'The AMBER-Dep: Autobiographical Memory and Depression study aimed to investigate the effects of depression and its treatment on memory function.',
                'image' => '/media/1738/amber-dep-trial.jpg',
                'category' => 'past',
            ],
            [
                'slug' => 'the-karma-dep-trial',
                'title' => 'The KARMA-Dep Trial',
                'excerpt' => 'The KARMA-Dep Trial investigated whether patients recover more quickly from depression with ketamine rather than with a placebo.',
                'image' => '/media/1739/karma-dep.png',
                'category' => 'past',
            ],
            [
                'slug' => 'impact-of-eating-disorders-on-biological-ageing',
                'title' => 'Impact of Eating Disorders on Biological Ageing',
                'excerpt' => 'The research department of St Patrick’s Mental Health Services are currently investigating the effect of eating disorders on the ageing process.',
                'image' => '/media/2392/impact-eating-disorders-biological-ageing.jpg',
                'category' => 'current',
            ],
            [
                'slug' => 'karma-dep-2-trial',
                'title' => 'KARMA-DEP (2) Trial',
                'excerpt' => 'The KARMA-DEP (2) Trial explores the use of ketamine as an adjunctive therapy for people with severe depression.',
                'image' => '/media/1739/karma-dep.png',
                'category' => 'current',
            ],
            [
                'slug' => 'care-dep-study',
                'title' => 'The CARE-Dep Study',
                'excerpt' => 'The CARE-Dep Study aims to identify clinical characteristics that may predict a good therapeutic response to electroconvulsive therapy (ECT).',
                'image' => '/media/3574/care-dep-logo.png',
                'category' => 'current',
            ],
            [
                'slug' => 'secondary-analyses-electroconvulsive-therapy-studies',
                'title' => 'Secondary Analyses of Anonymised Data from Three Historical Studies of Electroconvulsive Therapy',
                'excerpt' => 'Secondary analyses of anonymised data collected during past studies on electroconvulsive therapy (ECT) is underway.',
                'image' => '/media/3575/secondary-analysis-of-research.png',
                'category' => 'current',
            ],
            [
                'slug' => 'profile-study',
                'title' => 'The PROFILE Study',
                'excerpt' => 'The research team at St Patrick’s Mental Health Services (SPMHS) is conducting a retrospective chart review about electroconvulsive therapy (ECT).',
                'image' => '/media/3647/profile-study-featured-image.png',
                'category' => 'current',
            ],
        ];
    }
}

if (! function_exists('matrix_migrate_research_project_html_file')) {
    function matrix_migrate_research_project_html_file(string $old_path): ?string
    {
        $safe = str_replace(['/', '.'], '_', trim($old_path, '/'));
        $file = matrix_migrate_html_dir() . '/original_https_www.stpatricks.ie_' . $safe . '.html';

        return is_readable($file) ? $file : null;
    }
}

if (! function_exists('matrix_migrate_research_project_fetch_html')) {
    function matrix_migrate_research_project_fetch_html(string $old_path): string
    {
        $file = matrix_migrate_research_project_html_file($old_path);

        if ($file !== null) {
            return (string) file_get_contents($file);
        }

        $response = wp_remote_get(
            matrix_migrate_live_url('/' . trim($old_path, '/') . '/'),
            ['timeout' => 30, 'redirection' => 5]
        );

        if (is_wp_error($response)) {
            return '';
        }

        $code = (int) wp_remote_retrieve_response_code($response);

        if ($code < 200 || $code >= 300) {
            return '';
        }

        return (string) wp_remote_retrieve_body($response);
    }
}

if (! function_exists('matrix_migrate_research_project_ensure_term')) {
    function matrix_migrate_research_project_ensure_term(string $taxonomy, string $name, string $slug): int
    {
        $existing = get_term_by('slug', $slug, $taxonomy);

        if ($existing instanceof WP_Term) {
            return (int) $existing->term_id;
        }

        $result = wp_insert_term($name, $taxonomy, ['slug' => $slug]);

        return is_wp_error($result) ? 0 : (int) ($result['term_id'] ?? 0);
    }
}

if (! function_exists('matrix_migrate_research_project_featured_image_id')) {
    function matrix_migrate_research_project_featured_image_id(array $parsed, string $fallback_image_path): int
    {
        $candidates = array_values(array_filter([
            (string) ($parsed['og_image'] ?? ''),
            $fallback_image_path,
        ]));

        foreach ($candidates as $candidate) {
            $attachment_id = matrix_migrate_attachment_id_for_source_path($candidate);

            if ($attachment_id > 0) {
                return $attachment_id;
            }

            $attachment_id = matrix_migrate_import_attachment($candidate, basename($candidate));

            if ($attachment_id > 0) {
                return $attachment_id;
            }
        }

        return 0;
    }
}

if (! function_exists('matrix_migrate_trash_sample_research_projects')) {
    function matrix_migrate_trash_sample_research_projects(): int
    {
        $trashed = 0;
        $samples = get_posts([
            'post_type' => 'research_projects',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_matrix_migrate_old_path',
                    'compare' => 'NOT EXISTS',
                ],
            ],
        ]);

        foreach ($samples as $post) {
            if (! $post instanceof WP_Post) {
                continue;
            }

            if (wp_trash_post($post->ID)) {
                $trashed++;
            }
        }

        return $trashed;
    }
}

$dry_run = matrix_migrate_is_dry_run();
$stats = ['seen' => 0, 'created' => 0, 'updated' => 0, 'failed' => 0, 'trashed' => 0];
$catalog = matrix_migrate_research_project_catalog();
$stats['seen'] = count($catalog);

$current_term_id = matrix_migrate_research_project_ensure_term('research_project_category', 'Current', 'current');
$past_term_id = matrix_migrate_research_project_ensure_term('research_project_category', 'Past', 'past');
$default_researcher_id = matrix_migrate_research_project_ensure_term('researcher', 'Prof Declan McLoughlin', 'prof-declan-mcloughlin');

WP_CLI::log(sprintf('Importing %d research projects (%s).', count($catalog), $dry_run ? 'dry-run' : 'live'));

$progress = \WP_CLI\Utils\make_progress_bar('Research projects', count($catalog));

foreach ($catalog as $project) {
    $old_path = 'research/research-projects/' . $project['slug'];
    $html = matrix_migrate_research_project_fetch_html($old_path);
    $parsed = $html !== '' ? matrix_migrate_extract_parsed_page($html, $old_path) : null;

    if ($parsed === null) {
        $stats['failed']++;
        WP_CLI::warning('Could not parse: ' . $old_path);
        $progress->tick();
        continue;
    }

    $existing_id = matrix_migrate_find_by_old_path($old_path, 'research_projects');

    if ($existing_id === 0) {
        $existing = get_page_by_path($project['slug'], OBJECT, 'research_projects');
        $existing_id = $existing instanceof WP_Post ? (int) $existing->ID : 0;
    }

    if ($dry_run) {
        $progress->tick();
        continue;
    }

    $content = matrix_migrate_rewrite_html_urls((string) $parsed['body_html']);

    if (function_exists('matrix_process_external_links_in_html')) {
        $content = matrix_process_external_links_in_html($content);
    }

    $excerpt = trim((string) ($parsed['intro'] ?? ''));

    if ($excerpt === '') {
        $excerpt = (string) $project['excerpt'];
    }

    $postarr = [
        'post_type' => 'research_projects',
        'post_status' => 'publish',
        'post_title' => (string) ($parsed['title'] !== '' ? $parsed['title'] : $project['title']),
        'post_name' => $project['slug'],
        'post_excerpt' => $excerpt,
        'post_content' => $content,
    ];

    if ($existing_id > 0) {
        $postarr['ID'] = $existing_id;
        $post_id = wp_update_post($postarr, true);
        $stats['updated']++;
    } else {
        $post_id = wp_insert_post($postarr, true);
        $stats['created']++;
    }

    if (is_wp_error($post_id) || ! $post_id) {
        $stats['failed']++;
        WP_CLI::warning('Failed: ' . $old_path);
        $progress->tick();
        continue;
    }

    update_post_meta((int) $post_id, '_matrix_migrate_old_path', $old_path);

    $category_term_id = ($project['category'] ?? 'current') === 'past' ? $past_term_id : $current_term_id;

    if ($category_term_id > 0) {
        wp_set_object_terms((int) $post_id, [$category_term_id], 'research_project_category', false);
    }

    if ($default_researcher_id > 0) {
        wp_set_object_terms((int) $post_id, [$default_researcher_id], 'researcher', false);
    }

    $featured_image_id = matrix_migrate_research_project_featured_image_id($parsed, (string) $project['image']);

    if ($featured_image_id > 0) {
        set_post_thumbnail((int) $post_id, $featured_image_id);

        if (function_exists('matrix_remove_leading_duplicate_featured_image_from_content')) {
            $attachment_url = (string) wp_get_attachment_url($featured_image_id);
            $content = matrix_remove_leading_duplicate_featured_image_from_content($content, $featured_image_id, $attachment_url);

            if ($content !== (string) $postarr['post_content']) {
                wp_update_post([
                    'ID' => (int) $post_id,
                    'post_content' => $content,
                ]);
            }
        }
    }

    $progress->tick();
}

$progress->finish();

if (! $dry_run) {
    $stats['trashed'] = matrix_migrate_trash_sample_research_projects();
    flush_rewrite_rules(false);
}

WP_CLI::success(sprintf(
    'Research projects: seen=%d created=%d updated=%d failed=%d trashed_samples=%d',
    $stats['seen'],
    $stats['created'],
    $stats['updated'],
    $stats['failed'],
    $stats['trashed']
));
