
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
        ],
        'update' => [
            'name', 'description'
        ]
    ],

    'forms' => [
        'isValid' => 'name',
        'rules'   => [
            'name' => 'required|string|max:50',
            'description' => 'required|string|max:255',
            'content_default' => 'required|array',
        ],
        'fillable' => [
            'name', 'description','content_default'
        ],
        'isNotDelete' => 'content',
        'update' => [
            'name', 'description'
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
        ],
        'update' => [
            'name', 'description'
        ]
    ]

];
