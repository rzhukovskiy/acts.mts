<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\MonthlyActSearch
 * @var $admin boolean
 */
use common\models\MonthlyAct;
use common\models\User;
use kartik\date\DatePicker;


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

<?php if ($type == \common\models\Service::TYPE_DISINFECT) {
    echo $this->render('_list_disinfect',
        [
            'dataProvider'  => $dataProvider,
            'searchModel'   => $searchModel,
            'type'          => $type,
            'admin'         => $admin,
            'filters'       => $filters,
            'visibleButton' => $visibleButton
        ]);
} elseif ($type == \common\models\Service::TYPE_SERVICE) {
    echo $this->render('_list_service',
        [
            'dataProvider'  => $dataProvider,
            'searchModel'   => $searchModel,
            'type'          => $type,
            'admin'         => $admin,
            'filters'       => $filters,
            'visibleButton' => $visibleButton
        ]);
} else {
    echo $this->render('_list_common',
        [
            'dataProvider'  => $dataProvider,
            'searchModel'   => $searchModel,
            'type'          => $type,
            'admin'         => $admin,
            'filters'       => $filters,
            'visibleButton' => $visibleButton
        ]);
}
/*
 * $clientField = [
        'attribute'         => 'client_id',
        'group'             => true,
        'groupedRow'        => true,
        'groupOddCssClass'  => 'kv-group-header',
        'groupEvenCssClass' => 'kv-group-header',
        'value'             => function ($data) {
            return isset($data->client) ? $data->client->name : 'error';
        },
        'groupFooter'       => function ($model, $key, $index, $widget) { // Closure method
            return [
                'mergeColumns' => [[2, 3]], // columns to merge in summary
                'content'      => [             // content to show in each summary cell
                                                'profit' => GridView::F_SUM,
                ],
                'options'      => ['class' => 'kv-group-footer']
            ];
        }
    ];
 */
