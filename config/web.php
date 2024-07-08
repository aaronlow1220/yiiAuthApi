<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$urlManager = require __DIR__ . '/urlManager.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'rMyLGf-KEQFr5_cEvOR6ZDcHZNIKzIbo',
            'parsers' => [
                "application/json" => "yii\web\JsonParser",
            ],
        ],
        'user' => [
            'identityClass' => 'app\models\users',
            'enableAutoLogin' => true,
        ],
        'db' => $db,
        
        'urlManager' => $urlManager,
    ],
    'params' => $params,
];

return $config;
