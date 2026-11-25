<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$callout_bar = new FieldsBuilder('callout_bar', [
    'label' => 'Callout Bar',
]);

$callout_bar
    ->addTab('Content', ['label' => 'Content'])
        ->addText('message', [
            'label' => 'Message',
            'instructions' => 'Single line of centered notice text for the bar.',
            'default_value' => 'SPMHS is a registered charity (Registered Charity Number (RCN): 20000370).',
        ]);

matrix_acf_append_shared_vertical_padding_fields($callout_bar, ['add_design_tab' => true]);

return $callout_bar;
