<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 */

use kartik\grid\GridView;
use common\models\Act;
use common\models\Company;
use common\models\Card;
use common\models\Mark;
use common\models\Type;
use common\models\User;
use common\models\Service;

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
                    'mergeColumns'=>[[0,10]],
                    'content' => [
                        0 => 'Итого по ' . (isset($data->client->parent) ? $data->client->parent->name : 'без филиалов'),
                        11 => GridView::F_COUNT,
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
                    'mergeColumns'=>[[3,10]],
                    'content' => [
                        3 => 'Итого по ' . $data->client->name,
                        11 => GridView::F_SUM,
                    ],
                    'options' => ['style' => 'font-size: smaller; font-weight:bold;']
                ];
            },
            'visible' => $role == User::ROLE_ADMIN,
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
            'attribute' => 'client_id',
            'filter' => Company::find()->select(['name', 'id'])->indexBy('id')->column(),
            'value' => function ($data) {
                return isset($data->client) ? $data->client->name : 'error';
            },
            'visible' => $role == User::ROLE_ADMIN,
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
        [
            'attribute' => 'extra_number',
            'visible' => $role == User::ROLE_ADMIN,
        ],
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
            'header' => 'Услуга',
            'value' => function ($data) {
                return Service::$listType[$data->service_type]['ru'];
            }
        ],
        [
            'attribute' => 'income',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
        ],
        [
            'attribute' => 'partner.address',
            'value' => function ($data) {
                return $data->partner->address;
            }
        ],
        'check',
        [
            'header' => '',
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{update} {delete}',
            'visible' => $role == User::ROLE_ADMIN,
        ],
    ],
]);