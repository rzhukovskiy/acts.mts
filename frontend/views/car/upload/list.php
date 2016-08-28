<?php

use kartik\grid\GridView;

/**
 * @var $this \yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

echo GridView::widget([
    'dataProvider' => $dataProvider,
]);