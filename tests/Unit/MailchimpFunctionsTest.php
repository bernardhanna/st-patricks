<?php

require_once dirname(__DIR__, 2) . '/inc/newsletter-functions.php';
require_once dirname(__DIR__, 2) . '/inc/forms/mailchimp-functions.php';

test('mailchimp datacenter is read from the api key suffix', function () {
    expect(matrix_get_mailchimp_datacenter('abc123-us21'))->toBe('us21')
        ->and(matrix_get_mailchimp_datacenter('not-a-key'))->toBe('');
});

test('mailchimp audience payloads parse id and name pairs', function () {
    $lists = matrix_parse_mailchimp_lists_payload([
        'lists' => [
            ['id' => 'gpnlst1234', 'name' => 'GP eNewsletter'],
            ['id' => 'mainlist99', 'name' => 'General'],
            ['id' => '', 'name' => 'Ignored'],
        ],
        'total_items' => 2,
    ]);

    expect($lists)->toBe([
        'gpnlst1234' => 'GP eNewsletter',
        'mainlist99' => 'General',
    ]);
});

test('newsletter list signatures reject unsigned or swapped mailchimp ids', function () {
    $signature = matrix_newsletter_list_signature('gpnlst1234', 'mailchimp');

    expect(matrix_newsletter_list_signature_is_valid('gpnlst1234', $signature, 'mailchimp'))->toBeTrue()
        ->and(matrix_newsletter_list_signature_is_valid('mainlist99', $signature, 'mailchimp'))->toBeFalse()
        ->and(matrix_newsletter_list_signature_is_valid('gpnlst1234', 'tampered', 'mailchimp'))->toBeFalse()
        ->and(matrix_get_signed_newsletter_list_id_from_request([
            '_cfg_newsletter_list_id' => 'gpnlst1234',
            '_cfg_newsletter_sig' => $signature,
        ]))->toBe('gpnlst1234')
        ->and(matrix_get_signed_newsletter_list_id_from_request([
            '_cfg_newsletter_list_id' => 'mainlist99',
            '_cfg_newsletter_sig' => $signature,
        ]))->toBe('');
});

test('GP newsletter list id prefers a named GP audience', function () {
    $audiences = [
        'mainlist99' => 'General',
        'gpnlst1234' => 'GP eNewsletter',
    ];

    expect(matrix_find_newsletter_list_id_by_name($audiences, 'GP'))->toBe('gpnlst1234')
        ->and(matrix_newsletter_list_id_from_flexi_rows([
            ['acf_fc_layout' => 'hero_with_breadcrumbs'],
            ['acf_fc_layout' => 'newsletter', 'heading' => 'GP Newsletter', 'newsletter_list_id' => 'gpnlst1234'],
        ]))->toBe('gpnlst1234')
        ->and(matrix_resolve_gp_newsletter_list_id($audiences))->toBe('gpnlst1234');
});

test('mailchimp subscribe uses a signed audience and ignores unsigned posted ids', function () {
    $signature = matrix_newsletter_list_signature('gpnlst1234', 'mailchimp');

    __wp_stub('get_field', fn ($field, $post_id = false) => match ($field) {
        'newsletter_provider' => 'mailchimp',
        'mailchimp_list_id' => 'defaultlist',
        default => null,
    });

    expect(matrix_resolve_subscribe_mailchimp_list_id([
        '_cfg_newsletter_list_id' => 'gpnlst1234',
        '_cfg_newsletter_sig' => $signature,
        'list_ids' => 'attacker',
    ]))->toBe('gpnlst1234')
        ->and(matrix_resolve_subscribe_mailchimp_list_id([
            'list_ids' => 'attacker',
        ]))->toBe('defaultlist');
});
