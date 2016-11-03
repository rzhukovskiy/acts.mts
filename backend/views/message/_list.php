<?php

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\MessageSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

use yii\grid\GridView;
use yii\helpers\Html;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'tableOptions' => ['class' => 'table table-bordered'],
    'layout' => '{items}',
    'emptyText' => '',
    'columns' => [
        [
            'header' => 'Кому',
            'contentOptions' => ['style' => 'width: 80px'],
            'value' => function($model) {
                return $model->recipient->username;
            }
        ],
        [
            'attribute' => 'topic.topic',
            'contentOptions' => ['style' => 'width: 80px'],
        ],
        [
            'attribute' => 'text',
            'contentOptions' => function ($model, $index, $widget, $grid){
                return ['style' => $model->is_read ? '' : 'font-weight: bold'];
            },
        ],

        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'contentOptions' => ['style' => 'width: 40px'],
            'buttons' => [
                'view' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'id' => $model->id]);
                }
            ]
        ],
    ],
]);
