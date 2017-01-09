<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=mts',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'tablePrefix' => 'acts_',
        ],
        'db_old' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=old_mts',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'tablePrefix' => 'mts_',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
    ],
    'aliases' => [
        '@backWeb' => 'http://offer.mtransservice.local/',
        '@frontWeb' => 'http://docs.mtransservice.local/',
    ],
];
