<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\MonthlyActSearch
 * @var $admin boolean
 */
use kartik\grid\GridView;

$visibleButton = [
    'update' => function ($model, $key, $index) {
        return Yii::$app->user->can(\common\models\User::ROLE_ADMIN);
    },
    'delete' => function ($model, $key, $index) {
        return Yii::$app->user->can(\common\models\User::ROLE_ADMIN);
    },
];

echo GridView::widget([
    'id'               => 'act-grid',
    'dataProvider'     => $dataProvider,
    'filterModel'      => $searchModel,
    'showPageSummary'  => false,
    'summary'          => false,
    'emptyText'        => '',
    'panel'            => [
        'type'    => 'primary',
        'heading' => 'Контакты по ' . \common\models\Service::$listType[$type]['ru'],
        'before'  => false,
        'footer'  => false,
        'after'   => false,
    ],
    'resizableColumns' => false,
    'hover'            => false,
    'striped'          => false,
    'export'           => false,
    'filterSelector'   => '.ext-filter',
    'beforeHeader'     => [
        [
            'columns' => [
                [
                    'content' => $filters,
                    'options' => [
                        'style'   => 'vertical-align: middle',
                        'colspan' => 9,
                        'class'   => 'kv-grid-group-filter',
                    ],
                ]
            ],
            'options' => ['class' => 'extend-header'],
        ],

    ],
    'layout'           => '{items}',
    'columns'          => [
        [
            'header' => '№',
            'class'  => 'yii\grid\SerialColumn'
        ],
        /*
        'company' => [
            'attribute' => 'company_id',
            'value'     => function ($data) {
                return isset($data->company) ? $data->company->name : 'error';
            },
            'filter'    => false,
        ],
        */
        'name',
        'description',
        [
            'class'          => 'yii\grid\ActionColumn',
            'template'       => '{update}{delete}',
            'contentOptions' => ['style' => 'min-width: 80px'],
            'visibleButtons' => $visibleButton,
        ],
    ],
]);
?>
