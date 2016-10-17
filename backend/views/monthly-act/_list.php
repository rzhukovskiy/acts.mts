<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 */
use common\models\MonthlyAct;
use kartik\date\DatePicker;
use kartik\grid\GridView;


$filters = 'Период: ' . DatePicker::widget([
        'model'         => $searchModel,
        'attribute'     => 'act_date',
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
            'endDate'         => '-1m'
        ],
        'options'       => [
            'class' => 'form-control ext-filter',
        ]
    ]);

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список моек
    </div>
    <div class="panel-body">
        <?=
        GridView::widget([
            'id'               => 'act-grid',
            'dataProvider'     => $dataProvider,
            'filterModel'      => $searchModel,
            'showPageSummary'  => false,
            'emptyText'        => '',
            'panel'            => [
                'type'    => 'primary',
                'heading' => 'Услуги',
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
                                'colspan' => 8,
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
                'city'           => [
                    'header' => 'Город',
                    'value'  => function ($data) {
                        return isset($data->client) ? $data->client->address : 'error';
                    },
                ],
                'client'         => [
                    'attribute' => 'client_id',
                    'value'     => function ($data) {
                        return isset($data->client) ? $data->client->name : 'error';
                    },
                    'filter'    => false,
                ],
                'profit',
                'payment_status' => [
                    'attribute' => 'payment_status',
                    'value'     => function ($data) {
                        return MonthlyAct::$paymentStatus[$data->payment_status]['ru'];
                    },
                    'filter'    => false,
                ],
                'payment_date',
                'act_status'     => [
                    'attribute' => 'act_status',
                    'value'     => function ($data) {
                        return MonthlyAct::$actStatus[$data->act_status]['ru'];
                    },
                    'filter'    => false,
                ],
                [
                    'class'          => 'yii\grid\ActionColumn',
                    'template'       => '{update} {delete}',
                    'contentOptions' => ['style' => 'min-width: 80px'],
                ],
            ],
        ]);
        ?>
    </div>
</div>