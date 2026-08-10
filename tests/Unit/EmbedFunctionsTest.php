<?php

require_once dirname(__DIR__, 2) . '/inc/link-functions.php';
require_once dirname(__DIR__, 2) . '/inc/migrate-functions.php';

test('matrix_migrate_html_chunk_has_content keeps iframe-only sections', function () {
    $iframe_only = '<p><iframe width="560" height="315" src="https://www.youtube.com/embed/8Od763ER_Qg" title="YouTube video player"></iframe></p>';

    expect(matrix_migrate_html_chunk_has_content($iframe_only))->toBeTrue()
        ->and(matrix_migrate_html_chunk_has_content('<p>   </p>'))->toBeFalse()
        ->and(matrix_migrate_html_chunk_has_content('<p>Hello</p>'))->toBeTrue();
});

test('matrix_normalize_absolute_embeds_in_html wraps issuu absolute iframes', function () {
    $html = '<p>Intro</p><p><iframe allowfullscreen="true" style="position: absolute; border: none; width: 100%; height: 100%; left: 0; right: 0; top: 0; bottom: 0;" src="https://e.issuu.com/embed.html?d=report&amp;u=stpatricksmentalhealthservices"></iframe></p>';

    $normalized = matrix_normalize_absolute_embeds_in_html($html);

    expect($normalized)->toContain('class="matrix-embed matrix-embed--issuu"')
        ->and($normalized)->toContain('matrix-embed__frame')
        ->and($normalized)->not->toMatch('/<p>\s*<iframe/i');
});

test('matrix_is_allowed_embed_iframe_src accepts trusted hosts only', function () {
    expect(matrix_is_allowed_embed_iframe_src('https://www.youtube.com/embed/abc'))->toBeTrue()
        ->and(matrix_is_allowed_embed_iframe_src('https://e.issuu.com/embed.html?d=x'))->toBeTrue()
        ->and(matrix_is_allowed_embed_iframe_src('https://evil.example/embed'))->toBeFalse()
        ->and(matrix_is_allowed_embed_iframe_src('javascript:alert(1)'))->toBeFalse();
});
