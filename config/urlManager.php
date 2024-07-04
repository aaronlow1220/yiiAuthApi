<?php
return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'rules' => [
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'v1/auth',
        ],
        'GET v1/auth' => 'v1/auth/index',
        'POST v1/auth/register' => 'v1/auth/register',
        'GET v1/auth/user/<id>'=> 'v1/auth/user',
    ],
];
