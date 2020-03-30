<?php


return [
    'type' => [
        'models', 'forms','action-forms'
    ],

    'models' => [
        'isValid' => 'name',
        'rules'   => [
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'fonction' => 'required|string',
        ],
        'fillable' => [
            'name', 'description', 'fonction'
        ]
    ],

    'forms' => [
        'isValid' => 'name',
        'rules'   => [
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:255',
        ],
        'fillable' => [
            'name', 'description',
        ]
    ],

    'action-forms' => [
        'isValid' => 'name',
        'rules'   => [
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'endpoint' => 'required|string',
        ],
        'fillable' => [
            'name', 'description', 'endpoint'
        ]
    ]

];
