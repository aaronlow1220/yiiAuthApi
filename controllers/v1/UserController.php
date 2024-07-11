<?php

namespace app\controllers\v1;

use Yii;
use yii\web\Controller;
use app\models\User;
use yii\filters\auth\HttpBearerAuth;
use yii\web\HttpException;
use app\models\ModifyUserForm;

/**
 * @OA\Tag(
 *      name="Auth",
 *      description="User API"
 * )
 * 
 * Controller for handling user action.
 * 
 * version: 1.0
 */
class UserController extends Controller
{

    /**
     * Behaviors.
     * @return array
     */
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
     * @OA\Put(
     *      path="/v1/user/{uuid}",
     *      summary="Update",
     *      description="Update a user info",
     *      tags={"User"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="User UUID",
     *          required=true,
     *          @OA\Schema(
     *              @OA\Property("uuid", type="string")
     *          )
     *      )
     *      @OA\RequestBody(
     *          description="User info to update",
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property("username", type="string", description="username"),
     *                  @OA\Property("email", type="string", description="email"),
     *                  @OA\Property("password", type="string", description="password"),
     *              )
     *          )
     *      )
     *      @OA\Response(
     *          response=200,
     *          description="User updated successfully",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property("username", type="string", description="username"),
     *                  @OA\Property("email", type="string", description="email"),
     *                  @OA\Property("password", type="string", description="password"),  
     *              )
     *          )
     *      )
     * )
     * 
     * Update a user info.
     * 
     * @throws HttpException If the user is not found or the data is invalid.
     * @return \yii\web\Response Return the updated user data.
     */
    public function actionUpdate()
    {
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
                $value = Yii::$app->getSecurity()->generatePasswordHash($value);
            }
            $user->$name = $value;
            $user->update();
            $data[$name] = $value;
        }

        $user = User::findIdentityByUUID(Yii::$app->request->get("uuid"));

        $data["updatedAt"] = $user->updated_at;

        return $this->asJson($data);
    }

    /**
     * @OA\Get(
     *      path="/v1/user/{uuid}",
     *      summary="Get",
     *      description="Get a user info",
     *      tags={"User"},
     *      @OA\Parameter(
     *          name="uuid",
     *          in="path",
     *          description="User UUID",
     *          required=true,
     *          @OA\Schema(
     *              @OA\Property("uuid", type="string")
     *          )
     *      )
     *      @OA\Response(
     *          response=200,
     *          description="User info",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property("id", type="int", description="id"),
     *                  @OA\Property("uuid", type="string", description="uuid"),
     *                  @OA\Property("username", type="string", description="username"),
     *                  @OA\Property("email", type="string", description="email")
     *              )
     *          )
     *      )
     * )
     * 
     * Get a user info.
     *  
     * @throws HttpException If the user is not found.
     * @return \yii\web\Response Return the user data.
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

        return $this->asJson($user);
    }
}