<?php
use yii\grid\GridView;

/**
 * @var $this \yii\web\View
 * @var $searchModel \common\models\search\CarSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
]);