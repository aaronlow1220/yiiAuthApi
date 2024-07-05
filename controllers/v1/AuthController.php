<?php

namespace app\controllers\v1;

use Yii;
use yii\web\Controller;
use app\models\users;
use yii\filters\auth\HttpBearerAuth;
use app\models\CLoginForm;
use app\models\CRegisterForm;

class AuthController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['index', 'user'],
        ];
        return $behaviors;
    }

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
                "error" => "Invalid data provided",
            ];
            return $this->asJson($data);
        }

        $query = users::find();
        $user = $query->where(['email' => $model->email])->one();

        if ($user != null) {
            $data = [
                "error" => "User already exists",
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
                "id" => $userModel->uuid,
            ];
            return $this->asJson($data);
        }

        $data = [
            "error" => "Register failed, please try again",
        ];
        return $this->asJson($data);
    }

    public function actionLogin()
    {
        $model = new CLoginForm();

        $model->attributes = Yii::$app->request->post('credentials');

        if (!$model->validate()) {
            $data = [
                "success" => false,
                "message" => "Invalid data provided",
            ];
            return $this->asJson($data);
        }

        if ($model->validate()) {
            $loggedUser = users::find()->where(["email" => $model->email])->one();
            if ($loggedUser == null) {
                $data = [
                    "success" => false,
                    "message" => "User not found",
                ];
                return $this->asJson($data);
            }
            if (password_verify($model->password, $loggedUser->password)) {
                $newToken = users::generateAccessToken();
                $loggedUser->access_token = $newToken;
                $loggedUser->update();
                $data = [
                    "success" => true,
                    "message" => "Login successful",
                    "token" => $newToken,
                ];

                return $this->asJson($data);
            }
        }
        Yii::$app->response->statusCode = 400;

        $data = [
            "success" => false,
            "message" => "Login failed, please try again",
        ];
        return $this->asJson($data);
    }

    public function actionLogout()
    {

        $loggedUser = users::find()->where(["access_token" => Yii::$app->request->post('access_token')])->one();
        if ($loggedUser != null) {
            $loggedUser->access_token = null;
            $loggedUser->update();
            $data = [
                "success" => true,
                "message" => "Logout successful",
            ];
            return $this->asJson($data);

        }
        $data = [
            "success" => false,
            "message" => "User not found",
        ];
        return $this->asJson($data);
    }

    public function actionUpdateUser()
    {
        $pattern = '/Bearer\s(\S+)/';
        $getHeaders = Yii::$app->request->headers->get('Authorization');
        preg_match($pattern, $getHeaders, $matches);
        $data = [
            "username" => $matches[1],
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