<?php

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\TopicSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

use yii\grid\GridView;

GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],

        'id',
        'text:ntext',
        'user_id',
        'topic_id',

        ['class' => 'yii\grid\ActionColumn'],
    ],
]);
