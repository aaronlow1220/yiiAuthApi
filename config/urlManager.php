<?php
return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'rules' => [
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'v1/auth',
            'pluralize' => false,
        ],
        'POST v1/auth/login' => 'v1/auth/login',
        'POST v1/auth/logout' => 'v1/auth/logout',
        'PUT v1/user/'=> 'v1/auth/update-user',
        'GET v1/user/<uuid>'=> 'v1/auth/user',
    ],
];
