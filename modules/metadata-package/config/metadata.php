<?php


return [

    'models' => [
        'isValid' => 'name',
        'rules'   => [
                'name' => 'required|string',
                'description' => 'required|string',
                'fonction' => 'required|string',
            ]
    ],

    'forms' => [
        'isValid' => 'name',
        'rules'   => [


        ]
    ]
];