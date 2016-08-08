<?php
return [
    3 => [
        'type' => 1,
        'description' => 'Наблюдатель',
        'ruleName' => 'userRole',
    ],
    1 => [
        'type' => 1,
        'description' => 'Партнер',
        'ruleName' => 'userRole',
    ],
    2 => [
        'type' => 1,
    ],
    0 => [
        'type' => 1,
        'description' => 'Администратор',
        'ruleName' => 'userRole',
        'children' => [
            3,
            1,
            2,
        ],
    ],
];
