<?php

namespace app\controllers\v1;

use Yii;
use yii\web\Controller;
use app\models\users;

class AuthController extends Controller
{
    public $enableCsrfValidation = false;
    public function actionIndex()
    {
        $loggedInUser = Yii::$app->user->identity;
        return $this->render('index', ["loggedInUser" => $loggedInUser]);
    }

    public function actionRegister(){
        $data = [
            "email" => "je"
        ];
        return $this->asJson($data);
    }

    public function actionUser(){
        $data = [
            "email" => Yii::$app->request->get('id'),
        ];
        return $this->asJson($data);
    }
}