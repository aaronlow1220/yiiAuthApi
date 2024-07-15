<?php

$params = require __DIR__ . '/params.php';
$urlManager = require __DIR__ . '/urlManager.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'components' => [
        'request' => [
            'cookieValidationKey' => 'rMyLGf-KEQFr5_cEvOR6ZDcHZNIKzIbo',
            'enableCsrfValidation' => false,
            'parsers' => [
                "application/json" => "yii\web\JsonParser",
            ],
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => $params['db']['dsn'],
            'username'=> $params['db']['username'],
            'password'=> $params['db']['password'],
            'charset' => 'utf8mb4',
        ],
        'urlManager' => $urlManager,
    ],
];

return $config;
