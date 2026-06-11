<?php

require_once dirname(__DIR__, 2) . '/inc/research-project-archive-functions.php';
require_once dirname(__DIR__, 2) . '/inc/research-project-single-functions.php';

test('research project single defaults include archive back label', function () {
    $defaults = matrix_get_research_project_single_defaults();

    expect($defaults['back_label'])->toBe('Back to research projects')
        ->and($defaults['related_count'])->toBe(3);
});

test('research project archive url falls back to post type archive', function () {
    expect(function_exists('matrix_get_research_project_archive_url'))->toBeTrue()
        ->and(function_exists('matrix_get_research_project_share_links'))->toBeTrue();
});
