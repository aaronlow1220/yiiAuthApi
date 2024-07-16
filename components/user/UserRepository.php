<?php

namespace app\components\user;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\User;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

class UserRepository extends AbstractRepository{
    protected string $modelClass = User::class;

    public function search(array $criteria):ActiveQuery
    {
        $query = $this->find();
        
        if (isset($criteria['username'])) {
            $query->andWhere(['like', 'username', $criteria['username']]);
        }

        if (isset($criteria['email'])) {
            $query->andWhere(['like', 'email', $criteria['email']]);
        }
        
        return $query;
    }
}