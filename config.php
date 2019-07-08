<?php

return [
    'instances' => [
        'main' => [
            'include' => [
                'jquery' => true,
                'bootstrap' => true,
                'all' => true,
                'switcher' => true
            ]
        ],
        'adm' => [
//            'compatibility' => 6,
            'include' => [
                'jquery' => true,
//                'switcher' => true,
                'bootstrap' => true,
                'all' => true,
                'fontawesome' => true
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
                'jquery' => true
//                'websymbols' => true
            ]
        ],
        'bootstrap-select' => [
            'include' => [
                'bootstrap' => true
            ]
        ]
    ]
];
