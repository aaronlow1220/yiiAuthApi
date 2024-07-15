<?php

$params = require __DIR__ . '/params.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => $params['db']['dsn'],
            'username'=> $params['db']['username'],
            'password'=> $params['db']['password'],
            'charset' => 'utf8mb4',
        ],
    ],
    'controllerMap'=>[
        'genmodel' => [
            'class' => 'AtelliTech\Yii2\Utils\ModelGeneratorController',
            'db' => 'db',
            'path' => '@app/models',
            'namespace' => 'app\models',
        ],
        'genapi' => [
            'class' => 'AtelliTech\Yii2\Utils\ApiGeneratorController',
            'db' => 'db',
        ],
    ]
];

return $config;
