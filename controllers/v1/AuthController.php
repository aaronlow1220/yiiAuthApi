<?php

namespace app\controllers\v1;

use Yii;
use yii\web\Controller;
use app\models\users;
use yii\filters\auth\HttpBearerAuth;
use app\models\CLoginForm;
use app\models\CRegisterForm;
use app\models\CModifyUserForm;
use yii\web\HttpException;

class AuthController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['index', 'user', 'logout', 'update-user'],
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
        $model = new CRegisterForm();

        $model->attributes = Yii::$app->request->post();
        // When user submits the form, the data will be validated

        // If the data is not valid, the server will return a 400 status code
        if (!$model->validate()) {
            throw new HttpException(400, "Invalid data provided");
        }

        // If the user already exists, the server will return a 409 status code
        if (users::accountExist($model->email)) {
            throw new HttpException(409, "User already exists");
        }

        $userModel = new users();
        $userModel->username = $model->username;
        $userModel->email = $model->email;
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
        $model = new CLoginForm();

        $model->attributes = Yii::$app->request->post();

        // When user submits the form, the data will be validated

        // If the data is not valid, return a 400 status code
        if (!$model->validate()) {
            throw new HttpException(400, "Invalid data provided");
        }

        $loggedUser = users::find()->where(["email" => $model->email])->one();

        // If the user is not found, return a 400 status code
        if ($loggedUser == null) {
            throw new HttpException(400, "User not found");
        }

        // If the password is incorrect, return a 400 status code
        if (!Yii::$app->getSecurity()->validatePassword($model->password, $loggedUser->password)) {
            throw new HttpException(400, "Login failed, please try again");
        }

        // Generate a new access token
        $newToken = users::generateAccessToken();
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
        $loggedUser = users::find()->where(["access_token" => Yii::$app->request->post('access_token')])->one();

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

    /**
     * @api {put} /v1/user/ Update User
     * 
     * Update a user info
     * 
     */
    public function actionUpdateUser()
    {
        // Get the access token from the header
        $auth = $this->GetHeaderToken();

        // If the access token is not found, the server will return a 401 status code
        if ($auth == null) {
            throw new HttpException(401, "Unauthorized");
        }

        // Find the user by the access token
        $user = users::findIdentityByAccessToken($auth);

        // If the user is not found, the server will return a 404 status code
        if ($user == null) {
            throw new HttpException(404, "User not found");
        }

        $model = new CModifyUserForm();
        $params = Yii::$app->request->getBodyParams();
        $model->attributes = $params;

        // When user submits the form, the data will be validated

        // If the data is not valid, the server will return a 400 status code
        if (!$model->validate()) {
            throw new HttpException(400, "Invalid data provided");
        }

        // Update the user data
        foreach ($params as $name => $value) {
            // If the name is password, the value will be hashed
            if ($name == 'password') {
                $value = password_hash($value, PASSWORD_DEFAULT);
            }
            $user->$name = $value;
            $user->update();
            $data[$name] = $value;
        }

        $user = users::findIdentityByAccessToken($auth);

        $data["updatedAt"] = $user->updated_at;

        return $this->asJson($data);
    }

    /**
     * @api {get} /v1/user/:uuid Get User
     * 
     * Get a user info
     * 
     */
    public function actionUser()
    {
        // Find the user by the uuid
        $user = users::find()->where(["uuid" => Yii::$app->request->get('uuid')])->one();

        // If the user is not found, the server will return a 404 status code
        if ($user == null) {
            throw new HttpException(404, "User not found");
        }
        // Unset the sensitive data
        unset($user->password, $user->access_token, $user->auth_key, $user->status, $user->created_at, $user->updated_at);

        $data = [
            "data" => $user,
        ];

        return $this->asJson($data);
    }

    /** 
     * 
     * Generate UUID for user
     * 
     * @return string
     * 
     */
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