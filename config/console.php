<?php

$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'components' => [
        'db' => $db,
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
