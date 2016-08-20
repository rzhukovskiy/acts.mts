<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 */

use common\models\Act;
use common\models\Card;
use common\models\Mark;
use common\models\Service;
use common\models\Type;
use kartik\grid\GridView;
use yii\helpers\Html;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'summary' => false,
    'emptyText' => '',
    'floatHeader' => true,
    'floatHeaderOptions' => ['top' => '0'],
    'panel' => [
        'type' => 'primary',
        'heading' => 'Список актов',
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'hover' => false,
    'striped' => false,
    'export' => false,
    'showPageSummary' => true,
    'beforeHeader' => [
        [
            'columns' => [
                [
                    'content' => 'Период:',
                    'options' => ['style' => 'vertical-align: middle'],
                ],
                [
                    'content' => Html::activeDropDownList($searchModel, 'period', Act::getPeriodList(),['class' => 'form-control']),
                    'options' => ['colspan' => 3, 'class' => 'kv-grid-group-filter'],
                ],'','',
                [
                    'content' => Html::a('Пересчитать', '#', ['class' => 'btn btn-primary btn-sm']),
                ],
                [
                    'content' => Html::a('Выгрузить', '#', ['class' => 'btn btn-primary btn-sm']),
                    'options' => ['colspan' => 4],
                ],
            ],
            'options' => ['class' => 'filters', 'id' => 'w1-filters'],
        ],
    ],
    'columns' => [
        [
            'header' => '№',
            'class' => 'kartik\grid\SerialColumn',
            'pageSummary' => 'Всего',
        ],
        [
            'attribute' => 'parent_id',
            'value' => function ($data) {
                return isset($data->partner->parent) ? $data->partner->parent->name : 'без филиалов';
            },
            'group' => true,
            'groupedRow' => true,
            'groupOddCssClass' => function ($data, $key, $index, $widget) {
                return isset($data->partner->parent) ? 'parent' : 'hidden';
            },
            'groupEvenCssClass' => function ($data, $key, $index, $widget) {
                return isset($data->partner->parent) ? 'parent' : 'hidden';
            },
            'groupFooter' => function ($data, $key, $index, $widget) {
                return [
                    'mergeColumns'=>[[0,10]],
                    'content' => [
                        0 => 'Итого по ' . (isset($data->partner->parent) ? $data->partner->parent->name : 'без филиалов'),
                        11 => GridView::F_COUNT,
                    ],
                    'options' => [
                        'class' => isset($data->partner->parent) ? '' : 'hidden',
                        'style' => 'font-weight:bold;'
                    ]
                ];
            }
        ],
        [
            'attribute' => 'partner_id',
            'value' => function ($data) {
                return isset($data->partner) ? $data->partner->name . '-' . $data->partner->address : 'error';
            },
            'group' => true,
            'subGroupOf' => 1,
            'groupedRow' => true,
            'groupOddCssClass' => 'child',
            'groupEvenCssClass' => 'child',
            'groupFooter' => function ($data, $key, $index, $widget) {
                return [
                    'mergeColumns'=>[[3,10]],
                    'content' => [
                        3 => 'Итого по ' . $data->partner->name,
                        11 => GridView::F_SUM,
                    ],
                    'options' => ['style' => 'font-size: smaller; font-weight:bold;']
                ];
            }
        ],
        [
            'attribute' => 'day',
            'filter' => Act::getDayList(),
            'value' => function ($data) {
                return date('j', $data->served_at);
            },
            'filterOptions' => ['style' => 'min-width:60px'],
            'contentOptions' => ['style' => 'min-width:60px'],
            'options' => ['style' => 'min-width:60px'],
        ],
        [
            'attribute' => 'card_id',
            'filter' => Card::find()->select(['number', 'id'])->indexBy('id')->column(),
            'value' => function ($data) {
                return isset($data->card) ? $data->card->number : 'error';
            },
            'filterOptions' => ['style' => 'min-width:80px'],
            'contentOptions' => ['style' => 'min-width:80px'],
            'options' => ['style' => 'min-width:80px'],
        ],
        [
            'attribute' => 'mark_id',
            'filter' => Mark::find()->select(['name', 'id'])->indexBy('id')->column(),
            'value' => function ($data) {
                return isset($data->mark) ? $data->mark->name : 'error';
            },
        ],
        'number',
        'extra_number',
        [
            'attribute' => 'type_id',
            'filter' => Type::find()->select(['name', 'id'])->indexBy('id')->column(),
            'value' => function ($data) {
                return isset($data->type) ? $data->type->name : 'error';
            },
        ],
        [
            'attribute' => 'income',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
        ],
        [
            'header' => 'Услуга',
            'value' => function ($data) {
                return Service::$listType[$data->service_type]['ru'];
            }
        ],
        'check',
        [
            'header' => '',
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{update} {delete}'
        ],
    ],
]);