<?php
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $userId  integer
 * @var $allUser  common\models\User[]
 */


$items = [];
foreach ($allUser as $user) {
    if ($user->role == \common\models\User::ROLE_ADMIN) {
        continue;
    }
    $items[] = [
        'label'  => $user->username,
        'url'    => [
            'list',
            'userId' => $user->id
        ],
        'active' => Yii::$app->controller->id == 'plan' && $userId == $user->id,
    ];
}


echo Tabs::widget([
    'items' => $items,
]);