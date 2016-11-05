<?php
return [
    5 => [
        'type' => 1,
        'description' => 'Поддержка',
        'ruleName' => 'userRole',
    ],
    4 => [
        'type' => 1,
        'description' => 'Менеджер',
        'ruleName' => 'userRole',
        'children' => [
            3,
        ],
    ],
    3 => [
        'type' => 1,
        'description' => 'Наблюдатель',
        'ruleName' => 'userRole',
    ],
    1 => [
        'type' => 1,
        'description' => 'Клиент',
        'ruleName' => 'userRole',
    ],
    2 => [
        'type' => 1,
        'description' => 'Партнер',
        'ruleName' => 'userRole',
    ],
    0 => [
        'type' => 1,
        'description' => 'Администратор',
        'ruleName' => 'userRole',
        'children' => [
            5,
            4,
            3,
            1,
            2,
        ],
    ],
];
