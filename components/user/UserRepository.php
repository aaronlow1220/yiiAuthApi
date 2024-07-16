<?php

namespace app\components\user;
use app\models\User;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class UserRepository{
    protected string $modelClass = User::class;

    public function search(array $criteria):ActiveQuery
    {
        $query = User::find();
        
        if (isset($criteria['username'])) {
            $query->andWhere(['like', 'name', $criteria['username']]);
        }

        if (isset($criteria['email'])) {
            $query->andWhere(['like', 'email', $criteria['email']]);
        }
        
        return $query;
    }
}