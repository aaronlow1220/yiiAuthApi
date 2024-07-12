<?php

namespace app\controllers\v1;

use Yii;
use yii\web\Controller;
use app\models\User;
use yii\filters\auth\HttpBearerAuth;
use yii\web\HttpException;
use yii\web\Response;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *      name="Auth",
 *      description="Authentication API"
 * )
 * 
 * Controller for handling authentication.
 * 
 * version: 1.0.0
 */
class AuthController extends Controller
{

    /**
     * Behaviors for the controller.
     * 
     * @return array Return behaviors.
     */
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
     * @OA\Post(
     *      path="/v1/auth/register",
     *      summary="Register a user",
     *      description="Register a user",
     *      tags={"Auth"},
     *      @OA\RequestBody(
     *          description="User credentials",
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="username", type="string", description="username"),
     *                  @OA\Property(property="email", type="string", description="email"),
     *                  @OA\Property(property="password", type="string", description="password"),
     *                  @OA\Property(property="conformPassword", type="string", description="confirm password")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User registered successfully",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="uuid", type="string", description="uuid"),
     * 
     *              )
     *          )
     *      )
     * )
     * 
     * Register a user.
     * 
     * @return array | Response Return the user data. If the data is invalid, return error messages.
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
     * Login a user.
     * 
     * @OA\Post(
     *      path="/v1/auth/login",
     *      summary="login a user",
     *      description="login a user",
     *      operationId="login",
     *      tags={"Auth"},
     *      @OA\RequestBody(
     *          description="User credentials",
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="email", type="string", description="email"),
     *                  @OA\Property(property="password", type="string", description="password")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User logged in successfully",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *                  @OA\Property(property="access_token", type="string", description="access token"),
     *          )
     *      )
     * )
     * 
     * @return array | Response Return the access token. If the data is invalid, return error messages.
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
     * @OA\Post(
     *      path="/v1/auth/logout",
     *      summary="logout a user",
     *      description="logout a user",
     *      tags={"Auth"},
     *      @OA\Response(
     *          response=200,
     *          description="User logged out successfully",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *                  @OA\Property(property="message", type="string", description="logout message"),
     *          )
     *      )
     * )
     * 
     * Logout a user.
     * 
     * @throws HttpException If the user is not found.
     * 
     * @return Response Return a message. 
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
     * Get access token from authorization header.
     * 
     * @return string | null Return the access token. If the access token is not found, return null.
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