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
    public function actionRegister()
    {
        $model = new User(['scenario' => User::SCENARIO_REGISTER]);
        $register = null;
        if (!($model->load(Yii::$app->request->post(), '') && $register = $model->register())) {
            return $model->getFirstErrors();
        }

        $data = [
            "uuid" => $register,
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
        $login = null;
        if (!($model->load(Yii::$app->request->post(), '') && $login = $model->login())) {
            return $model->getFirstErrors();
        }
        $data = [
            "access_token" => $login,
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