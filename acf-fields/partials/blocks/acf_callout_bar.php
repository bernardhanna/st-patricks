<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$callout_bar = new FieldsBuilder('callout_bar', [
    'label' => 'Callout Bar',
]);

$callout_bar
    ->addTab('Content', ['label' => 'Content'])
        ->addText('message', [
            'label' => 'Message',
            'instructions' => 'Centered notice text for the bar. Basic HTML links are allowed.',
            'default_value' => 'SPMHS is a registered charity (Registered Charity Number (RCN): 20000370).',
        ])
        ->addSelect('bar_style', [
            'label' => 'Bar style',
            'choices' => [
                'default' => 'Default (light teal)',
                'navy' => 'Navy',
            ],
            'default_value' => 'default',
        ]);

matrix_acf_append_shared_vertical_padding_fields($callout_bar, ['add_design_tab' => true]);

return $callout_bar;
