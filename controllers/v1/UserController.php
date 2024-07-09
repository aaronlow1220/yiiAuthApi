<?php

namespace app\controllers\v1;

use Yii;
use yii\web\Controller;
use app\models\User;
use yii\filters\auth\HttpBearerAuth;
use yii\web\HttpException;
use app\models\ModifyUserForm;

class UserController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::className(),
            'only' => ['update', 'user'],
        ];

        return $behaviors;
    }

    /**
     * @api {put} /v1/user/<uuid> Update User
     * 
     * Update a user info
     * 
     */
    public function actionUpdate()
    {
        // Get the access token from the header
        $auth = $this->GetHeaderToken();

        // If the access token is not found, the server will return a 401 status code
        if ($auth == null) {
            throw new HttpException(401, "Unauthorized");
        }

        // Find the user by UUID
        $user = User::findIdentityByUUID(Yii::$app->request->get("uuid"));

        // If the user is not found, the server will return a 404 status code
        if ($user == null) {
            throw new HttpException(404, "User not found");
        }

        $model = new ModifyUserForm();
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

        $user = User::findIdentityByAccessToken($auth);

        $data["updatedAt"] = $user->updated_at;

        return $this->asJson($data);
    }

    /**
     * @api {get} /v1/user/<uuid>:uuid Get User
     * 
     * Get a user info
     * 
     */
    public function actionUser()
    {
        // Find the user by the uuid
        $user = User::findIdentityByUUID(Yii::$app->request->get("uuid"));

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