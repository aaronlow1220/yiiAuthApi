<?php

namespace v1\controllers;

use Yii;
use app\models\User;
use v1\components\user\UserSearchService;
use yii\base\Module;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

/**
 * @OA\Tag(
 *      name="Auth",
 *      description="User API"
 * )
 *
 * Controller for handling user action.
 *
 * version: 1.0.0
 */
class UserController extends Controller
{
    /**
     * Constructor.
     *
     * @param string $id
     * @param Module $module
     * @param UserSearchService $userSearchService
     * @param array<int|string, mixed> $config
     */
    public function __construct($id, $module, private UserSearchService $userSearchService, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    /**
     * Behaviors.
     *
     * @return array<int|string, mixed>
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
     *              @OA\Property(property="uuid", type="string")
     *          )
     *      ),
     *      @OA\RequestBody(
     *          description="User info to update",
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="username", type="string", description="username"),
     *                  @OA\Property(property="email", type="string", description="email"),
     *                  @OA\Property(property="password", type="string", description="password"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User updated successfully",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="username", type="string", description="username"),
     *                  @OA\Property(property="email", type="string", description="email"),
     *                  @OA\Property(property="password", type="string", description="password"),
     *              )
     *          )
     *      )
     * )
     *
     * Update a user info.
     *
     * @throws HttpException if the user is not found or the data is invalid
     * @return array<string>|Response Return the user data. If the data is invalid, return error messages.
     */
    public function actionUpdate()
    {
        $model = new User(['scenario' => User::SCENARIO_UPDATE]);
        $update = null;
        if (!($model->load(Yii::$app->request->post(), '') && $update = $model->updateUser(Yii::$app->request->get('uuid')))) {
            return $model->getFirstErrors();
        }

        return $this->asJson($update);
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
     *              @OA\Property(property="uuid", type="string")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User info",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="id", type="int", description="id"),
     *                  @OA\Property(property="uuid", type="string", description="uuid"),
     *                  @OA\Property(property="username", type="string", description="username"),
     *                  @OA\Property(property="email", type="string", description="email")
     *              )
     *          )
     *      )
     * )
     *
     * Get a user info.
     *
     * @throws HttpException if the user is not found
     * @return Response return the user data
     */
    public function actionUser()
    {
        // Find the user by the uuid
        $user = User::findIdentityByUUID(Yii::$app->request->get('uuid'));

        // If the user is not found, the server will return a 404 status code
        if (null == $user) {
            throw new HttpException(404, 'User not found');
        }
        // Unset the sensitive data
        unset($user->password, $user->access_token, $user->auth_key, $user->status, $user->created_at, $user->updated_at);

        return $this->asJson($user);
    }

    /**
     * @OA\Post(
     *      path="/v1/user/search",
     *      summary="Search",
     *      description="Search for users info",
     *      tags={"User"},
     *      @OA\RequestBody(
     *          description="criteria to search",
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="username", type="string", description="username"),
     *                  @OA\Property(property="email", type="string", description="email"),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User info",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="id", type="int", description="id"),
     *                  @OA\Property(property="uuid", type="string", description="uuid"),
     *                  @OA\Property(property="username", type="string", description="username"),
     *                  @OA\Property(property="email", type="string", description="email"),
     *                  @OA\Property(property="status", type="string", description="status")
     *              )
     *          )
     *      )
     * )
     *
     * Search for users info
     *
     * @return array<string, mixed> return the user data
     */
    public function actionSearch()
    {
        $criteria = Yii::$app->request->bodyParams;

        return $this->userSearchService->searchUser($criteria);
    }
}
