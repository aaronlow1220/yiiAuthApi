<?php

namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $email;
    public $password;
    private $_user;

    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email']
        ];
    }

    public function login()
    {
        if (!$this->validate()) {
            return false;
        }

        $this->_user = User::getUser($this->email);

        if (!$this->_user || !$this->_user->validatePassword($this->password)) {
            return false;
        }

        $access_token = $this->_user->generateAccessToken();
        $this->_user->access_token = $access_token;
        $this->_user->update();
        return $access_token;
    }
}
