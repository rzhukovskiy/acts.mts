<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 */

use kartik\grid\GridView;
use common\models\Act;
use common\models\Company;
use common\models\Card;
use common\models\Mark;
use common\models\Type;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'summary' => false,
    'emptyText' => '',
    'floatHeader' => true,
    'floatHeaderOptions' => ['scrollingTop' => '0'],
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
            'attribute' => 'period',
            'filter' => Act::getPeriodList(),
            'value' => function ($data) {
                return date('m-Y', $data->served_at);
            },
            'filterOptions' => ['style' => 'min-width:105px'],
            'contentOptions' => ['style' => 'min-width:105px'],
            'options' => ['style' => 'min-width:105px'],
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_COUNT,
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
            'attribute' => 'partner_id',
            'filter' => Company::find()->select(['name', 'id'])->indexBy('id')->column(),
            'value' => function ($data) {
                return isset($data->partner) ? $data->partner->name : 'error';
            },
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
        'number',
        'extra_number',
        [
            'attribute' => 'mark_id',
            'filter' => Mark::find()->select(['name', 'id'])->indexBy('id')->column(),
            'value' => function ($data) {
                return isset($data->mark) ? $data->mark->name : 'error';
            },
        ],
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
        'check',
        [
            'header' => '',
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{update} {delete}'
        ],
    ],
]);