<?php
use common\models\Company;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 * @var $listType array[]
 */

$action = Yii::$app->controller->action->id;
$requestType = Yii::$app->request->get('type');

$items = [];
foreach ($listType as $type_id => $typeData) {
    $items[] = [
        'label' => Company::$listType[$type_id]['ru'],
        'url' => ["/company/$action", 'type' => $type_id],
        'active' => Yii::$app->controller->id == 'company' && $requestType == $type_id,
    ];
}

echo Tabs::widget([
    'items' => $items,
]);