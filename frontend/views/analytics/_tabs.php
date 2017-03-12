<?php
use common\models\Service;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 * @var $type null|integer
 * @var $group string
 */

$request = Yii::$app->request;
$items = [];
foreach (Service::$listType as $type_id => $typeData) {
    if ($type_id == Service::TYPE_DISINFECT) continue;
    $items[] = [
        'label' => $typeData['ru'],
        'url' => ['list', 'type' => $type_id, 'group' => $group],
        'active' => $type == $type_id,
    ];
}

echo Tabs::widget([
    'items' => $items,
]);