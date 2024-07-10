<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\HttpException;

class LoginForm extends Model
{
    public $email;
    public $password;
    private $_user = false;

    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            ['email', 'email']
        ];
    }
}
