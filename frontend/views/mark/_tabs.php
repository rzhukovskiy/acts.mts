<?php
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 */

$items = [
    [
        'label' => 'Марки',
        'url' => '/mark/list',
        'active' => \Yii::$app->controller->id == 'mark',
    ],
    [
        'label' => 'Типы',
        'url' => '/type/list',
        'active' => \Yii::$app->controller->id == 'type',
    ],
];

echo Tabs::widget([
    'items' => $items,
]);