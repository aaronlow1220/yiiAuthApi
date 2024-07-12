<?php

namespace app\controllers\v1;

use Yii;
use yii\web\Controller;
use yii\web\Response;
/**
 * @OA\Info(
 *         version="1.0.0",
 *         title="APIv1",
 *         description="APIs document of v1 that based on Swagger OpenAPI",
 *     ),
 */
class OpenApiController extends Controller{
    public function actionIndex(){
        $controllersPath = Yii::getAlias('@app/controllers/v1/');
        $modelPath = Yii::getAlias('@app/models/');
        $openapi = \OpenApi\Generator::scan([$controllersPath, $modelPath]);

        $content = $openapi->toJson();
        $this->response->format = Response::FORMAT_JSON;

        $this->response->content = $content;
        return $this->response;
    }
}