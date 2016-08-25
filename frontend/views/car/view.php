<?php

/**
 * @var $searchModel common\models\search\CarSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Car
 */

use common\models\Card;
use common\models\Company;
use common\models\Service;
use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\bootstrap\Tabs;

$this->title = 'История машины ' . $model->number;

$request = Yii::$app->request;

echo Tabs::widget([
    'items' => [
        [
            'label' => 'Машины',
            'url' => ['car/list'],
            'active' => false,
        ],
        [
            'label' => 'История машины',
            'url' => '#',
            'active' => true,
        ],
    ],
]);

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
            'attribute' => 'served_at',
            'value' => function ($data) {
                return date('d-m-Y', $data->served_at);
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
            'attribute' => 'partner.address',
            'filter' => Company::find()->select(['name', 'id'])->indexBy('id')->column(),
            'value' => function ($data) {
                return isset($data->partner) ? $data->partner->address : 'error';
            },
        ],
        [
            'attribute' => 'income',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
        ],
        [
            'header' => '',
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url, $data, $key) {
                    if ($data->service_type == Service::TYPE_WASH) {
                        return '';
                    }
                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['act-view', 'id' => $data->id]);
                },
            ],
        ],
    ],
]);