<?php

/**
 * @var $searchModel common\models\search\CarSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Car
 */

use kartik\grid\GridView;
use common\models\Act;
use common\models\Company;
use common\models\Card;

$this->title = 'История машины ' . $model->number;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => false,
    'emptyText' => '',
    'panel' => [
        'type' => 'primary',
        'heading' => 'История машины ' . $model->number,
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
            'pageSummary' => 'Итого',
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
            'attribute' => 'partner.address',
            'filter' => Company::find()->select(['name', 'id'])->indexBy('id')->column(),
            'value' => function ($data) {
                return isset($data->partner) ? $data->partner->address : 'error';
            },
        ],
        [
            'header' => 'Услуга',
            'value' => function ($data) {
                return \common\models\Service::$listType[$data->service_type]['ru'];
            }
        ],
        [
            'attribute' => 'income',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
        ],
    ],
]);