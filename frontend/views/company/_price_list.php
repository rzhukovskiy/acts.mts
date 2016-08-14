<?php

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

use yii\grid\GridView;
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'layout' => '{items}',
    'emptyText' => '',
    'columns' => [
        [
            'header' => '№',
            'class' => 'yii\grid\SerialColumn'
        ],
        [
            'attribute' => 'type_id',
            'value' => function ($data) {
                return $data->getSamePrices();
            },
        ],
        [
            'attribute' => 'service_id',
            'value' => function ($data) {
                return $data->service->description;
            },
        ],
        'price',
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}'
        ],
    ],
]);