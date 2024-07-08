<?php

namespace app\models;

use Yii;
use yii\base\Model;

class CModifyUserForm extends Model
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
