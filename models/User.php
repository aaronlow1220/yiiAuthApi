<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\web\IdentityInterface;

/**
 * @OA\Schema(
 *      schema="User",
 *      title="User Model",
 *      description="This model is used to store user data",
 *      required={"email", "password"},
 *      @OA\Property(property="id", type="int", description="Auto increment id #auto increment #primary key", maxLength=20),
 *      @OA\Property(property="uuid", type="string", description="Unique id", maxLength=255),
 *      @OA\Property(property="username", type="string", description="username", maxLength=255),
 *      @OA\Property(property="email", type="string", description="email", maxLength=255),
 *      @OA\Property(property="password", type="string", description="password", maxLength=255),
 *      @OA\Property(property="status", type="int", description="user status 1:Active 0:Inactive", default=0 ,maxLength=4),
 *      @OA\Property(property="auth_key", type="string", description="authentication key", maxLength=255),
 *      @OA\Property(property="access_token", type="string", description="access token", maxLength=255),
 *      @OA\Property(property="created_at", type="int", description="unixtime", maxLength=10),
 *      @OA\Property(property="updated_at", type="int", description="unixtime", maxLength=10),
 * )
 *
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * Scenario for register.
     *
     * @var string
     */
    public const SCENARIO_REGISTER = 'register';

    /**
     * Scenario for login.
     *
     * @var string
     */
    public const SCENARIO_LOGIN = 'login';

    /**
     * Scenario for update.
     *
     * @var string
     */
    public const SCENARIO_UPDATE = 'update';

    /**
     * Status for active account.
     *
     * @var int
     */
    public const STATUS_ACTIVE = 1;

    /**
     * Status for inactive account.
     *
     * @var int
     */
    public const STATUS_INACTIVE = 0;

    /**
     * Confirm password of the user when register.
     *
     * @var string
     */
    public $confirmPassword;

    /**
     * User object.
     *
     * @var User
     */
    public $_user;

    /**
     * Behaviors for the model.
     *
     * @return array<int, mixed> return behaviors
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Rules for validation.
     *
     * @return array<int, mixed> return rules
     */
    public function rules()
    {
        return [
            ['email', 'email'],
            ['username', 'required', 'on' => self::SCENARIO_REGISTER],
            ['email', 'unique', 'on' => [self::SCENARIO_REGISTER, self::SCENARIO_UPDATE]],
            ['confirmPassword', 'compare', 'compareAttribute' => 'password', 'on' => self::SCENARIO_REGISTER],
        ];
    }

    /**
     * Scenarios for validation.
     *
     * @return array<string> return Scenarios
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_REGISTER] = ['username', 'email', 'password', 'confirmPassword'];
        $scenarios[self::SCENARIO_LOGIN] = ['email', 'password'];
        $scenarios[self::SCENARIO_UPDATE] = ['username', 'email', 'password'];

        return $scenarios;
    }

    /**
     * Returns table name of users.
     *
     * @return string Return Table name
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * Get user by auto increment id.
     *
     * @param int $id user auto increment id
     * @return BaseActiveRecord return user object with the id
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Get user by uuid.
     *
     * @param string $uuid user uuid
     * @return BaseActiveRecord return user object with the uuid
     */
    public static function findIdentityByUUID($uuid)
    {
        return static::findOne(['uuid' => $uuid]);
    }

    /**
     * Get user by access token.
     *
     * @param string $token access token
     * @param null|string $type
     * @return BaseActiveRecord return user object with the access token
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Generate new access token for login.
     *
     * @return string return new access token
     */
    public function generateAccessToken()
    {
        $this['access_token'] = Yii::$app->security->generateRandomString();

        return $this['access_token'];
    }

    /**
     * Get user's auto increment id.
     *
     * @return int return user id
     */
    public function getId()
    {
        return $this['id'];
    }

    /**
     * Get user's auth key.
     *
     * @return string return auth key
     */
    public function getAuthKey()
    {
        return $this['auth_key'];
    }

    /**
     * Validate auth key.
     *
     * @param string $authKey auth key
     * @return bool return whether the auth key is valid
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validate password.
     *
     * @param string $password input password
     * @param string $hash password hash from database
     * @return bool return whether the password is valid
     */
    public function validatePassword($password, $hash)
    {
        return Yii::$app->security->validatePassword($password, $hash);
    }

    /**
     * Get user by email.
     *
     * @param string $email user email
     * @return BaseActiveRecord return user object with the email
     */
    public static function getUser($email)
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * Actions before saving a record.
     *
     * @param bool $insert whether the record is inserted
     * @return bool return whether the record is saved
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->isNewRecord) {
            $this['auth_key'] = Yii::$app->security->generateRandomString();
            $this['password'] = Yii::$app->getSecurity()->generatePasswordHash($this['password']);
        }

        return true;
    }

    /**
     * Register user.
     *
     * @return bool|string Return whether the user is registered.
     *                     If the user is registered, return the uuid.
     */
    public function register()
    {
        $uuid = static::gen_uuid();
        $this['uuid'] = $uuid;
        $this['status'] = self::STATUS_ACTIVE;

        if (!$this->save()) {
            return false;
        }

        return $uuid;
    }

    /**
     * Login user.
     *
     * @return array<string, null>|bool|string Return whether the user is logged in.
     *                                         If the user is logged in, return the access token.
     *                                         If the user is not logged in, return the error message.
     */
    public function login()
    {
        $_user = self::getUser($this['email']);

        if (!$_user || !self::validatePassword($this['password'], $_user['password'])) {
            return false;
        }

        $accessToken = self::generateAccessToken();
        $_user['access_token'] = $accessToken;
        if (!$_user->update()) {
            return $_user->getErrors();
        }

        return $accessToken;
    }

    /**
     * Update a user.
     *
     * @param string $uuid
     * @return array<string, mixed>|bool
     */
    public function updateUser($uuid)
    {
        $_user = self::findIdentityByUUID($uuid);
        if (!$_user) {
            return false;
        }

        foreach ($this->dirtyAttributes as $name => $value) {
            // If the name is password, the value will be hashed
            if ('password' == $name) {
                $value = Yii::$app->getSecurity()->generatePasswordHash($value);
            }
            $_user->{$name} = $value;
            $_user->update();
            $data[$name] = $value;
        }

        $_user = self::findIdentityByUUID($uuid);
        $data['updatedAt'] = $_user['updated_at'];

        return $data;
    }

    /**
     * Generate UUID for user.
     *
     * @return string return newly generated UUID
     */
    public static function gen_uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0x0FFF) | 0x4000,
            mt_rand(0, 0x3FFF) | 0x8000,
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF)
        );
    }
}
