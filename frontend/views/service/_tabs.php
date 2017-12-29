<?php
use yii\bootstrap\Tabs;
use common\models\Service;
use yii\helpers\ArrayHelper;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$items = [];
foreach (Service::$listType as $type_id => $typeData) {
    $items[] = [
        'label' => $typeData['ru'],
        'url' => ['index', 'ServiceSearch[type]' => $type_id],
        'active' => ArrayHelper::getValue(Yii::$app->request->get(), 'ServiceSearch.type') == $type_id,
    ];
}

$items[] = [
    'label' => 'Замещение услуг',
    'url' => ['replace', 'type' => 2],
    'active' => Yii::$app->controller->action->id == 'replace',
];

echo Tabs::widget([
    'items' => $items,
]);