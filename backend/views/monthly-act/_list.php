<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\MonthlyActSearch
 * @var $admin boolean
 */
use common\models\MonthlyAct;
use common\models\User;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\helpers\Html;

//Настройки фильтров
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
//Настройки кнопок
if (Yii::$app->user->can(User::ROLE_ADMIN)) {
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
//Настройки галереи
echo newerton\fancybox\FancyBox::widget([
    'target'  => 'a.fancybox',
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

<?=
GridView::widget([
    'id'               => 'act-grid',
    'dataProvider'     => $dataProvider,
    'filterModel'      => $searchModel,
    'showPageSummary'  => false,
    'summary'          => false,
    'emptyText'        => '',
    'panel'            => [
        'type'    => 'primary',
        'heading' => 'Сводные акты по ' . \common\models\Service::$listType[$type]['ru'],
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
            'attribute'      => 'act_status',
            'value'          => function ($data) {
                return MonthlyAct::$actStatus[$data->act_status];
            },
            'contentOptions' => function ($model) {
                return ['class' => MonthlyAct::colorForStatus($model->act_status)];
            },
            'filter'         => false,
        ],
        'img'            => [
            'attribute' => 'img',
            'value'     => function ($data) {
                return $data->getImageList();
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
                    return Html::a('<span class="glyphicon detail"></span>',
                        ['/monthly-act/detail', 'id' => $model->id],
                        ['title' => "Детализация", 'aria-label' => "Детализация", 'data-pjax' => "0"]);
                },
            ],
            'visibleButtons' => $visibleButton
        ],
    ],
]);
?>
