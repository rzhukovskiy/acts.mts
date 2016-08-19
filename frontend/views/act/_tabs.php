<?php
use yii\bootstrap\Tabs;
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
    case User::ROLE_ADMIN:
        foreach (Service::$listType as $type_id => $typeData) {
            $items[] = [
                'label' => $typeData['ru'],
                'url' => ['/act/list', 'type' => $type_id],
                'active' => $request->get('type') == $type_id && !$request->get('company'),
            ];
            $items[] = [
                'label' => 'Для компании',
                'url' => ['/act/list', 'type' => $type_id, 'company' => true],
                'active' => $request->get('type') == $type_id && $request->get('company'),
            ];
        }
        break;

    case User::ROLE_CLIENT:
        foreach (Service::$listType as $type_id => $typeData) {
            $items[] = [
                'label' => $typeData['ru'],
                'url' => ['/act/list', 'type' => $type_id, 'company' => true],
                'active' => $request->get('type') == $type_id && $request->get('company'),
            ];
        }
        break;
}

echo Tabs::widget([
    'items' => $items,
]);