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
use yii\web\View;

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
    $filters .= ' Выбор филиала: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->andWhere(['parent_id' => Yii::$app->user->identity->company_id])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все', 'class' => 'form-control ext-filter']);
}
if ($role == User::ROLE_ADMIN) {
    $filters .= Html::a('Выгрузить', array_merge(['act/export'], Yii::$app->getRequest()->get()), ['class' => 'pull-right btn btn-primary btn-sm']);
    $filters .= Html::a('Пересчитать', array_merge(['act/fix'], Yii::$app->getRequest()->get()), ['class' => 'pull-right btn btn-primary btn-sm']);
}

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
        'hidden' => true,
        'contentOptions' => function ($data) {
            return isset($data->client->parent) ? [
                'class' => 'grouped',
                'data-header' => $data->client->parent->name,
                'data-footer' => 'Итого ' . $data->client->parent->name . ':',
            ] : [];
        },
    ],
    [
        'attribute' => 'client_id',
        'value' => function ($data) {
            return isset($data->client) ? $data->client->name . ' - ' . $data->client->address : 'error';
        },
        'hidden' => true,
        'contentOptions' => function ($data) {
            return isset($data->client) ? [
                'class' => 'grouped',
                'data-header' => $data->client->name . ' - ' . $data->partner->address,
                'data-footer' => 'Итого ' . $data->client->name . ':',
                'data-parent' => 1,
            ] : [];
        },
    ],
    [
        'attribute' => 'day',
        'filter' => Act::getDayList(),
        'value' => function ($data) use ($role) {
            return $role == User::ROLE_ADMIN ? date('j', $data->served_at) : date('d-m-Y', $data->served_at);
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
        'value' =>  function ($data) {
            return $data->number . ($data->client->is_split ? " ($data->extra_number)" : '');
        },
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
        'attribute' => 'card_id',
        'filter' => Card::find()->select(['number', 'id'])->indexBy('id')->column(),
        'value' => function ($data) {
            return isset($data->card) ? $data->card->number : 'error';
        },
        'contentOptions' => function ($data) {
            if ($data->hasError('card')) return ['style' => 'min-width:80px', 'class' => 'text-danger'];
            return ['style' => 'min-width:80px'];
        },
    ],
    [
        'header' => 'Услуга',
        'value' => function ($data) {
            /** @var \common\models\ActScope $scope */
            $services = [];
            foreach ($data->clientScopes as $scope) {
                $services[] = $scope->description;
            }
            return implode('+', $services);
        }
    ],
    [
        'attribute' => 'income',
        'pageSummary' => true,
        'pageSummaryFunc' => GridView::F_SUM,
        'contentOptions' => function ($data) {
            $options['class'] = 'sum';
            if ($data->hasError('income')) $options['class'] .= ' text-danger';
            return $options;
        },
    ],
    'partner.address',
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
        'contentOptions' => function ($data) {
            if ($data->hasError('check')) return ['class' => 'text-danger'];
        },
    ],
    [
        'header' => '',
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{update}{delete}',
        'contentOptions' => ['style' => 'min-width: 100px'],
        'mergeHeader' => false,
    ],
];

echo GridView::widget([
    'id' => 'act-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
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
            'columns' => [
                [
                    'content' => $filters,
                    'options' => [
                        'style' => 'vertical-align: middle',
                        'colspan' => count($columns),
                        'class' => 'kv-grid-group-filter',
                    ],
                ]
            ],
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

$script = <<< JS
    function createHeaders() {
        addHeaders({
            tableSelector: "#act-grid",
            footers: [
                {
                    className: '.parent',
                    title: 'Всего',
                    rowClass: 'main total'
                },
                {
                    className: '.client',
                    title: 'Итого',
                    rowClass: 'total'
                },
            ],
            headers: [
                {
                    className: '.parent',
                    rowClass: 'main header'
                },
                {
                    className: '.client',
                    rowClass: 'header'
                }
            ]
        });
    }

    $(document).ready(function() {
        createHeaders();
    });
JS;
$this->registerJs($script, View::POS_READY);