<?php
use common\models\Plan;
use kartik\editable\Editable;
use yii\bootstrap\Html;

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\PlanSearch
 * @var $admin boolean
 */


echo \kartik\grid\GridView::widget([
    'id'               => 'monthly-act-grid',
    'dataProvider'     => $dataProvider,
    'showPageSummary'  => false,
    'summary'          => false,
    'emptyText'        => '',
    'panel'            => [
        'type'    => 'primary',
        'heading' => 'Задачи',
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
                    'content' => '&nbsp',
                    'options' => [
                        'colspan' => 8,
                    ]
                ]
            ],
            'options' => ['class' => 'kv-group-header'],
        ],
    ],
    'columns'          => [
        [
            'header'        => '№',
            'class'         => 'yii\grid\SerialColumn',
            'footer'        => 'Итого:',
            'footerOptions' => ['style' => 'font-weight: bold'],
            'contentOptions' => ['style' => 'width: 50px'],
        ],
        [
            'attribute'      => 'task_name',
            'contentOptions' => ['style' => 'width:45%'],
        ],
        [
            'value'          => function ($model) {
                return Html::activeDropDownList($model,
                    'status',
                    Plan::$listStatus,
                    [
                        'class'        => 'form-control change-status',
                        'data-user-id' => $model->user_id,
                        'data-id'      => $model->id,
                    ]

                );
            },
            'filter'         => false,
            'format'         => 'raw',
            'attribute'      => 'status',
            'contentOptions' => function ($model) {
                return [
                    'class' => \common\models\Plan::colorForStatus($model->status),
                    'style' => 'width: 150px'
                ];
            },

        ],
        [
            'attribute' => 'comment',
            'value'     => function ($data) {
                return Editable::widget([
                    'model'           => $data,
                    'placement'       => \kartik\popover\PopoverX::ALIGN_TOP_RIGHT,
                    'formOptions'     => [
                        'action' => ['update', 'id' => $data->id]
                    ],
                    'valueIfNull'     => '(не задано)',
                    'buttonsTemplate' => '{submit}',
                    'inputType'       => Editable::INPUT_TEXTAREA,
                    'submitButton'    => [
                        'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                    ],
                    'attribute'       => 'comment',
                    'asPopover'       => true,
                    'size'            => 'md',
                    'options'         => [
                        'class'       => 'form-control',
                        'placeholder' => 'Введите название',
                        'id'          => 'editable' . $data->id
                    ],
                ]);
            },
            'format'    => 'raw'
        ],
        [
            'class'          => 'yii\grid\ActionColumn',
            'template'       => '{delete}',
            'contentOptions' => ['style' => 'width: 60px'],
            'visible'        => $admin,
        ],


    ],
]);
