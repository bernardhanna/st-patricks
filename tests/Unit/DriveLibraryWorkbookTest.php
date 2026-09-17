<?php

/**
 * @covers matrix_workbook_drive helpers used by the content workbook export
 */

require_once dirname(__DIR__, 2) . '/scripts/lib/drive-library-workbook.php';

test('drive page folder map includes extending services under present and future', function () {
    $map = matrix_workbook_drive_page_folder_map();

    expect($map)->toHaveKey('about-us/our-present-and-future/extending-and-enhancing-our-services')
        ->and($map['about-us/our-present-and-future/extending-and-enhancing-our-services'])
        ->toBe('02-Page-content/About Us/Extending our services');
});

test('content on drive options are Yes and No', function () {
    expect(matrix_workbook_content_on_drive_options())->toBe(['Yes', 'No']);
});

test('drive folder ids payload exposes root library url', function () {
    $ids = matrix_workbook_drive_folder_ids();

    expect($ids['root_url'])->toContain('19x_kP3NV29kzesNI81ob9XFB9dTUctRk')
        ->and($ids['folders'])->toBeArray();
});
