<?php

namespace v1\components\user;

use app\components\user\UserRepository;
use yii\data\ActiveDataProvider;

/**
 * Service for searching users.
 */
class UserSearchService
{
    /**
     * Constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(private UserRepository $userRepository) {}

    /**
     * Search for users with username and email.
     *
     * @param array<string> $criteria
     * @return array<string, mixed>
     */
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
