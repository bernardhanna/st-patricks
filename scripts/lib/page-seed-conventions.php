<?php

/**
 * Shared conventions for page seed scripts.
 *
 * @see docs/superpowers/page-build-rules.md
 */

if (! function_exists('matrix_page_seed_heading')) {
    /**
     * Return a valid flexi heading tag for the given outline level (1–6).
     */
    function matrix_page_seed_heading(int $level): string
    {
        $level = max(1, min(6, $level));

        return 'h' . $level;
    }
}

if (! function_exists('matrix_page_seed_strip_padding')) {
    /**
     * Remove padding_settings from a flexi row so templates use defaults.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    function matrix_page_seed_strip_padding(array $row): array
    {
        unset($row['padding_settings']);

        return $row;
    }
}

if (! function_exists('matrix_page_seed_strip_padding_from_rows')) {
    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    function matrix_page_seed_strip_padding_from_rows(array $rows): array
    {
        return array_map('matrix_page_seed_strip_padding', $rows);
    }
}

if (! function_exists('matrix_seed_resolve_page_id_by_path')) {
    /**
     * Resolve a hierarchical page path to a page ID (never attachments).
     */
    function matrix_seed_resolve_page_id_by_path(string $path): int
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        if ($segments === []) {
            return 0;
        }

        $parent_id = 0;
        $page = null;

        foreach ($segments as $segment) {
            $matches = get_posts([
                'post_type' => 'page',
                'name' => $segment,
                'post_parent' => $parent_id,
                'posts_per_page' => 1,
                'post_status' => ['publish', 'draft', 'private', 'pending'],
                'suppress_filters' => false,
            ]);

            if ($matches === []) {
                return 0;
            }

            $page = $matches[0];
            $parent_id = (int) $page->ID;
        }

        return $page instanceof WP_Post ? (int) $page->ID : 0;
    }
}

if (! function_exists('matrix_seed_release_youth_advocacy_page_slug')) {
    /**
     * Free the youth-advocacy slug from a migrated attachment and ensure a real page exists.
     */
    function matrix_seed_release_youth_advocacy_page_slug(int $advocacy_services_id): int
    {
        if (! function_exists('matrix_migrate_attachment_id_for_source_path')) {
            return 0;
        }

        $attachment_id = (int) matrix_migrate_attachment_id_for_source_path('/media/3707/youth-advocacy.png');

        if ($attachment_id > 0) {
            $attachment = get_post($attachment_id);

            if ($attachment instanceof WP_Post
                && $attachment->post_type === 'attachment'
                && $attachment->post_name === 'youth-advocacy'
            ) {
                wp_update_post([
                    'ID' => $attachment_id,
                    'post_name' => 'youth-advocacy-feature-image',
                ]);

                if (function_exists('update_field')) {
                    update_field('flexible_content_blocks', [], $attachment_id);
                    update_field('hero_content_blocks', [], $attachment_id);
                }
            }
        }

        $page_id = matrix_seed_resolve_page_id_by_path('advocacy-services/youth-advocacy');

        if ($page_id > 0) {
            return $page_id;
        }

        $inserted_id = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_parent' => $advocacy_services_id,
            'post_name' => 'youth-advocacy',
            'post_title' => 'Youth Advocacy',
        ], true);

        if (is_wp_error($inserted_id)) {
            return 0;
        }

        return (int) $inserted_id;
    }
}
