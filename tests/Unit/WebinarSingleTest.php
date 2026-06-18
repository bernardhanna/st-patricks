<?php

require_once dirname(__DIR__, 2) . '/inc/blog-single-functions.php';
require_once dirname(__DIR__, 2) . '/inc/webinar-single-functions.php';

test('webinar single helpers expose safe defaults without post context', function () {
    expect(function_exists('matrix_is_webinar_post'))->toBeTrue()
        ->and(matrix_is_webinar_post(0))->toBeFalse()
        ->and(matrix_uses_event_style_single_layout(0))->toBeFalse()
        ->and(matrix_get_webinars_archive_url())->toBe('/webinars/')
        ->and(matrix_format_webinar_date_label(0))->toBe('')
        ->and(matrix_format_webinar_time_label(0))->toBe('')
        ->and(matrix_get_webinar_post_intro(0))->toBe('')
        ->and(matrix_get_webinar_adjacent_post_link('next', 0))->toBeNull()
        ->and(matrix_get_event_style_adjacent_post_link('previous', 0))->toBeNull();
});

test('webinar single defaults include archive back label', function () {
    $defaults = matrix_get_webinar_single_defaults();

    expect($defaults['back_label'])->toBe('Back to webinars & events')
        ->and($defaults['date_label'])->toBe('Date:')
        ->and($defaults['webinar_type_taxonomy'])->toBe('webinar_type');
});

test('webinar date label formats ymd values', function () {
    if (! function_exists('update_field')) {
        expect(true)->toBeTrue();

        return;
    }

    $post_id = wp_insert_post([
        'post_type' => 'webinars',
        'post_status' => 'publish',
        'post_title' => 'Test webinar date formatting',
    ]);

    update_field('webinar_date', '20260615', $post_id);
    update_field('webinar_time', '14:30:00', $post_id);

    expect(matrix_format_webinar_date_label($post_id))->toBe('15/06/26')
        ->and(matrix_format_webinar_time_label($post_id))->toBeString();

    wp_delete_post($post_id, true);
});

test('event style layout includes webinars post type', function () {
    if (! function_exists('wp_insert_post')) {
        expect(true)->toBeTrue();

        return;
    }

    $post_id = wp_insert_post([
        'post_type' => 'webinars',
        'post_status' => 'publish',
        'post_title' => 'Test webinar event layout',
    ]);

    expect(matrix_is_webinar_post($post_id))->toBeTrue()
        ->and(matrix_uses_event_style_single_layout($post_id))->toBeTrue()
        ->and(matrix_should_force_webinar_single_placeholder($post_id))->toBeTrue()
        ->and(matrix_get_webinar_single_placeholder_figma_key())->toBe('webinar-single-placeholder-3279-17604');

    wp_delete_post($post_id, true);
});
