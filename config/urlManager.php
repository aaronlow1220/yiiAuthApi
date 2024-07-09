<?php
return [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'rules' => [ // Auth
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'v1/auth',
            'pluralize' => false,
            'extraPatterns' => [
                'POST login' => 'login',
                'POST register' => 'create',
                'POST logout' => 'logout',
            ],
        ],
        [ // User
            'class' => 'yii\rest\UrlRule',
            'controller' => 'v1/user',
            'pluralize' => false,
            'extraPatterns' => [
                'GET <uuid>' => 'user',
                'PUT <uuid>' => 'update',
            ],
        ],
    ],
];
