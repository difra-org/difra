<?php

return [
    'instances' => [
        'main' => [
            'include' => [
                'jquery' => true,
                'all' => true,
                'bootstrap' => true,
                'switcher' => true
            ]
        ],
        'adm' => [
            'compatibility' => 6,
            'include' => [
                'jquery' => true,
                'switcher' => true,
                'all' => true
            ]
        ],
        'all' => [
            'include' => [
                'jquery' => true
            ]
        ],
        'bootstrap' => [
            'include' => [
                'popper' => true
            ]
        ],
        'popper' => [
            'include' => [
                'jquery' => true
            ]
        ],
        'console' => [
            'include' => [
                'jquery' => true
            ]
        ],
        'switcher' => [
            'include' => [
                'jquery' => true,
                'websymbols' => true
            ]
        ]
    ]
];
