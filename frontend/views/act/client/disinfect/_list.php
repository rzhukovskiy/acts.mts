<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 */

use common\models\Act;
use common\models\Company;
use common\models\Mark;
use common\models\Type;
use common\models\User;
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\date\DatePicker;

$filters = 'Период: ' . DatePicker::widget([
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
    ]);

if ($role != User::ROLE_ADMIN && !empty(Yii::$app->user->identity->company->children)) {
    $filters .= ' Выбор филиала: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()
            ->where(['parent_id' => Yii::$app->user->identity->company_id])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все', 'class' => 'form-control ext-filter']);
}
if ($role == User::ROLE_ADMIN) {
    $filters .= Html::a('Выгрузить', array_merge(['act/export'], Yii::$app->getRequest()->get()), ['class' => 'pull-right btn btn-primary btn-sm']);
    $filters .= Html::a('Пересчитать', array_merge(['act/fix'], Yii::$app->getRequest()->get()), ['class' => 'pull-right btn btn-primary btn-sm']);
}

$headerColumns = [
    [
        'content' => $filters,
        'options' => ['style' => 'vertical-align: middle', 'colspan' => 8, 'class' => 'kv-grid-group-filter'],
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
                'mergeColumns' => [[0, 4]],
                'content' => [
                    0 => 'Итого по ' . (isset($data->client->parent) ? $data->client->parent->name : 'без филиалов'),
                    7 => GridView::F_SUM,
                ],
                'contentOptions' => [
                    5 => ['style' => 'display: none'],
                    6 => ['style' => 'display: none'],
                ],
                'options' => [
                    'class' => isset($data->client->parent) ? '' : 'hidden',
                    'style' => 'font-weight:bold;'
                ]
            ];
        },
        'groupHeader' => function ($data) {
            return [
                'mergeColumns' => [[0, 8]],
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
                    7 => GridView::F_SUM,
                ],
                'contentOptions' => [
                    6 => ['style' => 'display: none'],
                ],
                'options' => ['style' => 'font-size: smaller; font-weight:bold;']
            ];
        },
        'groupHeader' => function ($data) {
            return [
                'mergeColumns' => [[0, 8]],
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
        'value' => function ($data) use ($role) {
            return $role == User::ROLE_ADMIN ? date('j', $data->served_at) : date('d-m-Y', $data->served_at);
        },
        'contentOptions' => ['style' => 'min-width:60px'],
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
        'contentOptions' => function ($data) {
            if ($data->hasError('car')) return ['class' => 'text-danger'];
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
        'contentOptions' => function ($data) {
            if ($data->hasError('income')) return ['class' => 'text-danger'];
        },
    ],
    [
        'header' => '',
        'class' => 'kartik\grid\ActionColumn',
        'template' => $role == '{update}{delete}',
        'contentOptions' => ['style' => 'min-width: 100px'],
    ],
];

if ($role != User::ROLE_ADMIN) {
    if (!empty(Yii::$app->user->identity->company->children)) {
        unset($columns[1], $columns[8]);
    } else {
        unset($columns[1], $columns[2], $columns[8]);
    }
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