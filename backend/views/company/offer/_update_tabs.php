<?php
use common\models\Company;
use yii\bootstrap\Tabs;

/**
 * @var $this \yii\web\View
 * @var $active string
 * @var $model Company
 */

$items = [
    [
        'label' => 'Мойка',
        'url'   => ['company/offer', 'ad' => Yii::$app->request->get('ad'), 'type' => 2],
        'active' => $model->type == 2,
    ],
    [
        'label'  => 'Сервис',
        'url'    => ['company/offer', 'ad' => Yii::$app->request->get('ad'), 'type' => 3],
        'active' => $model->type == 3,
    ],
    [
        'label'  => 'Шиномонтаж',
        'url'    => ['company/offer', 'ad' => Yii::$app->request->get('ad'), 'type' => 4],
        'active' => $model->type == 4,
    ],
    /*[
        'label'  => 'Дезинфекция',
        'url'    => ['company/offer', 'ad' => Yii::$app->request->get('ad'), 'type' => 5],
        'active' => $model->type == 5,
    ],*/
    /*[
        'label'  => 'Универсальная',
        'url'    => ['company/offer', 'ad' => Yii::$app->request->get('ad'), 'type' => 6],
        'active' => $model->type == 6,
    ]*/
];

echo Tabs::widget([
    'items' => $items,
]);