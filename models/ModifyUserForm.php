<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ModifyUserForm extends Model
{
    /**
     * @var string $username Username of the user.
     */
    public $username;

    /**
     * @var string $email Email of the user.
     */
    public $email;

    /**
     * @var string $password Password of the user.
     */
    public $password;

    /**
     * Validation rules for the form.
     * @return string[][] Return validation rules.
     */
    public function rules()
    {
        return [
            ['email', 'email'],
        ];
    }
}
