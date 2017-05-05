<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\MonthlyActSearch
 * @var $admin boolean
 */
use common\models\MonthlyAct;
use common\models\Service;
use yii\helpers\Html;
use common\models\User;
use kartik\date\DatePicker;

$isAdmin = $admin ? 1 : 0;

$script = <<< JS
    $('.change-payment_status').change(function(){
       
     var select=$(this);
        $.ajax({
            url: "/monthly-act/ajax-payment-status",
            type: "post",
            data: {status:$(this).val(),id:$(this).data('id')},
            success: function(data){
                select.parent().attr('class',data);
                if(($isAdmin!=1)&&(select.data('paymentstatus')!=1)){
                    select.attr('disabled', 'disabled');
                }
            }
        });
    });
    
    $('.change-act_status').change(function(){
        var select=$(this);
        $.ajax({
            url: "/monthly-act/ajax-act-status",
            type: "post",
            data: {status:$(this).val(),id:$(this).data('id')},
            success: function(data){
            var obj = jQuery.parseJSON(data);
            select.parent().attr('class',obj.color);

            if($isAdmin!=1){

                        var i = obj.value;
                        if( i == 2){
                            select.children('option[value="0"]').remove();
                            select.children('option[value="1"]').remove();
                            select.children('option[value="3"]').remove();

                        }else if( i == 3) {
                            select.children('option[value="0"]').remove();
                            select.children('option[value="1"]').remove();

                        }else if( i == 4){
                            select.children('option[value="0"]').remove();
                            select.children('option[value="1"]').remove();
                            select.children('option[value="2"]').remove();
                            select.children('option[value="3"]').remove();
                        }else if( i == 1){
                            select.children('option[value="0"]').remove();

                        }
            }

            }
        });
    });
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

//Настройки фильтров
$filters = 'Период: ' . DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'act_date',
        'type' => DatePicker::TYPE_INPUT,
        'language' => 'ru',
        'pluginOptions' => [
            'autoclose' => true,
            'changeMonth' => true,
            'changeYear' => true,
            'showButtonPanel' => true,
            'format' => 'm-yyyy',
            'maxViewMode' => 2,
            'minViewMode' => 1,
            //'endDate'         => '-1m'
        ],
        'options' => [
            'class' => 'form-control ext-filter',
        ]
    ]);
//Настройки кнопок

// Кнопки не оплачен и не подписан
if (strpos(Yii::$app->request->url, '&filterStatus=') > 0) {
    $filters .= Html::a('<span class="btn btn-danger btn-sm" style="margin-left: 15px;">Не оплаченные</span>', substr(Yii::$app->request->url, 0, strpos(Yii::$app->request->url, '&filterStatus=')) . '&filterStatus=' . 1);
} else {
    $filters .= Html::a('<span class="btn btn-danger btn-sm" style="margin-left: 15px;">Не оплаченные</span>', Yii::$app->request->url . '&filterStatus=' . 1);;
}

if (strpos(Yii::$app->request->url, '&filterStatus=') > 0) {
    $filters .= Html::a('<span class="btn btn-primary btn-sm" style="margin-left: 15px;">Не подписанные</span>', substr(Yii::$app->request->url, 0, strpos(Yii::$app->request->url, '&filterStatus=')) . '&filterStatus=' . 2);
} else {
    $filters .= Html::a('<span class="btn btn-primary btn-sm" style="margin-left: 15px;">Не подписанные</span>', Yii::$app->request->url . '&filterStatus=' . 2);;
}
// Кнопки не оплачен и не подписан

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
    'target' => 'a.fancybox',
    'helpers' => true,
    'mouse' => true,
    'config' => [
        'maxWidth' => '90%',
        'maxHeight' => '90%',
        'playSpeed' => 7000,
        'padding' => 0,
        'fitToView' => false,
        'width' => '70%',
        'height' => '70%',
        'autoSize' => false,
        'closeClick' => false,
        'openEffect' => 'elastic',
        'closeEffect' => 'elastic',
        'prevEffect' => 'elastic',
        'nextEffect' => 'elastic',
        'closeBtn' => false,
        'openOpacity' => true,
        'helpers' => [
            'title' => ['type' => 'float'],
            'buttons' => [],
            'thumbs' => ['width' => 68, 'height' => 50],
            'overlay' => [
                'css' => [
                    'background' => 'rgba(0, 0, 0, 0.8)'
                ]
            ]
        ],
    ]
]);
?>
<?php if ($type == Service::TYPE_DISINFECT) {
    echo $this->render('_list_disinfect',
        [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'admin' => $admin,
            'filters' => $filters,
            'visibleButton' => $visibleButton
        ]);
} elseif ($type == Service::TYPE_SERVICE) {
    echo $this->render('_list_service',
        [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'admin' => $admin,
            'filters' => $filters,
            'visibleButton' => $visibleButton
        ]);
} else {
    echo $this->render('_list_common',
        [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'admin' => $admin,
            'filters' => $filters,
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
