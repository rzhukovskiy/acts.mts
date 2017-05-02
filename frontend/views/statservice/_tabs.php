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
        'url' => ['company', 'type' => $type_id],
        'active' => ArrayHelper::getValue(Yii::$app->request->get(), 'type') == $type_id,
    ];
}

echo Tabs::widget([
    'items' => $items,
]);