<?php

namespace app\controllers\v1;

use Yii;
use yii\web\Controller;
use app\models\User;
use yii\filters\auth\HttpBearerAuth;
use app\models\LoginForm;
use app\models\RegisterForm;
use yii\web\HttpException;

class AuthController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['logout'],
        ];

        return $behaviors;
    }

    /**
     * @api {post} /v1/auth/register Register
     * 
     * Register a new user
     * 
     */

    public function actionCreate()
    {
        $model = new RegisterForm();

        $model->attributes = Yii::$app->request->post();
        // When user submits the form, the data will be validated

        // If the data is not valid, the server will return a 400 status code
        if (!$model->validate()) {
            throw new HttpException(400, "Invalid data provided");
        }

        // If the user already exists, the server will return a 409 status code
        if (User::getUser($model->email)) {
            throw new HttpException(409, "User already exists");
        }

        $userModel = new User();
        $userModel->load(["User" => Yii::$app->request->post()]);
        $userModel->password = Yii::$app->getSecurity()->generatePasswordHash($model->password);

        // If the user is not successfully registered, return a 400 status code
        if (!$userModel->save()) {
            throw new HttpException(400, "Failed to register user");
        }

        // If the user is successfully registered, return the user id
        $data = [
            "id" => $userModel->uuid,
        ];

        return $this->asJson($data);
    }

    /**
     * @api {post} /v1/auth/login Login
     * 
     * Login a user
     * 
     */

    public function actionLogin()
    {
        $model = new LoginForm();

        $model->attributes = Yii::$app->request->post();
        // When user submits the form, the data will be validated

        // If the data is not valid, return a 400 status code
        if (!$model->validate()) {
            throw new HttpException(400, "Invalid data provided");
        }

        $loggedUser = User::getUser($model->email);

        // If the user is not found, return a 400 status code
        if (!$loggedUser) {
            throw new HttpException(400, "User not found");
        }

        // If the password is incorrect, return a 400 status code
        if (!Yii::$app->getSecurity()->validatePassword($model->password, $loggedUser->password)) {
            throw new HttpException(400, "Login failed, please try again");
        }

        // Generate a new access token
        $newToken = User::generateAccessToken();
        $loggedUser->access_token = $newToken;
        $loggedUser->update();

        $data = [
            "token" => $newToken,
        ];

        return $this->asJson($data);
    }

    /**
     * @api {post} /v1/auth/logout Logout
     * 
     * Logout a user
     * 
     */
    public function actionLogout()
    {
        $loggedUser = User::find()->where(["access_token" => Yii::$app->request->post('access_token')])->one();

        // If the user is found, the server will return a 404 status code
        if ($loggedUser == null) {
            throw new HttpException(404, "User not found");
        }

        // Set the access token to null
        $loggedUser->access_token = null;
        $loggedUser->update();

        $data = [
            "message" => "Logout successful",
        ];

        return $this->asJson($data);

    }

    function GetHeaderToken(): string
    {
        $header = Yii::$app->request->headers->get('Authorization');
        if ($header == null) {
            return null;
        }
        $pattern = '/Bearer\s(\S+)/';
        preg_match($pattern, $header, $matches);
        return $matches[1];
    }
}