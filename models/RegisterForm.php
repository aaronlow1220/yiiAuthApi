<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\VarDumper;

class RegisterForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $confirmPassword;
    private $_user = false;


    public function rules()
    {
        return [
            [['username', 'email', 'password', 'confirmPassword'], 'required'],
            ['email', 'email'],
            ['confirmPassword', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    public function register()
    {
        if (!$this->validate()) {
            return false;
        }

        if (User::getUser($this->email)) {
            return false;
        }

        $uuid = static::gen_uuid();
        $userModel = new User();
        $userModel->load(Yii::$app->request->post(), '');
        $userModel->uuid = $uuid;
        $userModel->status = User::STATUS_ACTIVE;
        $userModel->auth_key = Yii::$app->security->generateRandomString();
        $userModel->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);

        if (!$userModel->save()) {
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
