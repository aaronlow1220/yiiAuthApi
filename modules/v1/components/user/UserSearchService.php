<?php

namespace v1\components\user;

use app\components\user\UserRepository;
use yii\data\ActiveDataProvider;

class UserSearchService{

    public function __construct(private UserRepository $userRepository){}

    public function searchUser(array $criteria): array
    {
        $query = $this->userRepository->search($criteria);

        $dataProvider = new ActiveDataProvider([
            'query' => &$query,
            'pagination' => [
                'class' => 'v1\components\Pagination',
                'params' => $criteria,
            ],
            'sort' => [
                'enableMultiSort' => true,
                'params' => $criteria,
            ],
        ]);

        return $dataProvider->getModels();
    }
}
