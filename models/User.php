<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use \yii\db\BaseActiveRecord;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $uuid
 * @property string $username
 * @property string $email
 * @property string $password
 * @property int $status
 * @property string $auth_key
 * @property string $access_token
 * @property string $created_at
 * @property string $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    const SCENARIO_REGISTER = 'register';
    const SCENARIO_LOGIN = 'login';
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    public $confirmPassword;
    public $_user;

    /**
     * Rules for validation.
     * 
     * @return array<string> Rules.
     */
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

    /**
     * Scenarios for validation.
     * 
     * @return array<string> Scenarios.
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_REGISTER] = ['username', 'email', 'password', 'confirmPassword'];
        $scenarios[self::SCENARIO_LOGIN] = ['email', 'password'];
        return $scenarios;
    }

    /**
     * Returns table name of users.
     * 
     * @return string Table name
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * Get user by auto increment id.
     * 
     * @param int $id User auto increment id.
     * @return BaseActiveRecord Return user object with the id.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Get user by uuid.
     * 
     * @param string $uuid User uuid.
     * @return BaseActiveRecord Return user object with the uuid.
     */
    public static function findIdentityByUUID($uuid)
    {
        return static::findOne(["uuid" => $uuid]);
    }

    /**
     * Get user by access token.
     * 
     * @param string $token Access token.
     * @param string|null $type
     * @return BaseActiveRecord Return user object with the access token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Generate new access token for login.
     * 
     * @return string New access token.
     */
    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
        return $this->access_token;
    }

    /**
     * Get user's auto increment id.
     * 
     * @return int User id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get user's auth key.
     * 
     * @return string Auth key.
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Validate auth key.
     * 
     * @param string $authKey Auth key.
     * @return bool Whether the auth key is valid.
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validate password.
     * 
     * @param string $password Input password.
     * @return bool Whether the password is valid.
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * Get user by email.
     * 
     * @param string $email User email.
     * @return BaseActiveRecord Return user object with the email.
     */
    public static function getUser($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * Actions before saving a record.
     * 
     * @param bool $insert Whether the record is inserted.
     * @return bool Whether the record is saved.
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->isNewRecord) {
            $this->auth_key = Yii::$app->security->generateRandomString();
            $this->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
        }
        return true;
    }

    /**
     * Register user.
     * 
     * @return bool|string Whether the user is registered.
     *                     If the user is registered, return the uuid.
     */
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

    /**
     * Login user.
     * 
     * @return bool | string | array Whether the user is logged in. 
     *                               If the user is logged in, return the access token. 
     *                               If the user is not logged in, return the error message.
     */
    public function login()
    {
        $_user = User::getUser($this->email);

        if (!$_user || !$_user->validatePassword($this->password)) {
            return false;
        }

        $accessToken = $_user->generateAccessToken();
        $_user->access_token = $accessToken;
        if (!$_user->update()) {
            return $_user->getErrors();
        }
        return $accessToken;
    }

    /**
     * Generate UUID for user.
     * 
     * @return string UUID.
     */
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