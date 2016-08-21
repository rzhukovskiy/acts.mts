<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'language' => 'ru',
    'timeZone' => 'UTC',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager','defaultRoles' => [
                \common\models\User::ROLE_ADMIN,
                \common\models\User::ROLE_CLIENT,
                \common\models\User::ROLE_PARTNER,
                \common\models\User::ROLE_WATCHER,
            ],
            'itemFile' => '@common/components/rbac/items.php',
            'assignmentFile' => '@common/components/rbac/assignments.php',
            'ruleFile' => '@common/components/rbac/rules.php'
        ],
        'formatter' => [
            'locale' => 'ru-RU',
            'timeZone' => 'Europe/Moscow',
            'defaultTimeZone' => 'UTC',
        ],
    ],
];
