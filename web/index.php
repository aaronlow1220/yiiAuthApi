<?php

use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use yii\web\Application;

require __DIR__.'/../vendor/autoload.php';

$repository = RepositoryBuilder::createWithNoAdapters()
    ->addAdapter(EnvConstAdapter::class)
    ->addWriter(PutenvAdapter::class)
    ->immutable()
    ->make();

$dotenv = Dotenv\Dotenv::create($repository, dirname(__DIR__), '.env');
$dotenv->safeLoad();

defined('YII_DEBUG') or define('YII_DEBUG', 'true' == getenv('YII_DEBUG'));
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV'));

require __DIR__.'/../vendor/yiisoft/yii2/Yii.php';
$config = require __DIR__.'/../config/web.php';

(new Application($config))->run();
