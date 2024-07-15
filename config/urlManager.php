<?php
return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'rules' => [ // Auth
        'GET apidoc' => 'v1/open-api/index',
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'auth' => 'v1/auth',
            ],
            'except' => ['index'],
            'pluralize' => false,
            'extraPatterns' => [
                'POST login' => 'login',
                'POST register' => 'register',
                'POST logout' => 'logout',
            ],
        ],
        [ // User
            'class' => 'yii\rest\UrlRule',
            'controller' => [
                'user' => 'v1/user',

            ],
            'except' => ['index'],
            'pluralize' => false,
            'extraPatterns' => [
                'GET <uuid>' => 'user',
                'PUT <uuid>' => 'update',
            ],
        ],
    ],
];
