<?php
use common\models\Service;
use common\models\User;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $model common\models\Company
 * @var $active string
 */

if (Yii::$app->user->can(User::ROLE_ADMIN) || Yii::$app->user->can(User::ROLE_CLIENT)) {
    foreach (Service::$listType as $type_id => $typeData) {
        $items[] = [
            'label'  => $typeData['ru'],
            'url'    => ['list', 'type' => $type_id],
            'active' => $type == $type_id && !Yii::$app->request->get('company'),
        ];
    }
} elseif (Yii::$app->user->can(User::ROLE_PARTNER)) {
    $company = Yii::$app->user->identity->company;
    $type = $company->type == \common\models\Company::TYPE_UNIVERSAL ? $company->serviceTypes[0]->type : $company->type;
    $items[] = [
        'label'  => Service::$listType[$type]['ru'],
        'url'    => ['list', 'type' => $type],
        'active' => $type == $type && !Yii::$app->request->get('company'),
    ];
}

echo Tabs::widget([
    'items' => $items,
]);