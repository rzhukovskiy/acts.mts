<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\MonthlyActSearch
 * @var $admin boolean
 */
use common\models\MonthlyAct;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\helpers\Html;

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

if ($admin) {
    $visibleButton = [];
} else {
    $visibleButton = [
        'update' => function ($model, $key, $index) {
            return $model->act_status != MonthlyAct::ACT_STATUS_DONE;
        },
        'detail' => function ($model, $key, $index) {
            return $model->act_status != MonthlyAct::ACT_STATUS_DONE;
        },
        'delete' => function ($model, $key, $index) {
            return false;
        },
    ];
}

echo newerton\fancybox\FancyBox::widget([
    'target'  => 'a[rel=fancybox]',
    'helpers' => true,
    'mouse'   => true,
    'config'  => [
        'maxWidth'    => '90%',
        'maxHeight'   => '90%',
        'playSpeed'   => 7000,
        'padding'     => 0,
        'fitToView'   => false,
        'width'       => '70%',
        'height'      => '70%',
        'autoSize'    => false,
        'closeClick'  => false,
        'openEffect'  => 'elastic',
        'closeEffect' => 'elastic',
        'prevEffect'  => 'elastic',
        'nextEffect'  => 'elastic',
        'closeBtn'    => false,
        'openOpacity' => true,
        'helpers'     => [
            'title'   => ['type' => 'float'],
            'buttons' => [],
            'thumbs'  => ['width' => 68, 'height' => 50],
            'overlay' => [
                'css' => [
                    'background' => 'rgba(0, 0, 0, 0.8)'
                ]
            ]
        ],
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
                        return MonthlyAct::$paymentStatus[$data->payment_status];
                    },
                    'filter'    => false,
                ],
                'payment_date',
                'act_status'     => [
                    'attribute' => 'act_status',
                    'value'     => function ($data) {
                        return MonthlyAct::$actStatus[$data->act_status];
                    },
                    'filter'    => false,
                ],
                'img'            => [
                    'attribute' => 'img',
                    'value'     => function ($data) {
                        $allImg = [];
                        if (!$data->img) {
                            return false;
                        }
                        foreach ($data->img as $img) {
                            $imgName = explode("/", $img);
                            $imgName = array_pop($imgName);
                            $a = Html::tag('a', $imgName, ['rel' => 'fancybox', 'href' => $img]);
                            $a .= Html::tag('a',
                                '',
                                [
                                    'class' => 'glyphicon glyphicon-remove',
                                    'href'  => \yii\helpers\Url::to([
                                            'monthly-act/delete-image',
                                            'id'  => $data->id,
                                            'url' => $img
                                        ])
                                ]);
                            $allImg[] = Html::tag('p', $a);
                        }

                        return implode('', $allImg);
                    },
                    'filter'    => false,
                    'format'    => 'raw'
                ],
                [
                    'class'          => 'yii\grid\ActionColumn',
                    'template'       => '{update}{detail}{delete}',
                    'contentOptions' => ['style' => 'min-width: 80px'],
                    'buttons'        => [
                        'detail' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-zoom-in"></span>',
                                ['/monthly-act/detail', 'id' => $model->id],
                                ['title' => "Детализация", 'aria-label' => "Детализация", 'data-pjax' => "0"]);
                        },
                    ],
                    'visibleButtons' => $visibleButton
                ],
            ],
        ]);
        ?>
    </div>
</div>