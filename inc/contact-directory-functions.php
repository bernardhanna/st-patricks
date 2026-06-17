<?php

require_once __DIR__ . '/key-contact-info-functions.php';
require_once __DIR__ . '/locations-map-functions.php';

function matrix_contact_directory_item_has_panel_content(array $item): bool
{
    return ($item['bullet_items'] ?? []) !== []
        || ($item['phone'] ?? '') !== ''
        || ($item['email'] ?? '') !== ''
        || ($item['opening_hours'] ?? []) !== [];
}

/**
 * @param mixed $location_post
 */
function matrix_contact_directory_resolve_location_post_id($location_post): int
{
    if ($location_post instanceof WP_Post) {
        return (int) $location_post->ID;
    }

    if (is_numeric($location_post)) {
        return (int) $location_post;
    }

    if (is_array($location_post) && isset($location_post['ID'])) {
        return (int) $location_post['ID'];
    }

    return 0;
}

/**
 * @param array<string, mixed> $item
 * @return array<string, mixed>
 */
function matrix_contact_directory_merge_location_fields(array $item, int $location_id): array
{
    if ($location_id <= 0 || get_post_type($location_id) !== 'locations') {
        return $item;
    }

    $title_override = trim((string) ($item['title'] ?? ''));
    $phone_override = trim((string) ($item['phone'] ?? ''));
    $email_override = trim((string) ($item['email'] ?? ''));
    $opening_hours_override = matrix_normalize_location_opening_hours($item['opening_hours'] ?? []);

    $item['title'] = $title_override !== '' ? $title_override : trim(get_the_title($location_id));
    $item['phone'] = $phone_override !== '' ? $phone_override : trim((string) get_field('phone', $location_id));
    $item['email'] = $email_override !== '' ? $email_override : trim((string) get_field('email', $location_id));

    if ($opening_hours_override === []) {
        $item['opening_hours'] = matrix_normalize_location_opening_hours(get_field('opening_hours', $location_id));
    } else {
        $item['opening_hours'] = $opening_hours_override;
    }

    $item['location_id'] = $location_id;
    $item['location_url'] = (string) get_permalink($location_id);

    return $item;
}

/**
 * @param array<string, mixed> $item
 * @return array<string, mixed>|null
 */
function matrix_normalize_contact_directory_item(array $item, int $flat_index): ?array
{
    $item_source = (string) ($item['item_source'] ?? 'manual');

    if ($item_source === 'location') {
        $location_id = matrix_contact_directory_resolve_location_post_id($item['location'] ?? 0);
        $item = matrix_contact_directory_merge_location_fields($item, $location_id);

        if ($location_id <= 0) {
            return null;
        }
    } else {
        $item['title'] = trim(strip_tags((string) ($item['title'] ?? '')));
        $item['phone'] = trim(strip_tags((string) ($item['phone'] ?? '')));
        $item['email'] = trim(strip_tags((string) ($item['email'] ?? '')));
        $item['opening_hours'] = matrix_normalize_location_opening_hours($item['opening_hours'] ?? []);
    }

    $title = trim((string) ($item['title'] ?? ''));

    if ($title === '') {
        return null;
    }

    $bullet_items = [];

    foreach ((array) ($item['bullet_items'] ?? []) as $bullet_item) {
        if (! is_array($bullet_item)) {
            continue;
        }

        $label = trim(strip_tags((string) ($bullet_item['label'] ?? '')));

        if ($label !== '') {
            $bullet_items[] = $label;
        }
    }

    return [
        'title' => $title,
        'starts_open' => ! empty($item['starts_open']),
        'bullet_items' => $bullet_items,
        'phone' => trim((string) ($item['phone'] ?? '')),
        'email' => trim((string) ($item['email'] ?? '')),
        'opening_hours' => matrix_normalize_location_opening_hours($item['opening_hours'] ?? []),
        'location_id' => (int) ($item['location_id'] ?? 0),
        'location_url' => trim((string) ($item['location_url'] ?? '')),
        'flat_index' => $flat_index,
        'item_source' => $item_source,
    ];
}

/**
 * @param mixed $location_posts
 * @return array<int, array<string, mixed>>
 */
function matrix_contact_directory_items_from_locations($location_posts, int &$flat_index): array
{
    if (! is_array($location_posts)) {
        return [];
    }

    $items = [];

    foreach ($location_posts as $location_post) {
        $location_id = matrix_contact_directory_resolve_location_post_id($location_post);

        if ($location_id <= 0) {
            continue;
        }

        $normalized = matrix_normalize_contact_directory_item([
            'item_source' => 'location',
            'location' => $location_id,
            'starts_open' => 0,
            'bullet_items' => [],
        ], $flat_index);

        if ($normalized === null) {
            continue;
        }

        $items[] = $normalized;
        $flat_index++;
    }

    return $items;
}

/**
 * @return array{columns: array<int, array<string, mixed>>, initial_open_index: int}
 */
function matrix_normalize_contact_directory_columns($columns, $auto_locations = null, string $auto_location_mode = 'none'): array
{
    $normalized_columns = [];
    $initial_open_index = -1;
    $flat_index = 0;
    $auto_items = [];

    if ($auto_location_mode === 'all') {
        $location_ids = get_posts([
            'post_type' => 'locations',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
            'fields' => 'ids',
        ]);
        $auto_items = matrix_contact_directory_items_from_locations($location_ids, $flat_index);
    } elseif ($auto_location_mode === 'selected') {
        $auto_items = matrix_contact_directory_items_from_locations($auto_locations, $flat_index);
    }

    $column_index = 0;

    foreach ((array) $columns as $column) {
        if (! is_array($column)) {
            continue;
        }

        $items = [];

        if ($column_index === 0 && $auto_items !== []) {
            foreach ($auto_items as $auto_item) {
                if (! empty($auto_item['starts_open']) && $initial_open_index < 0) {
                    $initial_open_index = (int) ($auto_item['flat_index'] ?? -1);
                }
            }

            $items = array_merge($items, $auto_items);
        }

        foreach ((array) ($column['items'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $normalized = matrix_normalize_contact_directory_item($item, $flat_index);

            if ($normalized === null) {
                continue;
            }

            if ($normalized['starts_open'] && $initial_open_index < 0) {
                $initial_open_index = $flat_index;
            }

            $items[] = $normalized;
            $flat_index++;
        }

        if ($items === []) {
            $column_index++;
            continue;
        }

        $normalized_columns[] = [
            'items' => $items,
        ];

        $column_index++;
    }

    if ($normalized_columns === [] && $auto_items !== []) {
        $normalized_columns[] = [
            'items' => $auto_items,
        ];
    }

    return [
        'columns' => $normalized_columns,
        'initial_open_index' => $initial_open_index,
    ];
}

function matrix_get_contact_directory_opening_hours_heading_class_names(): string
{
    return 'pt-2 font-primary text-[16px] font-semibold leading-[28px] text-[#08284B]';
}

function matrix_get_contact_directory_opening_hours_grid_class_names(): string
{
    return 'mt-2 grid grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-x-6 gap-y-1';
}

function matrix_get_contact_directory_opening_hours_label_class_names(): string
{
    return 'font-primary text-[16px] font-medium leading-[28px] text-[#08284B]';
}

function matrix_contact_directory_has_visible_intro($intro_text): bool
{
    if (! is_string($intro_text)) {
        return false;
    }

    $intro_text = trim(function_exists('wp_strip_all_tags') ? wp_strip_all_tags($intro_text) : strip_tags($intro_text));

    return $intro_text !== '';
}

function matrix_get_contact_directory_section_wrapper_class_names(): string
{
    return 'contact-directory flex overflow-hidden relative';
}

function matrix_get_contact_directory_wrapper_class_names(): string
{
    return implode(' ', [
        'mx-auto',
        'flex',
        'w-full',
        'max-w-[1018px]',
        'flex-col',
        'px-5',
        'pt-12',
        'lg:px-0',
        'lg:pt-16',
        'lg:pb-[100px]',
    ]);
}

function matrix_get_contact_directory_grid_class_names(): string
{
    return 'grid w-full grid-cols-1 gap-8 lg:grid-cols-3 lg:gap-x-8 lg:items-start';
}

function matrix_get_contact_directory_intro_column_class_names(): string
{
    return 'flex w-full flex-col gap-6 lg:gap-8';
}

function matrix_get_contact_directory_heading_class_names(): string
{
    return 'font-primary text-[24px] font-semibold leading-[28px] tracking-[-0.18px] text-[#1E244B] lg:text-[30px] lg:leading-[36px] lg:tracking-[-0.225px]';
}

function matrix_get_contact_directory_intro_body_class_names(): string
{
    if (! function_exists('matrix_get_content_rich_text_wrapper_class_names')) {
        return 'wp_editor w-full font-primary text-[16px] font-medium leading-[28px] text-[#08284B]';
    }

    return matrix_get_content_rich_text_wrapper_class_names('medium', 'w-full', 'default');
}

function matrix_get_contact_directory_column_class_names(): string
{
    return 'flex w-full flex-col gap-4';
}

/** @deprecated Use matrix_get_contact_directory_grid_class_names(). */
function matrix_get_contact_directory_split_grid_class_names(): string
{
    return matrix_get_contact_directory_grid_class_names();
}

/** @deprecated Use matrix_get_contact_directory_wrapper_class_names(). */
function matrix_get_contact_directory_layout_class_names(): string
{
    return matrix_get_contact_directory_wrapper_class_names();
}
