<?php
namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class users extends ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    public static function tableName()
    {
        return 'users';
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->password;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function generateAccessToken()
    {
        $this->access_token = Yii::$app->security->generateRandomString();
        return $this->access_token;
    }

    public function login()
{
    if ($this->validate()) {
        $user = users::findOne(['email' => $this->email]);

        if ($user && Yii::$app->getSecurity()->validatePassword($this->password, $user->password_hash)) {
            return $user;
        }
    }

    return null;
}
}