<?php

namespace app\controllers\v1;

use Yii;
use yii\web\Controller;
use app\models\User;
use yii\filters\auth\HttpBearerAuth;
use yii\web\HttpException;

/**
 * Controller for handling authentication
 * 
 * @author aaronlow <aaron.low@atelli.ai>
 * version: 1.0
 */
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
     * Register a user
     * 
     * @return array | \yii\web\Response
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
     * Login a user
     * 
     * @return array | \yii\web\Response
     */
    public function actionLogin()
    {
        $model = new User(['scenario' => User::SCENARIO_LOGIN]);
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
     * Logout a user
     * 
     * @throws HttpException
     * 
     * @return yii\web\Response
     */
    public function actionLogout()
    {
        $loggedUser = User::findIdentityByAccessToken($this->GetHeaderToken());

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
     * Get access token from authorization header
     * 
     * @return string
     */
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