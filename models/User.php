<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    const SCENARIO_REGISTER = 'register';
    const SCENARIO_LOGIN = 'login';
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    private $access_token;
    public $confirmPassword;

    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email'],
            ['username', 'required', 'on' => self::SCENARIO_REGISTER],
            ['email', 'unique', 'on' => self::SCENARIO_REGISTER],
            ['confirmPassword', 'compare', 'compareAttribute' => 'password', 'on' => self::SCENARIO_REGISTER],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_REGISTER] = ['username', 'email', 'password', 'confirmPassword'];
        $scenarios[self::SCENARIO_LOGIN] = ['email', 'password'];
        return $scenarios;
    }

    public static function tableName()
    {
        return 'users';
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByUUID($uuid)
    {
        return static::findOne(["uuid" => $uuid]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
        return $this->access_token;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    public static function getUser($email)
    {
        return static::findOne(['email' => $email]);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            return false;
        }
        if ($this->isNewRecord) {
            $this->auth_key = Yii::$app->security->generateRandomString();
            $this->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
        }
        return true;
    }

    // Auth
    public function register()
    {
        $uuid = static::gen_uuid();
        $this->uuid = $uuid;
        $this->status = User::STATUS_ACTIVE;

        if (!$this->save()) {
            return false;
        }

        return $uuid;
    }

    public static function gen_uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}