<?php

require_once dirname(__DIR__, 2) . '/inc/sentence-case-functions.php';
require_once dirname(__DIR__, 2) . '/inc/useful-links-functions.php';
require_once dirname(__DIR__, 2) . '/inc/link-functions.php';

beforeEach(function () {
    __wp_stub('home_url', fn ($path = '') => 'https://www.stpatricks.ie' . $path);
    __wp_stub('wp_parse_url', fn ($url) => parse_url($url));
    __wp_stub('untrailingslashit', fn ($value) => rtrim((string) $value, '/'));
});

test('matrix_format_sentence_case converts title case to sentence case', function () {
    expect(matrix_format_useful_link_title('About Us'))->toBe('About us')
        ->and(matrix_format_useful_link_title('What We Offer'))->toBe('What we offer')
        ->and(matrix_format_useful_link_title('Service User FAQs'))->toBe('Service user FAQs')
        ->and(matrix_format_useful_link_title('Staff Wellbeing'))->toBe('Staff wellbeing')
        ->and(matrix_format_useful_link_title('Useful Links'))->toBe('Useful links')
        ->and(matrix_format_useful_link_title('Day Programmes'))->toBe('Day programmes')
        ->and(matrix_format_useful_link_title('Make a Payment'))->toBe('Make a payment')
        ->and(matrix_format_useful_link_title('Directions and Parking'))->toBe('Directions and parking');
});

test('matrix_format_sentence_case preserves proper nouns and acronyms', function () {
    expect(matrix_format_useful_link_title('About St Patrick\'s'))->toBe('About St Patrick\'s')
        ->and(matrix_format_useful_link_title('Visit SPIRE'))->toBe('Visit SPIRE')
        ->and(matrix_format_useful_link_title('Outpatient Care - Dean Clinics'))->toBe('Outpatient care - Dean Clinics')
        ->and(matrix_format_useful_link_title('CBT and CFT programmes'))->toBe('CBT and CFT programmes')
        ->and(matrix_format_useful_link_title('Referrals from your GP'))->toBe('Referrals from your GP')
        ->and(matrix_format_useful_link_title('About Your Portal'))->toBe('About Your Portal')
        ->and(matrix_format_useful_link_title('Care at SPMHS'))->toBe('Care at SPMHS')
        ->and(matrix_format_useful_link_title('Dean Clinic Cork'))->toBe('Dean Clinic Cork')
        ->and(matrix_format_useful_link_title('St Patrick\'s University Hospital'))->toBe('St Patrick\'s University Hospital');
});

test('matrix_normalize_useful_links applies sentence case on output', function () {
    $items = matrix_normalize_useful_links([
        ['link' => ['title' => 'About Us', 'url' => '/about-us/', 'target' => '']],
        ['link' => ['title' => 'Visit SPIRE', 'url' => 'https://repository.stpatricks.ie/home', 'target' => '_blank']],
        ['link' => ['title' => 'Service User FAQs', 'url' => '/faqs/', 'target' => '']],
    ], '', 0);

    expect($items[0]['title'])->toBe('About us')
        ->and($items[1]['title'])->toBe('Visit SPIRE')
        ->and($items[2]['title'])->toBe('Service user FAQs');
});

test('matrix_format_useful_links_heading defaults and sentence-cases', function () {
    expect(matrix_format_useful_links_heading(''))->toBe('Useful links')
        ->and(matrix_format_useful_links_heading('Useful Links'))->toBe('Useful links')
        ->and(matrix_format_useful_links_heading('In this section'))->toBe('In this section');
});
