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
            'extraPatterns' => [
                'POST login' => 'login',
                'POST register' => 'create',
                'POST logout' => 'logout',
            ],
        ],
        [
            'class' => 'yii\rest\UrlRule',
            'controller' => 'v1/user',
            'pluralize' => false,
            'extraPatterns' => [
                'PUT <uuid>' => 'update',
            ],
        ],
    ],
];
