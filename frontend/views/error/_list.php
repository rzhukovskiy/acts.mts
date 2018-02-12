<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 * @var $admin boolean
 */

use common\models\Act;
use yii\web\View;
use common\models\Mark;
use common\models\Service;
use common\models\Type;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\date\DatePicker;

$actionResave = Url::to('@web/act/resaveact');

$script = <<< JS

$('.AllResave').on('click', function(){
    // Выделить все
    $('.resave').prop('checked','checked');
});
$('.UnResave').on('click', function(){
    // Снять все выделения
    $('.resave').removeAttr('checked');
});
$('.doResave').on('click', function(){
    
    // Выполнить пересохранение
    var actArr = [];
    
    $('.resave').each(function (id, value) {
        
        if($(this).prop("checked")) {
            actArr.push($(this).data("id"));
        }
        
    });
    
    // Отправление данных
    if(actArr.length > 0) {
        
        var doResave = confirm("Вы уверены что хотите выполнить пересохранение?");
    
        if(doResave == true) {
        
        $.ajax({
                type     :'POST',
                cache    : true,
                data:'data=' + JSON.stringify(actArr),
                url  : '$actionResave',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                    // Удачно
                    alert('Успешно');
                    window.location.reload();
                } else {
                    // Неудачно
                    alert('Ошибка пересохранения');
                }
                
                }
        });
        
        }
        
    }
    
});

JS;
$this->registerJs($script, View::POS_READY);

$partnerFilter = '';
$clientFilter = '';

if((Yii::$app->controller->action->id == 'list') && (Yii::$app->controller->id == 'error')) {
    $partnerFilter = Html::activeDropDownList($searchModel, 'partner_id', Act::find()->innerJoin('act_error', '`act_error`.`act_id` = `act`.`id`')->innerJoin('company', '`company`.`id` = `act`.`partner_id`')->where(['AND', ['act.service_type' => Yii::$app->request->get('type')], ['!=', 'act_error.error_type', 19], ['!=', 'act_error.error_type', 20], ['!=', 'act_error.error_type', 21], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $searchModel->period]])->select('company.name')->indexBy('partner_id')->column(), ['class' => 'form-control', 'prompt' => 'Все партнеры']);
    $clientFilter = Html::activeDropDownList($searchModel, 'client_id', Act::find()->innerJoin('act_error', '`act_error`.`act_id` = `act`.`id`')->innerJoin('company', '`company`.`id` = `act`.`client_id`')->where(['AND', ['act.service_type' => Yii::$app->request->get('type')], ['!=', 'act_error.error_type', 19], ['!=', 'act_error.error_type', 20], ['!=', 'act_error.error_type', 21], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $searchModel->period]])->select('company.name')->indexBy('client_id')->column(), ['class' => 'form-control', 'prompt' => 'Все клиенты']);
} elseif((Yii::$app->controller->action->id == 'losses') && (Yii::$app->controller->id == 'error')) {
    $partnerFilter = Html::activeDropDownList($searchModel, 'partner_id', Act::find()->innerJoin('act_error', '`act_error`.`act_id` = `act`.`id`')->innerJoin('company', '`company`.`id` = `act`.`partner_id`')->where(['AND', ['act.service_type' => Yii::$app->request->get('type')], ['act_error.error_type' => 19], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $searchModel->period]])->select('company.name')->indexBy('partner_id')->column(), ['class' => 'form-control', 'prompt' => 'Все партнеры']);
    $clientFilter = Html::activeDropDownList($searchModel, 'client_id', Act::find()->innerJoin('act_error', '`act_error`.`act_id` = `act`.`id`')->innerJoin('company', '`company`.`id` = `act`.`client_id`')->where(['AND', ['act.service_type' => Yii::$app->request->get('type')], ['act_error.error_type' => 19], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $searchModel->period]])->select('company.name')->indexBy('client_id')->column(), ['class' => 'form-control', 'prompt' => 'Все клиенты']);
} elseif((Yii::$app->controller->action->id == 'async') && (Yii::$app->controller->id == 'error')) {
    $partnerFilter = Html::activeDropDownList($searchModel, 'partner_id', Act::find()->innerJoin('act_error', '`act_error`.`act_id` = `act`.`id`')->innerJoin('company', '`company`.`id` = `act`.`partner_id`')->where(['AND', ['act.service_type' => Yii::$app->request->get('type')], ['act_error.error_type' => 20], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $searchModel->period]])->select('company.name')->indexBy('partner_id')->column(), ['class' => 'form-control', 'prompt' => 'Все партнеры']);
    $clientFilter = Html::activeDropDownList($searchModel, 'client_id', Act::find()->innerJoin('act_error', '`act_error`.`act_id` = `act`.`id`')->innerJoin('company', '`company`.`id` = `act`.`client_id`')->where(['AND', ['act.service_type' => Yii::$app->request->get('type')], ['act_error.error_type' => 20], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $searchModel->period]])->select('company.name')->indexBy('client_id')->column(), ['class' => 'form-control', 'prompt' => 'Все клиенты']);
} elseif((Yii::$app->controller->action->id == 'double') && (Yii::$app->controller->id == 'error')) {
    $partnerFilter = Html::activeDropDownList($searchModel, 'partner_id', Act::find()->innerJoin('act_error', '`act_error`.`act_id` = `act`.`id`')->innerJoin('company', '`company`.`id` = `act`.`partner_id`')->where(['AND', ['act.service_type' => Yii::$app->request->get('type')], ['act_error.error_type' => 21], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $searchModel->period]])->select('company.name')->indexBy('partner_id')->column(), ['class' => 'form-control', 'prompt' => 'Все партнеры']);
    $clientFilter = Html::activeDropDownList($searchModel, 'client_id', Act::find()->innerJoin('act_error', '`act_error`.`act_id` = `act`.`id`')->innerJoin('company', '`company`.`id` = `act`.`client_id`')->where(['AND', ['act.service_type' => Yii::$app->request->get('type')], ['act_error.error_type' => 21], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $searchModel->period]])->select('company.name')->indexBy('client_id')->column(), ['class' => 'form-control', 'prompt' => 'Все клиенты']);
}

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
        'contentOptions' => function ($data) {
            if ($data->hasError(Act::ERROR_LOST)) return ['class' => 'text-danger'];
        },
    ],
    [
        'attribute' => 'partner_id',
        'filter' => $partnerFilter,
        'value' => function ($data) {
            return isset($data->partner) ? $data->partner->name : '';
        },
    ],
    [
        'attribute' => 'client_id',
        'filter' => $clientFilter,
        'value' => function ($data) {
            return isset($data->client) ? $data->client->name : '';
        },
    ],
    [
        'attribute' => 'card_number',
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
        'attribute' => 'car_number',
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
        'contentOptions' => ['style' => 'width: 100px;'],
    ],
    [
        'header' => 'Расход',
        'attribute' => 'expense',
        'pageSummary' => true,
        'contentOptions' => function($data) {
            if($data->hasError('expense')) return ['class' => 'text-danger'];
        },
        'value' => function ($data) {

            $intVal = (Int) $data->expense;
            $checkVal = $data->expense - $intVal;

            if($checkVal > 0) {
                return $data->expense;
            } else {
                return $intVal;
            }

        }
    ],
    [
        'header' => 'Приход',
        'attribute' => 'income',
        'pageSummary' => true,
        'contentOptions' => function($data) {
            if($data->hasError('income')) return ['class' => 'text-danger'];
        },
        'value' => function ($data) {

            $intVal = (Int) $data->income;
            $checkVal = $data->income - $intVal;

            if($checkVal > 0) {
                return $data->income;
            } else {
                return $intVal;
            }

        }
    ],
    [
        'attribute' => 'check',
        'value' => function ($data) {
            $imageLink = $data->getImageLink();
            if ($data->check && $imageLink) {
                return Html::a($data->check, $imageLink, ['class' => 'preview']);
            }
            return 'error';
        },
        'format' => 'raw',
        'visible' => $searchModel->service_type == Service::TYPE_WASH,
        'contentOptions' => function ($data) {
            if ($data->hasError('check')) {
                return ['class' => 'text-danger'];
            }
        },
    ],
    [
        'header' => 'Сохранить',
        'format' => 'raw',
        'contentOptions' => ['class' => 'text-center kv-align-middle'],
        'value' => function ($data) {
            return '<input type="checkbox" class="resave" data-id="' . $data->id . '">';
        },
        //'visible' => Yii::$app->user->identity->role != \common\models\User::ROLE_ADMIN,
    ],
    [
        'header'         => '',
        'class'          => 'kartik\grid\ActionColumn',
        'template'       => '{update}{delete}',
        'contentOptions' => ['style' => 'min-width: 85px'],
        'buttons'        => [
            'delete' => function ($url, $data, $key) {

                if(Yii::$app->controller->action->id == 'losses') {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', [
                        'dellosses',
                        'id' => $data->id,
                    ], [
                        'data-confirm' => "Вы уверены, что хотите удалить этот элемент?"
                    ]);
                } elseif(Yii::$app->controller->action->id == 'async') {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', [
                        'delasync',
                        'id' => $data->id,
                    ], [
                        'data-confirm' => "Вы уверены, что хотите удалить этот элемент?"
                    ]);
                } elseif(Yii::$app->controller->action->id == 'double') {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', [
                        'deldouble',
                        'id' => $data->id,
                    ], [
                        'data-confirm' => "Вы уверены, что хотите удалить этот элемент?"
                    ]);
                } else {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', [
                        'delete',
                        'id' => $data->id,
                    ], [
                        'data-confirm' => "Вы уверены, что хотите удалить этот элемент?"
                    ]);
                }

            },

        ],
        'visible' => $admin,
    ],
];

$buttons = 'Период: ' . DatePicker::widget([
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
    ]);;

$buttons .= '<span class="btn btn-warning btn-sm doResave" style="float:right;">Пересохранить выделенные</span>';
$buttons .= '<span class="btn btn-success btn-sm UnResave" style="float:right;">Снять выделение</span>';
$buttons .= '<span class="btn btn-danger btn-sm AllResave" style="float:right;">Выделить все</span>';

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'summary' => false,
    'emptyText' => '',
    'floatHeader' => false,
    'resizableColumns' => false,
    'floatHeaderOptions' => ['top' => '0'],
    'filterSelector' => '.ext-filter',
    'panel' => [
        'type' => 'primary',
        'heading' => (Yii::$app->controller->action->id == 'losses') ? 'Убыточные акты' : ((Yii::$app->controller->action->id == 'async') ? 'Асинхронные акты' : ((Yii::$app->controller->action->id == 'double') ? 'Задвоенные акты' : 'Ошибочные акты')),
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
                    'content' => $buttons,
                    'options' => [
                        'style' => 'vertical-align: middle',
                        'colspan' => count($columns),
                        'class' => 'kv-grid-group-filter',
                    ],
                ]
            ],
            'options' => ['class' => 'extend-header'],
        ],
        [
            'columns' => [
                [
                    'content' => '&nbsp',
                    'options' => [
                        'colspan' => count($columns),
                    ]
                ]
            ],
            'options' => ['class' => 'kv-group-header'],
        ],
    ],
    'columns' => $columns,
]);