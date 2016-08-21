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

if ($role == User::ROLE_ADMIN) {
    $headerColumns = [
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
            'content' => Html::activeDropDownList($searchModel, 'period', Act::getPeriodList(),['class' => 'form-control']),
            'options' => ['colspan' => 3, 'class' => 'kv-grid-group-filter'],
        ],
        [
            'content' => '',
            'options' => ['colspan' => $searchModel->service_type == Service::TYPE_WASH ? 6 : 4],
        ],
    ];
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
            'columns' => $headerColumns,
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
                return isset($data->client->parent) ? $data->client->parent->name : 'без филиалов';
            },
            'group' => $role == User::ROLE_ADMIN,
            'groupedRow' => true,
            'groupOddCssClass' => function ($data, $key, $index, $widget) {
                return isset($data->client->parent) ? 'parent' : 'hidden';
            },
            'groupEvenCssClass' => function ($data, $key, $index, $widget) {
                return isset($data->client->parent) ? 'parent' : 'hidden';
            },
            'groupFooter' => function ($data, $key, $index, $widget) {
                return [
                    'mergeColumns'=>[[0,8]],
                    'content' => [
                        0 => 'Итого по ' . (isset($data->client->parent) ? $data->client->parent->name : 'без филиалов'),
                        9 => GridView::F_COUNT,
                    ],
                    'options' => [
                        'class' => isset($data->client->parent) ? '' : 'hidden',
                        'style' => 'font-weight:bold;'
                    ]
                ];
            },
            'visible' => $role == User::ROLE_ADMIN
        ],
        [
            'attribute' => 'client_id',
            'value' => function ($data) {
                return isset($data->client) ? $data->client->name . '-' . $data->client->address : 'error';
            },
            'group' => $role == User::ROLE_ADMIN,
            'subGroupOf' => 1,
            'groupedRow' => true,
            'groupOddCssClass' => 'child',
            'groupEvenCssClass' => 'child',
            'groupFooter' => function ($data, $key, $index, $widget) {
                return [
                    'mergeColumns'=>[[3,8]],
                    'content' => [
                        3 => 'Итого по ' . $data->client->name,
                        9 => GridView::F_SUM,
                    ],
                    'options' => ['style' => 'font-size: smaller; font-weight:bold;']
                ];
            },
            'visible' => $role == User::ROLE_ADMIN,
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
        [
            'attribute' => 'extra_number',
            'visible' => $role == User::ROLE_ADMIN,
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
        [
            'header' => 'Услуга',
            'visible' => $searchModel->service_type == Service::TYPE_WASH,
            'value' => function ($data) {
                return Service::$listType[$data->service_type]['ru'];
            }
        ],
        [
            'attribute' => 'partner.address',
            'value' => function ($data) {
                return $data->partner->address;
            }
        ],
        [
            'attribute' => 'check',
            'visible' => $searchModel->service_type == Service::TYPE_WASH,
        ],
        [
            'header' => '',
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{update} {delete}',
            'visible' => $role == User::ROLE_ADMIN,
        ],
    ],
]);