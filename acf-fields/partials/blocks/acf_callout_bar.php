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

return $callout_bar;
