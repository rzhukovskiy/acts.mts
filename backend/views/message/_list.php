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

        'id',
        'text',
        'recipient.username',
        'topic.topic',

        ['class' => 'yii\grid\ActionColumn'],
    ],
]);
