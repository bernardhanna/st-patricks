<?php

$heading = trim((string) get_sub_field('heading'));
$subtext = get_sub_field('subtext');
$list_id = function_exists('matrix_sanitize_newsletter_list_id')
    ? matrix_sanitize_newsletter_list_id(get_sub_field('newsletter_list_id') ?: get_sub_field('brevo_list_id'))
    : trim((string) (get_sub_field('newsletter_list_id') ?: get_sub_field('brevo_list_id')));

$padding_settings = get_sub_field('padding_settings');
$padding_classes = function_exists('matrix_flexi_resolve_vertical_padding_classes')
    ? matrix_flexi_resolve_vertical_padding_classes(
        get_sub_field('vertical_padding'),
        is_array($padding_settings) ? $padding_settings : [],
        'py-12 lg:py-24'
    )
    : 'py-12 lg:py-24';

get_template_part('template-parts/footer/newsletter', null, [
    'force' => true,
    'heading' => $heading !== '' ? $heading : 'Sign up to our newsletter',
    'heading_tag' => 'h2',
    'subtext' => is_string($subtext) ? $subtext : '',
    'list_id' => $list_id,
    'link_healthcare_signup' => false,
    'padding_classes' => $padding_classes,
]);
