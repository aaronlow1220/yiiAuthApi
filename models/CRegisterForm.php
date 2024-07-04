<?php

namespace app\models;

use Yii;
use yii\base\Model;

class CRegisterForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $confirmPassword;

    public function rules()
    {
        return [
            [['username', 'email', 'password', 'confirmPassword'], 'required'],
            ['email', 'email'],
            ['confirmPassword', 'compare', 'compareAttribute' => 'password'],
        ];
    }
}
