<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $group string
 */

use common\models\Act;
use common\models\Mark;
use common\models\Type;
use kartik\grid\GridView;
use yii\helpers\Html;

$columns = [
    [
        'header' => '№',
        'class' => 'kartik\grid\SerialColumn',
        'contentOptions' => ['style' => 'max-width: 40px'],
    ],
    [
        'attribute' => 'day',
        'filter' => Act::getDayList(),
        'value' => function ($data) {
            return date('d-m-Y', $data->served_at);
        },
        'visible' => $group != 'count',
    ],
    [
        'attribute' => 'mark_id',
        'filter' => Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
        'value' => function ($data) {
            return isset($data->mark) ? $data->mark->name : 'error';
        },
    ],
    [
        'attribute' => 'number',
    ],
    [
        'attribute' => 'type_id',
        'filter' => Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
        'value' => function ($data) {
            return isset($data->type) ? $data->type->name : 'error';
        },
    ],
    [
        'header' => 'Период обслуживаний',
        'value' => function ($data) {
            // Вывод среднего времени обслуживания
            return \frontend\controllers\AnalyticsController::GetSrTime($data->car->number, $data->service_type);
        },
        'visible' => $group == 'count',
    ],
    [
        'header' => '',
        'mergeHeader' => false,
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view}',
        'width' => '40px',
        'buttons' => [
            'view' => function ($url, $data, $key) use ($group, $searchModel) {
                return Html::a('<span class="glyphicon glyphicon-search"></span>', [
                    'detail',
                    'ActSearch[dateFrom]' => $searchModel->dateFrom,
                    'ActSearch[dateTo]' => $searchModel->dateTo,
                    'ActSearch[number]' => $data->number,
                    'ActSearch[service_type]' => $searchModel->service_type,
                ]);
            },
        ],
        'visible' => $group == 'count' && $count != 0,
    ],
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => false,
    'emptyText' => '',
    'floatHeader' => true,
    'floatHeaderOptions' => ['top' => '0'],
    'panel' => [
        'type' => 'primary',
        'heading' => 'Обслуженные машины',
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'hover' => false,
    'striped' => false,
    'export' => false,
    'columns' => $columns,
]);