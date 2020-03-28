<?php


return [
    'type' => [
        'models', 'forms'
    ],

    'models' => [
        'isValid' => 'name',
        'rules'   => [
            'name' => 'required|string',
            'description' => 'required|string',
            'fonction' => 'required|string',
        ],
        'fillable' => [
            'name', 'description', 'fonction'
        ]
    ],

    'forms' => [
        'isValid' => 'name',
        'rules'   => [
            'name' => 'required|string',
            'description' => 'required|string',
            'content' => 'required|string',

        ],
        'fillable' => [
            'name', 'description', 'content'
        ]
    ]
        
];