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
use kartik\grid\GridView;
use yii\helpers\Html;

$columns = [
    [
        'header' => '№',
        'class' => 'kartik\grid\SerialColumn',
        'contentOptions' => ['style' => 'max-width: 40px'],
    ],
    [
        'attribute' => 'day',
        'filter' => Act::getDayList(),
        'value' => function ($data) {
            return date('d-m-Y', $data->served_at);
        },
    ],
    [
        'attribute' => 'partner_id',
        'value' => function ($data) {
            return isset($data->partner) ? $data->partner->name : '';
        },
    ],
    [
        'attribute' => 'client_id',
        'value' => function ($data) {
            return isset($data->client) ? $data->client->name : '';
        },
    ],
    [
        'attribute' => 'card_id',
        'filter' => Card::find()->select(['number', 'id'])->indexBy('id')->column(),
        'value' => function ($data) {
            return isset($data->card) ? $data->card->number : 'error';
        },
        'contentOptions' => function($data) {
            if($data->hasError('card')) return ['class' => 'text-danger'];
        },
        'visible' => $searchModel->service_type != Service::TYPE_DISINFECT,
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
        'header' => 'Расход',
        'attribute' => 'expense',
        'pageSummary' => true,
        'contentOptions' => function($data) {
            if($data->hasError('expense')) return ['class' => 'text-danger'];
        },
    ],
    [
        'header' => 'Приход',
        'attribute' => 'income',
        'pageSummary' => true,
        'contentOptions' => function($data) {
            if($data->hasError('income')) return ['class' => 'text-danger'];
        },
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
        'contentOptions' => function($data) {
            if($data->hasError('check')) return ['class' => 'text-danger'];
        },
        'visible' => $searchModel->service_type == Service::TYPE_WASH,
    ],
    [
        'header'         => '',
        'class'          => 'kartik\grid\ActionColumn',
        'template'       => '{update}{delete}{add-car}',
        'contentOptions' => ['style' => 'min-width: 100px'],
        'buttons'        => [
            'add-car' => function ($url, $model, $key) {
                if (isset($model->car->company_id)) {
                    return false;
                }
                $url = \yii\helpers\Url::to(['/car/create']);
                $data = http_build_query([
                    'Car[company_id]' => $model->client_id,
                    'Car[number]'     => $model->number,
                    'Car[mark_id]'    => $model->mark_id,
                    'Car[type_id]'    => $model->type_id
                ]);

                return Html::a('<span class="glyphicon glyphicon-search"></span>',
                    '#',
                    [
                        'title'      => "Добавить номер",
                        'aria-label' => "Добавить номер",
                        'data-pjax'  => "0",
                        'onclick'    => "
                            $.ajax('$url', {
                                type: 'POST',
                                data:'$data',
                            }).done(function(data) {
                                window.location.reload();
                            });
                        return false;
                        ",
                    ]);
            },
        ],
    ],
];


echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'summary' => false,
    'emptyText' => '',
    'floatHeader' => true,
    'floatHeaderOptions' => ['top' => '0'],
    'panel' => [
        'type' => 'primary',
        'heading' => 'Ошибочные акты',
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'hover' => false,
    'striped' => false,
    'export' => false,
    'columns' => $columns,
]);