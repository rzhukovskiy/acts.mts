<?php
use common\models\Service;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$action = \Yii::$app->controller->action->id;
$requestType = Yii::$app->request->get('type');

$items = [];
$serviceList = [Service::TYPE_WASH, Service::TYPE_SERVICE, Service::TYPE_TIRES];
foreach ($serviceList as $type_id) {
    $items[] = [
        'label' => Service::$listType[$type_id]['ru'],
        'url' => ['/order/list', 'type' => $type_id],
        'active' => $action == 'list' && $requestType == $type_id,
    ];
}

echo Tabs::widget([
    'items' => $items,
]);