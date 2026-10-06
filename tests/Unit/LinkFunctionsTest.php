<?php

require_once dirname(__DIR__, 2) . '/inc/link-functions.php';

beforeEach(function () {
    __wp_stub('home_url', fn ($path = '') => 'https://www.stpatricks.ie' . $path);
});

test('matrix_is_external_url detects off-site http links', function () {
    expect(matrix_is_external_url('https://www.walkinmyshoes.ie/'))->toBeTrue()
        ->and(matrix_is_external_url('https://soundcloud.com/example'))->toBeTrue()
        ->and(matrix_is_external_url('/about-us/'))->toBeFalse()
        ->and(matrix_is_external_url('mailto:hello@example.com'))->toBeFalse();
});

test('matrix_normalize_link_target opens external urls in a new tab', function () {
    expect(matrix_normalize_link_target('https://example.com/page'))->toBe('_blank')
        ->and(matrix_normalize_link_target('/about-us/'))->toBe('_self')
        ->and(matrix_normalize_link_target('https://example.com/page', '_self'))->toBe('_blank');
});

test('matrix_normalize_link_target opens pdf urls in a new tab', function () {
    expect(matrix_is_pdf_url('/wp-content/uploads/2026/06/spmhs-addiction-services-booklet-web.pdf'))->toBeTrue()
        ->and(matrix_is_pdf_url('https://www.stpatricks.ie/media/3568/st-patricks-day-programmes.pdf?ver=1'))->toBeTrue()
        ->and(matrix_is_pdf_url('/about-us/'))->toBeFalse()
        ->and(matrix_normalize_link_target('/wp-content/uploads/file.pdf'))->toBe('_blank')
        ->and(matrix_normalize_link_target('https://www.stpatricks.ie/media/file.pdf', '_self'))->toBe('_blank');
});

test('matrix_process_external_links_in_html adds target blank and rel', function () {
    $html = '<p>Read <a href="https://example.com/report.pdf">the report</a>.</p>';
    $processed = matrix_process_external_links_in_html($html);

    expect($processed)->toContain('target="_blank"')
        ->and($processed)->toContain('rel="noopener noreferrer"');
});

test('matrix_process_external_links_in_html opens internal pdf links in a new tab', function () {
    $html = '<p>Read <a href="/wp-content/uploads/2026/06/guide.pdf">the guide</a>.</p>';
    $processed = matrix_process_external_links_in_html($html);

    expect($processed)->toContain('target="_blank"')
        ->and($processed)->toContain('rel="noopener noreferrer"')
        ->and($processed)->toContain('is-pdf-link');
});

test('matrix_process_external_links_in_html marks pdf list items for the pdf icon', function () {
    $html = '<ul><li><a href="https://www.stpatricks.ie/media/4215/css-overarching.pdf">Child Safeguarding Statement</a></li></ul>';
    $processed = matrix_process_external_links_in_html($html);

    expect($processed)->toContain('has-pdf-link')
        ->and($processed)->toContain('is-pdf-link');
});

test('matrix_normalize_acf_link forces external acf links to open in a new tab', function () {
    $link = matrix_normalize_acf_link([
        'title' => 'Walk in My Shoes',
        'url' => 'https://www.walkinmyshoes.ie/',
        'target' => '_self',
    ]);

    expect($link)->toMatchArray([
        'title' => 'Walk in My Shoes',
        'url' => 'https://www.walkinmyshoes.ie/',
        'target' => '_blank',
        'rel' => 'noopener noreferrer',
    ]);
});

test('matrix_link_newsletter_subtext_click_here links plain newsletter click here text', function () {
    expect(function_exists('matrix_link_newsletter_subtext_click_here'))->toBeTrue();

    $html = matrix_link_newsletter_subtext_click_here('<p>For healthcare newsletter click here</p>');

    expect($html)->toContain('<a href="https://www.stpatricks.ie/campaigns/subscribe-to-our-gp-enewsletter/">subscribe to our GP e-newsletter</a>');
});

test('matrix_process_external_links_in_html strips orphan Word comment anchors', function () {
    $html = '<p>Intro<a href="#_msocom_1"></a> and <a id="_msocom_1" href=""></a>more.</p>';
    $processed = matrix_process_external_links_in_html($html);

    expect($processed)->not->toContain('#_msocom')
        ->and($processed)->toContain('Intro')
        ->and($processed)->toContain('more.');
});

test('matrix_process_external_links_in_html rewrites vague link text from the URL', function () {
    $html = '<p>See <a href="https://example.com/locations/st-patricks-university-hospital/">Find out more</a>.</p>';
    $processed = matrix_process_external_links_in_html($html);

    expect($processed)->toContain('>St Patricks University Hospital<')
        ->and($processed)->not->toContain('>Find out more<');
});

test('matrix_resolve_link_accessible_name prefers context over vague titles', function () {
    expect(matrix_is_vague_link_text('click here'))->toBeTrue()
        ->and(matrix_resolve_link_accessible_name('Learn more', '/about-us/advocacy/', 'Advocacy'))->toBe('Advocacy')
        ->and(matrix_link_label_from_url('https://example.com/child-safeguarding-statement'))->toBe('Child Safeguarding Statement');
});

test('matrix_get_theme_path_redirect_map includes deleted page redirects', function () {
    $map = matrix_get_theme_path_redirect_map();

    expect($map)->toHaveKey('advocacy-services')
        ->and($map['advocacy-services'])->toBe('/about-us/our-present-and-future/advocacy-centre/')
        ->and($map)->toHaveKey('advocacy-services/youth-advocacy')
        ->and($map['advocacy-services/youth-advocacy'])->toBe('/about-us/advocacy/youth-advocacy/')
        ->and($map)->toHaveKey('public-education-anti-stigma-campaigns')
        ->and($map['public-education-anti-stigma-campaigns'])->toBe('/about-us/advocacy/public-education-anti-stigma-campaigns/')
        ->and($map)->toHaveKey('collaborative-efforts')
        ->and($map['collaborative-efforts'])->toBe('/about-us/advocacy/collaborative-efforts/')
        ->and($map)->toHaveKey('referrals')
        ->and($map['referrals'])->toBe('/healthcare-professionals/')
        ->and($map)->toHaveKey('getting-help')
        ->and($map['getting-help'])->toBe('/service-users-and-visitors/')
        ->and($map)->toHaveKey('get-involved')
        ->and($map['get-involved'])->toBe('/about-us/support-us/')
        ->and($map)->toHaveKey('mental-health')
        ->and($map['mental-health'])->toBe('/service-users-and-visitors/about-mental-health/')
        ->and($map)->toHaveKey('anxiety')
        ->and($map['anxiety'])->toBe('/mental-health/anxiety/')
        ->and($map)->toHaveKey('addiction-dual-diagnosis')
        ->and($map['addiction-dual-diagnosis'])->toBe('/mental-health/addiction-dual-diagnosis/')
        ->and($map)->toHaveKey('schizophrenia')
        ->and($map['schizophrenia'])->toBe('/mental-health/schizophrenia/')
        ->and($map)->toHaveKey('inpatient-hospital-care')
        ->and($map['inpatient-hospital-care'])->toBe('/inpatient-care/')
        ->and($map)->toHaveKey('outpatient-clinics/about-the-dean-clinics')
        ->and($map['outpatient-clinics/about-the-dean-clinics'])->toBe('/what-we-offer/outpatient-care-dean-clinics/')
        ->and($map)->toHaveKey('careers/clinical-nurse-manager-2')
        ->and($map['careers/clinical-nurse-manager-2'])->toBe('/about-us/careers/')
        ->and($map)->toHaveKey('about-us/extending-our-services')
        ->and($map['about-us/extending-our-services'])->toBe('/about-us/our-present-and-future/extending-and-enhancing-our-services/')
        ->and($map)->toHaveKey('extending-our-services')
        ->and($map['extending-our-services'])->toBe('/about-us/our-present-and-future/extending-and-enhancing-our-services/');
});
