<?php
use yii\bootstrap\Tabs;
use common\models\Company;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$action = Yii::$app->controller->action->id;
$requestType = Yii::$app->request->get('type');

$items = [];
foreach (Company::$listType as $type_id => $typeData) {
    $items[] = [
        'label' => $typeData['ru'],
        'url' => ["/company/$action", 'type' => $type_id],
        'active' => Yii::$app->controller->id == 'company' && $requestType == $type_id,
    ];
}

echo Tabs::widget([
    'items' => $items,
]);