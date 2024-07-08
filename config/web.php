<?php

$db = require __DIR__ . '/db.php';
$urlManager = require __DIR__ . '/urlManager.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'rMyLGf-KEQFr5_cEvOR6ZDcHZNIKzIbo',
            'parsers' => [
                "application/json" => "yii\web\JsonParser",
            ],
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'user' => [
            'identityClass' => 'app\models\users',
        ],
        'db' => $db,
        'urlManager' => $urlManager,
    ],
];

return $config;
