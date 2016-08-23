<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 */

use common\models\Act;
use common\models\Card;
use common\models\Mark;
use common\models\Service;
use common\models\Type;
use common\models\User;
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\date\DatePicker;


if ($role == User::ROLE_ADMIN) {
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
                ],
                'options' => [
                    'class' => 'form-control',
                ]
            ]),
            'options' => ['colspan' => 3, 'class' => 'kv-grid-group-filter'],
        ], '', '',
        [
            'content' => Html::a('Пересчитать', '#', ['class' => 'btn btn-primary btn-sm']),
        ],
        [
            'content' => Html::a('Выгрузить', '#', ['class' => 'btn btn-primary btn-sm']),
            'options' => ['colspan' => 5],
        ],
    ];
} else {
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
                ],
                'options' => [
                    'class' => 'form-control',
                ]
            ]),
            'options' => ['colspan' => 3, 'class' => 'kv-grid-group-filter'],
        ], '', '', '', '', '',
    ];
}

$columns = [
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
                'mergeColumns' => [[0, 8]],
                'content' => [
                    0 => 'Итого по ' . (isset($data->partner->parent) ? $data->partner->parent->name : 'без филиалов'),
                    9 => GridView::F_COUNT,
                ],
                'options' => [
                    'class' => isset($data->partner->parent) ? '' : 'hidden',
                    'style' => 'font-weight:bold;'
                ]
            ];
        },
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
                'mergeColumns' => [[3, 8]],
                'content' => [
                    3 => 'Итого по ' . $data->partner->name,
                    9 => GridView::F_SUM,
                ],
                'options' => ['style' => 'font-size: smaller; font-weight:bold;']
            ];
        },
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
        'attribute' => 'expense',
        'pageSummary' => true,
        'pageSummaryFunc' => GridView::F_SUM,
    ],
    [
        'header' => 'Услуга',
        'value' => function ($data) {
            if ($data->service_type == Service::TYPE_WASH) {
                /** @var \common\models\ActScope $scope */
                $services = [];
                foreach ($data->partnerScopes as $scope) {
                    $services[] = $scope->description;
                }
                return implode('+', $services);
            }
            return Service::$listType[$data->service_type]['ru'];
        }
    ],
    [
        'attribute' => 'check',
        'value' => function ($data) {
            $imageLink = $data->getImageLink();
            if ($imageLink) {
                return Html::a($data->check, $imageLink, ['class' => 'preview']);
            }
            return 'error';
        },
        'format' => 'raw',
        'visible' => $searchModel->service_type == Service::TYPE_WASH,
    ],
    [
        'header' => '',
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{update} {delete}',
        'contentOptions' => ['style' => 'min-width: 80px'],
    ],
];
if ($role != User::ROLE_ADMIN) {
    unset($columns[1], $columns[2], $columns[12]);
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
            'options' => ['class' => 'filters', 'id' => 'w1-filters'],
        ],
    ],
    'columns' => $columns,
]);