<?php

namespace v1\components\user;

use app\components\user\UserRepository;
use yii\data\ActiveDataProvider;

class UserSearchService{

    public function __construct(private UserRepository $userRepository){}

    public function searchUser(array $criteria): ActiveDataProvider
    {
        $query = $this->userRepository->search($criteria);
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
        ]);
    }
}
