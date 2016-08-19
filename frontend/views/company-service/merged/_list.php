<?php

/* @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $type int
 */

use yii\grid\GridView;
use common\models\Service;

$columns = [
    [
        'header' => '№',
        'class' => 'yii\grid\SerialColumn'
    ],
    [
        'attribute' => 'type_id',
        'value' => function ($data) {
            return $data->type->name;
        },
    ],
];

foreach (Service::findAll(['type' => $type]) as $service) {
    $columns[] = [
        'header' => $service->description,
        'value' => function ($data) use($service) {
            return $data->getPriceForService($service->id);
        },
    ];
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}',
    'emptyText' => '',
    'tableOptions' => ['class' => 'table table-bordered'],
    'columns' => $columns,
]);