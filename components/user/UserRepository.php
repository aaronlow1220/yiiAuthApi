<?php

namespace app\components\user;

use AtelliTech\Yii2\Utils\AbstractRepository;
use app\models\User;
use yii\db\ActiveQuery;

/**
 * Repository for accessing User model.
 */
class UserRepository extends AbstractRepository
{
    /**
     * @var string model class name
     */
    protected string $modelClass = User::class;

    /**
     * Search for users with username and email.
     *
     * @param array<string> $criteria
     * @return ActiveQuery
     */
    public function search(array $criteria): ActiveQuery
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
