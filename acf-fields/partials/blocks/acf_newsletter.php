<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$newsletter_block = new FieldsBuilder('newsletter', [
    'label' => 'Newsletter signup',
]);

$newsletter_block
    ->addText('heading', [
        'label' => 'Heading',
        'default_value' => 'Sign up to our newsletter',
    ])
    ->addWysiwyg('subtext', [
        'label' => 'Subtext',
        'tabs' => 'visual',
        'media_upload' => 0,
        'toolbar' => 'basic',
        'delay' => 1,
    ])
    ->addSelect('newsletter_list_id', [
        'label' => 'Mailing list',
        'instructions' => 'Choose which Mailchimp audience (or Brevo list) this form subscribes to. Leave empty to use the default from Theme Options → Newsletter.',
        'choices' => [],
        'ui' => 1,
        'allow_null' => 1,
        'return_format' => 'value',
    ]);

return $newsletter_block;
