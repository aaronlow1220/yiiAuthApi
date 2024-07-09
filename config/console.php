<?php

$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'components' => [
        'db' => $db,
    ],
];

return $config;
