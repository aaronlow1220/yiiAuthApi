<?php
namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use \yii\db\BaseActiveRecord;

/**
 * @OA\Schema(
 *      schema="User",
 *      title="User Model",
 *      description="This model is used to store user data",
 *      required={"email", "password"},
 *      @OA\Property(property="id", type="int", description="Auto increment id #auto increment #primary key", maxLength=11),
 *      @OA\Property(property="uuid", type="string", description="Unique id", maxLength=255),
 *      @OA\Property(property="username", type="string", description="username", maxLength=255),
 *      @OA\Property(property="email", type="string", description="email", maxLength=255),
 *      @OA\Property(property="password", type="string", description="password", maxLength=255),
 *      @OA\Property(property="status", type="int", description="user status 1:Active 0:Inactive", default=0 ,maxLength=4),
 *      @OA\Property(property="auth_key", type="string", description="authentication key", maxLength=255),
 *      @OA\Property(property="access_token", type="string", description="access token", maxLength=255),
 *      @OA\Property(property="created_at", type="string", description="create timestamp", format="date-time"),
 *      @OA\Property(property="updated_at", type="string", description="update timestamp", format="date-time"),
 * )
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * Scenario for register.
     * 
     * @var string
     */
    const SCENARIO_REGISTER = 'register';

    /**
     * Scenario for login.
     * 
     * @var string
     */
    const SCENARIO_LOGIN = 'login';

    /**
     * Status for active account.
     * 
     * @var int
     */
    const STATUS_ACTIVE = 1;

    /**
     * Status for inactive account.
     * 
     * @var int
     */
    const STATUS_INACTIVE = 0;

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
     * @return array<int, mixed> Return behaviors.
     */
    public function behaviors(){
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Rules for validation.
     * 
     * @return array<int, mixed> Return rules.
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
     * @return array<string> Return Scenarios.
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
     * @return string Return Table name
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
     * @return string Return new access token.
     */
    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
        return $this->access_token;
    }

    /**
     * Get user's auto increment id.
     * 
     * @return int Return user id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get user's auth key.
     * 
     * @return string Return auth key.
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Validate auth key.
     * 
     * @param string $authKey Auth key.
     * @return bool Return whether the auth key is valid.
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validate password.
     * 
     * @param string $password Input password.
     * @return bool Return whether the password is valid.
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
     * @return bool Return whether the record is saved.
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
     * @return bool|string Return whether the user is registered.
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
     * @return bool | string | array Return whether the user is logged in. 
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
     * @return string Return newly generated UUID.
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