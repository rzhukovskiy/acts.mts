<?php
use yii\bootstrap\Tabs;
use common\models\Service;
use yii\helpers\ArrayHelper;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$items = [];

$items[] = [
    'label' => 'Управление услугами',
    'url' => ['index', 'ServiceSearch[type]' => 2],
    'active' => Yii::$app->controller->action->id != 'replace',
];

foreach (Service::$listType as $type_id => $typeData) {
    $items[] = [
        'label' => $typeData['ru'],
        'url' => ['replace', 'type' => $type_id],
        'active' => $type == $type_id,
    ];
}

echo Tabs::widget([
    'items' => $items,
]);