<?php

function matrix_normalize_inpatient_bed_vacancy_item($item, $defaults = [])
{
    $status_background_color = trim((string) ($item['status_background_color'] ?? ''));

    if ($status_background_color === '') {
        $status_background_color = (string) ($defaults['status_background_color'] ?? '#C3DBAE');
    }

    return [
        'bed_count' => max(0, (int) ($item['bed_count'] ?? 0)),
        'location_title' => trim((string) ($item['location_title'] ?? '')),
        'location_subtitle' => trim((string) ($item['location_subtitle'] ?? '')),
        'disclaimer' => trim((string) ($item['disclaimer'] ?? '')),
        'status_background_color' => $status_background_color,
    ];
}

function matrix_normalize_inpatient_bed_vacancy_items($rows, $defaults = [])
{
    if (! is_array($rows)) {
        return [];
    }

    $items = [];

    foreach ($rows as $row) {
        if (! is_array($row)) {
            continue;
        }

        $item = matrix_normalize_inpatient_bed_vacancy_item($row, $defaults);

        if ($item['location_title'] === '' && $item['disclaimer'] === '') {
            continue;
        }

        $items[] = $item;
    }

    return $items;
}

function matrix_format_inpatient_bed_vacancy_location_label($item)
{
    $title = trim((string) ($item['location_title'] ?? ''));
    $subtitle = trim((string) ($item['location_subtitle'] ?? ''));

    if ($title === '') {
        return '';
    }

    if ($subtitle === '') {
        return $title;
    }

    return sprintf('%s (%s)', $title, $subtitle);
}

function matrix_resolve_inpatient_bed_vacancies_updated_label($updated_text)
{
    return trim((string) $updated_text);
}

function matrix_get_inpatient_bed_vacancies_defaults()
{
    return [
        'heading' => 'Current Inpatient Bed Vacancies',
        'updated_text' => '',
        'section_background_color' => '#FBF8F3',
        'card_background_color' => '#FFFFFF',
        'heading_color' => '#1E244B',
        'updated_color' => '#5F6478',
        'location_color' => '#1E244B',
        'disclaimer_color' => '#5F6478',
        'count_color' => '#1E244B',
        'beds_label_color' => '#1E244B',
        'underline_color' => '#6FC9C0',
        'status_background_color' => '#C3DBAE',
    ];
}
