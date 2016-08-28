<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 */

use common\models\Act;
use common\models\Card;
use common\models\Company;
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
                'class' => 'form-control ext-filter',
            ]
        ]),
        'options' => ['colspan' => 2, 'class' => 'kv-grid-group-filter'],
    ],
    [
        'content' => 'Выбор филиала:',
        'options' => ['colspan' => 2, 'style' => 'vertical-align: middle'],
    ],
    [
        'content' => Html::activeDropDownList($searchModel, 'client_id', Company::find()
            ->where(['parent_id' => Yii::$app->user->identity->company_id])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter']),
    ],
    [
        'content' => Html::a('Пересчитать', array_merge(['act/fix'], Yii::$app->getRequest()->get()), ['class' => 'btn btn-primary btn-sm']),
    ],
    [
        'content' => Html::a('Выгрузить', '#', ['class' => 'btn btn-primary btn-sm']),
    ],
];

$columns = [
    [
        'header' => '№',
        'class' => 'kartik\grid\SerialColumn',
        'pageSummary' => 'Всего',
        'mergeHeader' => false,
        'width' => '30px',
        'vAlign' => GridView::ALIGN_TOP,
    ],
    [
        'attribute' => 'parent_id',
        'value' => function ($data) {
            return isset($data->client->parent) ? $data->client->parent->name : 'без филиалов';
        },
        'group' => true,
        'groupFooter' => function ($data) {
            return [
                'mergeColumns' => [[0, 5]],
                'content' => [
                    0 => 'Итого по ' . (isset($data->client->parent) ? $data->client->parent->name : 'без филиалов'),
                    8 => GridView::F_SUM,
                ],
                'contentOptions' => [
                    6 => ['style' => 'display: none'],
                    7 => ['style' => 'display: none'],
                ],
                'options' => [
                    'class' => isset($data->client->parent) ? '' : 'hidden',
                    'style' => 'font-weight:bold;'
                ]
            ];
        },
        'groupHeader' => function ($data) {
            return [
                'mergeColumns' => [[0, 11]],
                'content' => [
                    0 => $data->client->parent->name,
                ],
                'options' => [
                    'class' => isset($data->client->parent) ? '' : 'hidden',
                    'style' => 'font-weight:bold;'
                ]
            ];
        },
        'hidden' => true,
    ],
    [
        'attribute' => 'client_id',
        'value' => function ($data) {
            return isset($data->client) ? $data->client->name . ' - ' . $data->client->address : 'error';
        },
        'group' => true,
        'subGroupOf' => 1,
        'groupFooter' => function ($data) {
            return [
                'mergeColumns' => [[2, 5]],
                'content' => [
                    2 => 'Итого по ' . $data->client->name,
                    8 => GridView::F_SUM,
                ],
                'contentOptions' => [
                    7 => ['style' => 'display: none'],
                ],
                'options' => ['style' => 'font-size: smaller; font-weight:bold;']
            ];
        },
        'groupHeader' => function ($data) {
            return [
                'mergeColumns' => [[0, 11]],
                'content' => [
                    0 => $data->client->name . ' - ' . $data->client->address,
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
        'attribute' => 'income',
        'pageSummary' => true,
        'pageSummaryFunc' => GridView::F_SUM,
        'contentOptions' => function($data) {
            if($data->hasError('income')) return ['class' => 'text-danger'];
        },
    ],
    [
        'header' => '',
        'class' => 'kartik\grid\ActionColumn',
        'template' => $role == User::ROLE_ADMIN ? '{update}{delete}{view}' : '{view}',
        'contentOptions' => ['style' => 'min-width: 100px'],
        'buttons' => [
            'view' => function ($url, $data, $key) {
                return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'id' => $data->id, 'company' => 1]);
            },
        ],
    ],
];

if ($role != User::ROLE_ADMIN) {
    if (!empty(Yii::$app->user->identity->company->children)) {
        unset($columns[1]);
    } else {
        $headerColumns[2]['content'] = '';
        $headerColumns[3]['content'] = '';
        unset($columns[1], $columns[2]);
    }
    $headerColumns[4]['content'] = '';
    $headerColumns[5]['content'] = '';
} else {
    $headerColumns[2]['content'] = '';
    $headerColumns[3]['content'] = '';
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
    'filterSelector' => '.ext-filter',
    'beforeHeader' => [
        [
            'columns' => $headerColumns,
            'options' => ['class' => 'extend-header'],
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