<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $group string
 * @var $admin boolean
 */

use common\models\Service;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\helpers\Html;

//Выбор периода
$filters = 'Период: ' . DatePicker::widget([
        'model'         => $searchModel,
        'attribute'     => 'period',
        'type'          => DatePicker::TYPE_INPUT,
        'language'      => 'ru',
        'pluginOptions' => [
            'autoclose'       => true,
            'changeMonth'     => true,
            'changeYear'      => true,
            'showButtonPanel' => true,
            'format'          => 'm-yyyy',
            'maxViewMode'     => 2,
            'minViewMode'     => 1,
        ],
        'options'       => [
            'class' => 'form-control ext-filter',
        ]
    ]);

$columns = [
    [
        'header' => '№',
        'class' => 'kartik\grid\SerialColumn',
        'contentOptions' => ['style' => 'max-width: 40px'],
    ],
    'partner.address',
    [
        'header' => 'Количество обслуженных машин',
        'value' => function ($data) {
            return $data->actsCount . ' ТС';
        },
    ],
//    [
//        'header' => '',
//        'mergeHeader' => false,
//        'class' => 'kartik\grid\ActionColumn',
//        'template' => '{view}',
//        'width' => '40px',
//        'buttons' => [
//            'view' => function ($url, $data, $key) use ($company){
//                if ($company) {
//                    return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'id' => $data->id, 'company' => $company]);
//                } else {
//                    return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'id' => $data->id]);
//                }
//            },
//        ],
//    ],
];

if ($group == 'type') {
    $columns[1] = [
        'header' => 'Тип услуги',
        'value' => function ($data) {
            return Service::$listType[$data->service_type]['ru'];
        }
    ];
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => false,
    'emptyText' => '',
    'filterSelector' => '.ext-filter',
    'panel' => [
        'type' => 'primary',
        'heading' => 'Анализ данных',
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'hover' => false,
    'striped' => false,
    'export' => false,
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
    ],
    'columns' => $columns,
]);