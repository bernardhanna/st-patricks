<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$wysiwyg = new FieldsBuilder('wysiwyg', [
    'label' => 'General WYSIWYG Section',
]);

$wysiwyg
  ->addTab('Content', ['placement' => 'top'])
    ->addWysiwyg('text_content', [
        'label' => 'Content',
        'instructions' => 'Enter the content using headings, paragraphs, and inline formatting as needed.',
        'wrapper' => ['class' => 'relative'],
        'media_upload' => 0,
        'toolbar' => 'full',
    ]);

return $wysiwyg;
