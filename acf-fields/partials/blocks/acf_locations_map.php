<?php

use StoutLogic\AcfBuilder\FieldsBuilder;

$locations_map = new FieldsBuilder('locations_map', [
    'label' => 'Locations Map',
]);

$locations_map
    ->addTab('Content', ['label' => 'Content'])
    ->addText('heading', [
        'label' => 'Heading',
        'default_value' => 'Find us',
    ])
    ->addSelect('heading_tag', [
        'label' => 'Heading Tag',
        'choices' => [
            'h1' => 'H1',
            'h2' => 'H2',
            'h3' => 'H3',
            'h4' => 'H4',
            'h5' => 'H5',
            'h6' => 'H6',
        ],
        'default_value' => 'h2',
    ])
    ->addWysiwyg('intro_text', [
        'label' => 'Intro Text',
        'instructions' => 'Optional supporting copy below the heading.',
        'tabs' => 'visual',
        'toolbar' => 'basic',
        'media_upload' => 0,
    ])
    ->addSelect('source_mode', [
        'label' => 'Locations Source',
        'choices' => [
            'all' => 'All published locations with map coordinates',
            'selected' => 'Selected locations',
        ],
        'default_value' => 'all',
    ])
    ->addRelationship('selected_locations', [
        'label' => 'Selected Locations',
        'instructions' => 'Only used when source is set to selected locations.',
        'post_type' => ['locations'],
        'filters' => ['search'],
        'return_format' => 'object',
        'conditional_logic' => [
            [
                [
                    'field' => 'source_mode',
                    'operator' => '==',
                    'value' => 'selected',
                ],
            ],
        ],
    ])
    ->addLink('locations_button', [
        'label' => 'Locations Button',
        'instructions' => 'Optional button below the location list (e.g. View all locations).',
        'return_format' => 'array',
    ])
    ->addLink('directions_link', [
        'label' => 'Directions Link',
        'instructions' => 'Optional outline button shown on the map (e.g. Directions and Parking).',
        'return_format' => 'array',
    ])
    ->addTab('Map Settings', ['label' => 'Map Settings'])
    ->addNumber('map_center_lat', [
        'label' => 'Map Center Latitude',
        'default_value' => 53.42,
        'step' => 0.000001,
    ])
    ->addNumber('map_center_lng', [
        'label' => 'Map Center Longitude',
        'default_value' => -7.69,
        'step' => 0.000001,
    ])
    ->addNumber('map_zoom', [
        'label' => 'Map Zoom Level',
        'default_value' => 7,
        'min' => 1,
        'max' => 20,
    ])
    ->addSelect('tile_provider', [
        'label' => 'Map Tile Provider',
        'choices' => [
            'jawg-lagoon' => 'Jawg Lagoon',
            'jawg-light' => 'Jawg Light',
            'jawg-dark' => 'Jawg Dark',
            'osm' => 'OpenStreetMap (fallback)',
        ],
        'default_value' => 'jawg-lagoon',
    ])
    ->addText('tile_api_key', [
        'label' => 'Tile API Key',
        'instructions' => 'Jawg access token. Falls back to the Jawg Access Token theme option or JAWG_ACCESS_TOKEN env var when empty.',
    ])
    ->addTab('Design', ['label' => 'Design'])
    ->addColorPicker('background_color', [
        'label' => 'Background Color',
        'default_value' => '#FFFFFF',
    ]);

return $locations_map;
