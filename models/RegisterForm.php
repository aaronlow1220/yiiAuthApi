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
}
