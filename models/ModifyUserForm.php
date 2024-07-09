<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ModifyUserForm extends Model
{
    public $username;
    public $email;
    public $password;

    public function rules()
    {
        return [
            ['email', 'email'],
        ];
    }
}
