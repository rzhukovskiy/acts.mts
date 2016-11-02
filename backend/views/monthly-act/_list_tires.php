<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\MonthlyActSearch
 * @var $admin boolean
 */
use common\models\MonthlyAct;
use kartik\grid\GridView;
use yii\helpers\Html;

echo GridView::widget([
    'id'               => 'act-grid',
    'dataProvider'     => $dataProvider,
    'summary'          => false,
    'emptyText'        => '',
    'panel'            => [
        'type'    => 'primary',
        'heading' => 'Сводные акты по ' . \common\models\Service::$listType[$type]['ru'],
        'before'  => false,
        'footer'  => false,
        'after'   => false,
    ],
    'resizableColumns' => false,
    'hover'            => false,
    'striped'          => false,
    'export'           => false,
    'showPageSummary'  => false,
    'filterSelector'   => '.ext-filter',
    'beforeHeader'     => [
        [
            'columns' => [
                [
                    'content' => $filters,
                    'options' => [
                        'style'   => 'vertical-align: middle',
                        'colspan' => 8,
                        'class'   => 'kv-grid-group-filter',
                    ],
                ]
            ],
            'options' => ['class' => 'extend-header'],
        ],
    ],
    'layout'           => '{items}',
    'columns'          => [
        [
            'header'      => '№',
            'class'       => 'kartik\grid\SerialColumn',
            'pageSummary' => 'Всего',
            'mergeHeader' => false,
            'width'       => '30px',
            'vAlign'      => GridView::ALIGN_TOP,
        ],
        [
            'attribute'         => 'client_id',
            'group'             => true,  // enable grouping
            'options'           => ['class' => 'kv-grouped-header'],
            'groupedRow'        => true,  // enable grouping
            'groupOddCssClass'  => 'kv-group-header',  // configure odd group cell css class
            'groupEvenCssClass' => 'kv-group-header', // configure even group cell css class
            'value'             => function ($data) {
                return isset($data->client) ? $data->client->name : 'error';
            },
        ],
        [
            'header' => 'Город',
            'value'  => function ($data) {
                return isset($data->client) ? $data->client->address : 'error';
            },

        ],
        [
            'attribute'       => 'profit',
            'value'           => function ($data) {
                return $data->profit;
            },
            'pageSummary'     => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'format'          => 'html',
        ],
        [
            'attribute'      => 'payment_status',
            'value'          => function ($data) {
                return MonthlyAct::$paymentStatus[$data->payment_status];
            },
            'filter'         => false,
            'contentOptions' => function ($model) {
                return [
                    'class' => MonthlyAct::colorForPaymentStatus($model->payment_status),
                    'style' => 'min-width: 100px'
                ];
            },
        ],
        'payment_date',
        [
            'attribute'      => 'act_status',
            'value'          => function ($data) {
                return MonthlyAct::$actStatus[$data->act_status];
            },
            'contentOptions' => function ($model) {
                return ['class' => MonthlyAct::colorForStatus($model->act_status), 'style' => 'min-width: 160px'];
            },
            'filter'         => false,

        ],
        /*
        'img'            => [
            'attribute' => 'img',
            'value'     => function ($data) {
                return $data->getImageList();
            },
            'filter'    => false,
            'format'    => 'raw'
        ],
        */
        [
            'class'          => 'kartik\grid\ActionColumn',
            'template'       => '{update}',
            'contentOptions' => ['style' => 'min-width: 40px'],
            'visibleButtons' => $visibleButton,
            'buttons'        => [
                'update' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/monthly-act/update', 'id' => $model->id]);
                },
            ]
        ],
    ],
]);
