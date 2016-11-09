<?php
use yii\bootstrap\Tabs;
use common\models\Company;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$action = \Yii::$app->controller->action->id;
$requestType = Yii::$app->request->get('type');

$items = [];
foreach (Company::$listType as $type_id => $typeData) {
    $items[] = [
        'label' => $typeData['ru'],
        'url' => ['/site/connect', 'type' => $type_id],
        'active' => $action == 'connect' && $requestType == $type_id,
    ];
}

echo Tabs::widget([
    'items' => $items,
]);