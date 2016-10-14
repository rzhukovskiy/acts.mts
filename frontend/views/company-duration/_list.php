<?php

/* @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $type int
 */

use yii\grid\GridView;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'layout'       => '{items}',
    'emptyText'    => '',
    'tableOptions' => ['class' => 'table table-bordered'],
    'columns'      => [
        [
            'header' => '№',
            'class'  => 'yii\grid\SerialColumn'
        ],
        [
            'attribute' => 'type_id',
            'value'     => function ($data) {
                return $data->type->name;
            },
        ],
        'duration',
    ],
]);