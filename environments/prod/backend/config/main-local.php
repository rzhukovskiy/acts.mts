<?php
return [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
        ],
        'user' => [
            'identityCookie' => [
                'name' => '_identity',
                'httpOnly' => true,
                'path' => '/',
                'domain' => '.mtransservice.ru',
            ],
        ],
        'session' => [
            'cookieParams' =>
                ['domain' => '.mtransservice.ru']
        ],
    ],
];
