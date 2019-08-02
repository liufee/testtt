<?php
return [
    'components' => [
        'db' => [
            'class' => yii\db\Connection::className(),
            'dsn' => 'mysql:host=localhost;port=3306;dbname=cms',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
            'tablePrefix' => 'feehi_12',
        ],
        'mailer' => [
            'class' => yii\swiftmailer\Mailer::className(),
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
    ],
    'bootstrap' => ['debug'],
    'modules' => [
        'debug' => [
            'class' => yii\debug\Module::className(),
            'allowedIPs' => ['127.0.0.1', '::1']
        ]
    ]
];