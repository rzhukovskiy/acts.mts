<?php
use yii\bootstrap\Tabs;
use common\models\Company;
use common\models\Service;
use common\models\User;

/**
 * @var $this \yii\web\View
 * @var $active string
 * @var $role string
 */

$request = Yii::$app->request;
$items = [];

switch ($role) {
    case User::ROLE_WATCHER:
    case User::ROLE_ADMIN:
        foreach (Service::$listType as $type_id => $typeData) {
            $items[] = [
                'label' => $typeData['ru'],
                'url' => [Yii::$app->controller->action->id, 'type' => $type_id],
                'active' => $request->get('type') == $type_id && !$request->get('company'),
            ];
        }
        break;
}

echo Tabs::widget([
    'items' => $items,
]);