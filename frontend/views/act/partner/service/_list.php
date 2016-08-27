<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 */

use common\models\Act;
use common\models\Card;
use common\models\Mark;
use common\models\Type;
use common\models\User;
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\date\DatePicker;
$headerColumns = [
    [
        'content' => 'Период:',
        'options' => ['style' => 'vertical-align: middle'],
    ],
    [
        'content' => DatePicker::widget([
            'model' => $searchModel,
            'attribute' => 'period',
            'type' => DatePicker::TYPE_INPUT,
            'language' => 'ru',
            'pluginOptions' => [
                'autoclose' => true,
                'changeMonth' => true,
                'changeYear' => true,
                'showButtonPanel' => true,
                'format' => 'm-yyyy',
                'maxViewMode' => 2,
                'minViewMode' => 1,
            ],
            'options' => [
                'class' => 'form-control',
            ]
        ]),
        'options' => ['colspan' => 2, 'class' => 'kv-grid-group-filter'],
    ],
    [
        'options' => ['style' => 'display: none'],
    ],
    [
        'options' => ['style' => 'display: none'],
    ],
    '',
    '',
    [
        'content' => Html::a('Пересчитать', '#', ['class' => 'btn btn-primary btn-sm']),
    ],
    [
        'content' => Html::a('Выгрузить', '#', ['class' => 'btn btn-primary btn-sm']),
    ],
];

$columns = [
    [
        'header' => '№',
        'class' => 'kartik\grid\SerialColumn',
        'contentOptions' => ['style' => 'max-width: 40px'],
        'pageSummary' => 'Всего',
    ],
    [
        'attribute' => 'parent_id',
        'value' => function ($data) {
            return isset($data->partner->parent) ? $data->partner->parent->name : 'без филиалов';
        },
        'group' => true,
        'groupFooter' => function ($data) {
            return [
                'mergeColumns' => [[0, 7]],
                'content' => [
                    0 => 'Итого по ' . (isset($data->partner->parent) ? $data->partner->parent->name : 'без филиалов'),
                    8 => GridView::F_SUM,
                ],
                'options' => [
                    'class' => isset($data->partner->parent) ? '' : 'hidden',
                    'style' => 'font-weight:bold;'
                ]
            ];
        },
        'groupHeader' => function ($data) {
            return [
                'mergeColumns' => [[0, 11]],
                'content' => [
                    0 => $data->partner->parent->name,
                ],
                'options' => ['style' => 'font-weight:bold;']
            ];
        },
        'hidden' => true,
    ],
    [
        'attribute' => 'partner_id',
        'value' => function ($data) {
            return isset($data->partner) ? $data->partner->name . ' - ' . $data->partner->address : 'error';
        },
        'group' => true,
        'subGroupOf' => 1,
        'groupOddCssClass' => 'child',
        'groupEvenCssClass' => 'child',
        'groupFooter' => function ($data) {
            return [
                'mergeColumns' => [[2, 5]],
                'content' => [
                    2 => 'Итого по ' . $data->partner->name,
                    8 => GridView::F_SUM,
                ],
                'contentOptions' => [      // content html attributes for each summary cell
                    6 => ['style' => 'display: none'],
                ],
                'options' => ['style' => 'font-size: smaller; font-weight:bold;']
            ];
        },
        'groupHeader' => function ($data) {
            return [
                'mergeColumns' => [[0, 11]],
                'content' => [
                    0 => $data->partner->name . ' - ' . $data->partner->address,
                ],
                'options' => ['style' => 'font-size: smaller; font-weight:bold;']
            ];
        },
        'hidden' => true,
    ],
    [
        'attribute' => 'day',
        'filter' => Act::getDayList(),
        'value' => function ($data) use($role) {
            return $role == User::ROLE_ADMIN ? date('j', $data->served_at) : date('d-m-Y', $data->served_at);
        },
        'contentOptions' => ['style' => 'min-width:60px'],
    ],
    [
        'attribute' => 'card_id',
        'filter' => Card::find()->select(['number', 'id'])->indexBy('id')->column(),
        'value' => function ($data) {
            return isset($data->card) ? $data->card->number : 'error';
        },
        'contentOptions' => function($data) {
            if($data->hasError('car')) return ['style' => 'min-width:80px', 'class' => 'text-danger'];
            return ['style' => 'min-width:80px'];
        },
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
        'contentOptions' => function($data) {
            if($data->hasError('car')) return ['class' => 'text-danger'];
        },
    ],
    [
        'attribute' => 'type_id',
        'filter' => Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
        'value' => function ($data) {
            return isset($data->type) ? $data->type->name : 'error';
        },
    ],
    [
        'attribute' => 'expense',
        'pageSummary' => true,
        'pageSummaryFunc' => GridView::F_SUM,
        'contentOptions' => function($data) {
            if($data->hasError('expense')) return ['class' => 'text-danger'];
        },
    ],
    [
        'header' => '',
        'class' => 'kartik\grid\ActionColumn',
        'template' => $role == User::ROLE_ADMIN ? '{update}{delete}{view}' : '{view}',
        'contentOptions' => ['style' => 'min-width: 100px'],
        'buttons' => [
            'view' => function ($url, $data, $key) {
                return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['view', 'id' => $data->id]);
            },
        ],
    ],
];

if ($role != User::ROLE_ADMIN) {
    unset($columns[1], $columns[2]);
    $headerColumns[6]['content'] = '';
    $headerColumns[7]['content'] = '';
}


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $role == User::ROLE_ADMIN ? $searchModel : null,
    'summary' => false,
    'emptyText' => '',
    'floatHeader' => true,
    'floatHeaderOptions' => ['top' => '0'],
    'panel' => [
        'type' => 'primary',
        'heading' => 'Услуги',
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
            'columns' => $headerColumns,
            'options' => ['class' => 'filters extend-header', 'id' => 'w1-filters'],
        ],
        [
            'columns' => [
                [
                    'content' => '&nbsp',
                    'options' => [
                        'colspan' => count($columns),
                    ]
                ]
            ],
            'options' => ['class' => 'kv-group-header'],
        ],
    ],
    'columns' => $columns,
]);