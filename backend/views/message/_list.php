<?php

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\MessageSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

use yii\grid\GridView;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'tableOptions' => ['class' => 'table table-bordered'],
    'layout' => '{items}',
    'emptyText' => '',
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        [
            'header' => 'Кому',
            'value' => function($model) {
                return $model->recipient->username;
            }
        ],
        'topic.topic',
        'text',

        ['class' => 'yii\grid\ActionColumn'],
    ],
]);
