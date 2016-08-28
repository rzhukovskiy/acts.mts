<?php

/**
 * @var $searchModel common\models\search\ActSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Car
 */

use common\models\Company;
use common\models\Service;
use kartik\date\DatePicker;
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
    'filterModel' => $searchModel,
    'summary' => false,
    'emptyText' => '',
    'panel' => [
        'type' => 'primary',
        'heading' => 'История машины ' . $model->number,
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'beforeHeader' => [
        [
            'columns' => [
                [
                    'content' => 'Выбор даты:',
                    'options' => ['style' => 'vertical-align: middle'],
                ],
                [
                    'content' => DatePicker::widget([
                        'model' => $searchModel,
                        'attribute' => 'dateFrom',
                        'attribute2' => 'dateTo',
                        'separator' => '-',
                        'type' => DatePicker::TYPE_RANGE,
                        'language' => 'ru',
                        'pluginOptions' => [
                            'autoclose' => true,
                            'changeMonth' => true,
                            'changeYear' => true,
                            'showButtonPanel' => true,
                            'format' => 'dd-mm-yyyy',
                        ],
                        'options' => [
                            'class' => 'form-control',
                        ]
                    ]),
                    'options' => ['colspan' => 2, 'class' => 'kv-grid-group-filter'],
                ],
                [
                    'content' => Html::submitButton('Показать', ['class' => 'btn btn-primary']),
                ],
                '',
                '',
                '',
            ],
            'options' => ['class' => 'filters extend-header', 'id' => 'w1-filters'],
        ],
        [
            'columns' => [
                [
                    'content' => '&nbsp',
                    'options' => [
                        'colspan' => 7,
                    ]
                ]
            ],
            'options' => ['class' => 'kv-group-header'],
        ],
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
        ],
        [
            'attribute' => 'card_id',
            'value' => function ($data) {
                return isset($data->card) ? $data->card->number : 'error';
            },
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
                    if (in_array($data->service_type, [Service::TYPE_WASH, Service::TYPE_DISINFECT])) {
                        return '';
                    }
                    return Html::a('<span class="glyphicon glyphicon-search"></span>', ['act-view', 'id' => $data->id]);
                },
            ],
        ],
    ],
]);