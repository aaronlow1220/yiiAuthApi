<?php

namespace app\controllers\v1;

use app\models\CRegisterForm;
use Yii;
use yii\web\Controller;
use app\models\users;

class AuthController extends Controller
{
    public $enableCsrfValidation = false;
    public function actionIndex()
    {
        $loggedInUser = Yii::$app->user->identity;
        return $this->render('index', ["loggedInUser" => $loggedInUser]);
    }

    public function actionRegister()
    {
        $model = new CRegisterForm();

        $model->attributes = Yii::$app->request->post('credentials');

        if (!$model->validate()) {
            $data = [
                "success" => false,
                "message" => "Invalid data provided",
            ];
            return $this->asJson($data);
        }

        $query = users::find();
        $user = $query->where(['email' => $model->email])->one();

        if ($user != null) {
            $data = [
                "success" => false,
                "message" => "User already exists",
            ];
            return $this->asJson($data);
        }

        $userModel = new users();
        $userModel->uuid = $this->gen_uuid();
        $userModel->username = $model->username;
        $userModel->email = $model->email;
        $userModel->password = password_hash($model->password, PASSWORD_DEFAULT);
        $userModel->status = users::STATUS_ACTIVE;

        if ($userModel->save()) {
            $data = [
                "success" => true,
                "message" => "User registered successfully",
            ];
            return $this->asJson($data);
        }

        $data = [
            "success" => false,
            "message" => "Register failed, please try again",
        ];
        return $this->asJson($data);
    }

    public function actionUser()
    {
        $data = [
            "email" => Yii::$app->request->get('id'),
        ];
        return $this->asJson($data);
    }

    function gen_uuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}