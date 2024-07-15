<?php

namespace v1\components;

use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\log\Logger;
use yii\rest\ActiveController;
use yii\web\IdentityInterface;
use yii\web\Response;

/**
 * This is a base API controller
 */
class ActiveApiController extends ActiveController
{
    /**
     * @var string $modelClass
     */
    public $modelClass = 'yii\base\DynamicModel';

    /**
     * @var IdentityInterface $webUser
     */
    protected $webUser;

    /**
     * @var string $user Component id of yii\web\User
     */
    protected $user = 'user';

    /**
     * @var array<string, string> $serializer
     */
    public $serializer = [
        'class' => 'v1\components\ApiSerializer',
        'collectionEnvelope' => '_data',
        'metaEnvelope' => '_meta'
    ];

    /**
     * init
     *
     * @return void
     */
    public function init(): void
    {
        parent::init();
        $this->webUser = $this->module->get($this->user);
    }

    /**
     * behaviors
     *
     * @return array<string, mixed>
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        // set only body format and response format
        $behaviors['contentNegotiator']['formats'] = ['application/json' => Response::FORMAT_JSON];

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
        ];

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBearerAuth::class,
            ],
            'except' => $this->authExcept()
        ];

        return $behaviors;
    }

    /**
     * auth exception list
     *
     * @return string[]
     */
    protected function authExcept(): array
    {
        return ['options'];
    }

    /**
     * get request parameters
     *
     * @return array<string, mixed>
     */
    protected function getRequestParams(): array
    {
        $requestParams = $this->module->get('request')->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = $this->module->get('request')->getQueryParams();
        }

        return $requestParams;
    }

    /**
     * log error
     *
     * @param mixed $message
     * @param string $category
     * @return void
     */
    protected function error(mixed $message, string $category = 'application'): void
    {
        $this->log(Logger::LEVEL_ERROR, $message, $category);
    }

    /**
     * log warning
     *
     * @param mixed $message
     * @param string $category
     * @return void
     */
    protected function warning(mixed $message, string $category = 'application'): void
    {
        $this->log(Logger::LEVEL_WARNING, $message, $category);
    }

    /**
     * log info
     *
     * @param mixed $message
     * @param string $category
     * @return void
     */
    protected function info(mixed $message, string $category = 'application'): void
    {
        $this->log(Logger::LEVEL_INFO, $message, $category);
    }

    /**
     * log trace
     *
     * @param mixed $message
     * @param string $category
     * @return void
     */
    protected function trace(mixed $message, string $category = 'application'): void
    {
        $this->log(Logger::LEVEL_TRACE, $message, $category);
    }

    /**
     * log beginProfile
     *
     * @param string $token
     * @param string $category
     * @return void
     */
    protected function beginProfile(string $token, string $category = 'application'): void
    {
        $this->module->get('log')->beginProfile($token, $category);
    }

    /**
     * log endProfile
     *
     * @param string $token
     * @param string $category
     * @return void
     */
    protected function endProfile(string $token, string $category = 'application'): void
    {
        $this->module->get('log')->endProfile($token, $category);
    }

    /**
     * log
     *
     * @param int $level
     * @param mixed $message
     * @param string $category
     * @return void
     */
    private function log(int $level, mixed $message, string $category): void
    {
        $this->module->get('log')->log($message, $level, $category);
    }
}
