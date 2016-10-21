<?php

use yii\bootstrap\Html;
use kartik\grid\GridView;

/**
 * @var $this \yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 */


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'export' => false,
    'floatHeader' => true,
    'floatHeaderOptions' => ['top' => '0'],
    'hover' => false,
    'striped' => false,
    'summary' => false,
    'emptyText' => $this->params['emptyText'],
    'panel' => [
        'type' => 'primary',
        'heading' => 'Загружено',
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'columns' => [
        [
            'header' => '№',
            'class' => 'yii\grid\SerialColumn'
        ],
        [
            'attribute' => 'company_id',
            'content' => function ($data) {
                return $data->company->name;
            },
            'group' => true,
            'groupedRow' => true,
            'groupOddCssClass' => 'kv-group-header',
            'groupEvenCssClass' => 'kv-group-header',
        ],
        [
            'attribute' => 'mark_id',
            'content' => function ($data) {
                return !empty($data->mark->name) ? Html::encode($data->mark->name) : '';
            },
        ],
        'number',
        [
            'attribute' => 'type_id',
            'content' => function ($data) {
                return !empty($data->type->name) ? Html::encode($data->type->name) : '';
            },
        ],
    ],
]);