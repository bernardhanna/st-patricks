<?php

$heading = trim((string) get_sub_field('heading'));
$subtext = get_sub_field('subtext');
$list_id = function_exists('matrix_sanitize_newsletter_list_id')
    ? matrix_sanitize_newsletter_list_id(get_sub_field('newsletter_list_id') ?: get_sub_field('brevo_list_id'))
    : trim((string) (get_sub_field('newsletter_list_id') ?: get_sub_field('brevo_list_id')));

get_template_part('template-parts/footer/newsletter', null, [
    'force' => true,
    'heading' => $heading !== '' ? $heading : 'Sign up to our newsletter',
    'heading_tag' => 'h2',
    'subtext' => is_string($subtext) ? $subtext : '',
    'list_id' => $list_id,
    'link_healthcare_signup' => false,
]);
