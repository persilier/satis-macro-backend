
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
            'content_default' => 'required|array',
        ],
        'fillable' => [
            'name', 'description','content_default'
        ],
        'isNotDelete' => 'content'
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
