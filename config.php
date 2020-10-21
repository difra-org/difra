<?php

return [
    'instances' => [
        'main' => [
            'include' => [
                'jquery3' => true,
                'bootstrap' => true,
                'all' => true,
                'switcher' => true
            ]
        ],
        'adm' => [
//            'compatibility' => 6,
            'include' => [
                'jquery3' => true,
//                'switcher' => true,
                'bootstrap' => true,
                'all' => true,
                'fontawesome' => true
            ]
        ],
        'all' => [
            'include' => [
                'jquery3' => true
            ]
        ],
        'bootstrap' => [
            'include' => [
		'popper' => true,
		'jquery' => true
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
