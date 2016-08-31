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
                return $data->client->name;
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
        [
            'attribute' => 'actsCount',
            'content' => function ($data) {
                return $data->actsCount;
            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url, $data, $key) {
                    if (!is_null($data->car)) // появился акт для машины призрака
                        return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'id' => $data->car->id]);
                    return Html::tag('span', 'Нет машины', ['class' => 'label label-danger']);
                },
            ],
        ],
    ],
]);