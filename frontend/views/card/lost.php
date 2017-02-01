<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
use common\models\User;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\CardSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = 'Карты';

echo $this->render('_lost_form');

Pjax::begin();
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'hover' => false,
    'striped' => false,
    'export' => false,
    'summary' => false,
    'emptyText' => '',
    'panel' => [
        'type' => 'primary',
        'heading' => 'Карты в поиске',
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'columns' => [
        [
            'header' => '№',
            'class' => 'yii\grid\SerialColumn'
        ],
        [
            'attribute' => 'company_id',
            'content' => function ($data) {
                return $data->company->name;
            },
        ],
        [
            'attribute' => 'number',
        ],
        [
            'attribute' => 'car_number',
            'header' => 'Номер авто',
        ],
        [
            'attribute' => 'car_mark',
            'header' => 'Марка',
        ],
        [
            'attribute' => 'car_type',
            'header' => 'Тип',
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{delete}',
            'options' => [
                'style' => 'width: 70px',
            ],
            'buttons' => [
                'delete' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/card/find', 'number' => $model->number]);
                }
            ]
        ]
    ],
]);
Pjax::end();