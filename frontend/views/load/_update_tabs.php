<?php
use common\models\Company;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 * @var $model Company
 */

$items = [
//    [
//        'label'  => 'Закрыть загрузки',
//        'url'    => ['load/list', 'type' => 2],
//        'active' => \Yii::$app->controller->action->id == 'attribute',
//    ],
    [
        'label'  => 'Сотрудники',
        'url'    => ['load/contact', 'id' => $model->id],
        'active' => \Yii::$app->controller->action->id == 'contact',
    ],
];

echo Tabs::widget([
    'items' => $items,
]);