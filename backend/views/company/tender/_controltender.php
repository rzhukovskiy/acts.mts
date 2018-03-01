<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use common\models\Company;
use common\models\TenderLists;
use common\models\TenderControl;
use yii\helpers\Url;
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use common\models\User;

$isAdmin = (\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN) ? 1 : 0;
$ajaxpaymentstatus = Url::to('@web/company/ajaxpaymentstatus');

$script = <<< JS
// формат числа
window.onload=function(){
  var formatSum8 = $('td[data-col-seq="8"]');
  $(formatSum8).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  var nomber = 0;
  var formatSum10 = $('td[data-col-seq="10"] .kv-editable .kv-editable-value');
  $(formatSum10).each(function (id, value) {
      var thisId = $(this);
       if(!isNaN(parseFloat($(this).text()))) {
       nomber += parseFloat($(this).text());
       }
      thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  $('.kv-page-summary-container td:eq(10)').text(nomber.toFixed(2).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
      
  var formatSum12 = $('td[data-col-seq="12"]');
  $(formatSum12).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum13 = $('td[data-col-seq="13"]');
  $(formatSum13).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum8a = $('.kv-page-summary-container td:eq(8)');
  $(formatSum8a).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  

  
    var formatSum12a = $('.kv-page-summary-container td:eq(12)');
  $(formatSum12a).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum13a = $('.kv-page-summary-container td:eq(13)');
  $(formatSum13a).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
};
$('.change-payment_status').change(function(){
       
     var select=$(this);
        $.ajax({
            url: '$ajaxpaymentstatus',
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
JS;
$this->registerJs($script, \yii\web\View::POS_READY);
// массив списков
$arrayTenderList = TenderLists::find()->select('id, description, type')->orderBy('type, id')->asArray()->all();

$arrLists = [];
$oldType = -1;
$tmpArray = [];

for ($i = 0; $i < count($arrayTenderList); $i++) {

    if($arrayTenderList[$i]['type'] == $oldType) {

        $index = $arrayTenderList[$i]['id'];
        $tmpArray[$index] = $arrayTenderList[$i]['description'];

    } else {

        if($i > 0) {

            $arrLists[$oldType] = $tmpArray;
            $tmpArray = [];

            $oldType = $arrayTenderList[$i]['type'];

            $index = $arrayTenderList[$i]['id'];
            $tmpArray[$index] = $arrayTenderList[$i]['description'];

        } else {
            $oldType = $arrayTenderList[$i]['type'];
            $tmpArray = [];

            $index = $arrayTenderList[$i]['id'];
            $tmpArray[$index] = $arrayTenderList[$i]['description'];
        }
    }

    if(($i + 1) == count($arrayTenderList)) {
        $arrLists[$oldType] = $tmpArray;
    }

}
//
$GLOBALS['arrLists'] = $arrLists;
$GLOBALS['usersList'] = $usersList;

$arrsite = [];
if (isset($arrLists[8])){
    $arrsite = $arrLists[8];
    asort($arrsite);
}
$arrtype = [];
if (isset($arrLists[9])){
    $arrtype = $arrLists[9];
    asort($arrtype);
}
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Контроль денежных средств
    </div>
    <div class="panel-body">
        <?php

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'showPageSummary' => true,
            'emptyText' => '',
            'layout' => '{items}',
            'columns' => [
                [
                    'header' => '№',
                    'vAlign'=>'middle',
                    'class' => 'kartik\grid\SerialColumn'
                ],
                [
                    'header' => 'ID',
                    'attribute' => 'tender_id',
                    'vAlign'=>'middle',
                ],
                [
                    'attribute' => 'user_id',
                    'header' => 'Сотрудник',
                    'filter' => Html::activeDropDownList($searchModel, 'user_id', isset($GLOBALS['usersList']) ? $GLOBALS['usersList'] : [], ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
                    'pageSummary' => 'Всего',
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'value' => function ($data) {

                        if (isset($GLOBALS['usersList'][$data->user_id])) {
                            return $GLOBALS['usersList'][$data->user_id];
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'attribute' => 'date_send',
                    'vAlign'=>'middle',
                    'filter' => false,
                    'header' => 'Дата отправки',
                    'value' => function ($data) {

                        if ($data->date_send) {
                            return date('d.m.Y', $data->date_send);
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'platform',
                    'vAlign'=>'middle',
                    'header' => 'Площадка',
                    'value' => function ($data) {

                        if ($data->platform) {
                            return $data->platform;
                        } else {
                            return '-';
                        }

                    },
                ],

                [
                    'attribute' => 'customer',
                    'header' => 'Заказчик',
                    'format' => 'raw',
                    'contentOptions' => ['style' => 'max-width: 300px'],
                    'vAlign'=>'middle',
                    'value' => function ($data) {

                        if ($data->customer) {
                            return $data->customer;
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'purchase',
                    'vAlign'=>'middle',
                    'header' => 'Закупка',
                    'contentOptions' => ['style' => 'max-width: 500px'],
                    'value' => function ($data) {

                        if ($data->purchase) {
                            return $data->purchase;
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'type_payment',
                    'header' => 'Тип платежа',
                    'filter' => Html::activeDropDownList($searchModel, 'type_payment', isset($arrtype) ? $arrtype : [], ['class' => 'form-control', 'prompt' => 'Все типы']),
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'value' => function ($data) {

                        if ($data->type_payment) {
                            return $GLOBALS['arrLists'][9][$data->type_payment];
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'send',
                    'header' => 'Мы отправили',
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_SUM,
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'contentOptions' => ['style' => 'min-width: 100px'],
                    'value' => function ($data) {

                        if ($data->send) {
                            return $data->send;
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'payment_status',
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'value' => function ($model, $key, $index, $column) {
                        return Html::activeDropDownList($model, 'payment_status', TenderControl::$paymentStatus,
                            [
                                'class'              => 'form-control change-payment_status',
                                'data-id'            => $model->id,
                                'data-paymentStatus' => $model->payment_status,
                                'disabled'           => TenderControl::payDis($model->payment_status) ? 'disabled' : false,
                            ]

                        );
                    },

                    'contentOptions' => function ($model) {
                        return [
                            'class' => TenderControl::colorForPaymentStatus($model->payment_status),
                            'style' => 'min-width: 50px',
                        ];
                    },
                ],
                [
                    'attribute' => 'tender_return',
                    'format'    => 'raw',
                    'contentOptions' => [
                            'style' => 'min-width: 100px',
                            ],
                    'value'     => function ($data) {
                        return Editable::widget([
                            'model'           => $data,
                            'placement'       => PopoverX::ALIGN_LEFT,
                            'formOptions'     => [
                                'action' => ['/company/updatecontroltender', 'id' => $data->id]
                            ],
                            'valueIfNull'     => '(не задано)',
                            'buttonsTemplate' => '{submit}',
                            'displayValue' => isset($data->tender_return) ? $data->tender_return : '',
                            'disabled'        => ((\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN || Yii::$app->user->identity->id == 708) && $data->is_archive == 0 ) ? false : true,
                            'submitButton'    => [
                                'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                            ],
                            'attribute'       => 'tender_return',
                            'asPopover'       => true,
                            'size'            => 'md',
                            'options'         => [
                                'class'       => 'form-control',
                                'placeholder' => 'Введите сумму возврата',
                                'id'          => 'tender_return' . $data->id,
                                'value'       => $data->tender_return
                            ],
                        ]);
                    },
                ],
                [
                    'attribute' => 'date_return',
                    'format' => 'raw',
                    'value'     => function ($data) {
                        return Editable::widget([
                            'name'            => 'date_return',
                            'placement'       => PopoverX::ALIGN_LEFT,
                            'inputType'       => Editable::INPUT_DATE,
                            'asPopover'       => true,
                            'value'           => ($data->date_return) ? date('d.m.Y', $data->date_return) : '',
                            'disabled'        => $data->is_archive == 1 ? true : false,
                            'valueIfNull'     => '(не задано)',
                            'buttonsTemplate' => '{submit}',
                            'submitButton'    => [
                                'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                            ],
                            'size'            => 'md',
                            'formOptions'     => [
                                'action'      => ['/company/updatecontroltender', 'id' => $data->id]
                            ],
                            'options'         => [
                                'class'         => 'form-control',
                                'id'            => 'date_return' . $data->id,
                                'removeButton'  => false,
                                'pluginOptions' => [
                                    'format'         => 'dd.mm.yyyy',
                                    'autoclose'      => true,
                                    'pickerPosition' => 'bottom-right',
                                ],
                                'options' => ['value' => ($data->date_return) ? date('d.m.Y', $data->date_return) : '']
                            ],
                        ]);
                    },
                ],
                [
                    'vAlign'=>'middle',
                    'header' => 'Нам должны вернуть',
                    'contentOptions' => ['style' => 'min-width: 100px'],
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_SUM,
                    'value' => function ($data) {

            if($data->payment_status == 1 || $data->payment_status == 2) {
                if ($data->send || $data->tender_return) {
                    return $data->send - $data->tender_return;
                } else {
                    return '-';
                }
                     } else {
                return '-';
            }
                    },
                ],
                [
                    'vAlign'=>'middle',
                    'header' => 'Невозвратные',
                    'contentOptions' => ['style' => 'min-width: 100px'],
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_SUM,
                    'value' => function ($data) {

                        if($data->payment_status == 0) {
                            if ($data->send || $data->tender_return) {
                                return $data->send - $data->tender_return;
                            } else {
                                return '-';
                            }
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'class' => 'kartik\grid\ActionColumn',
                    'header' => 'Действие',
                    'vAlign'=>'middle',
                    'template' => '{update}{archive}',
                    'contentOptions' => ['style' => 'min-width: 60px'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                        if (isset($model->tender_id)) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                ['/company/fulltender', 'tender_id' => $model->tender_id]);
                            } else {
                            return '';
                        }
                        },
                        'archive' => function ($url, $data, $key) {
                            if ($data->is_archive == 0) {
                                return Html::a('<span class="glyphicon glyphicon-floppy-save" style="margin-left: 5px; font-size: 16px;"> </span>', ['/company/controlisarchive', 'id' => $data->id],
                                    ['data-confirm' => "Вы уверены, что хотите перенести в архив?"]);
                            } else if (($data->is_archive == 1) && (Yii::$app->user->identity->role == User::ROLE_ADMIN)) {
                                return Html::a('<span class="glyphicon glyphicon-floppy-open" style="margin-left: 5px; font-size: 16px;"> </span>', ['/company/controlisarchive', 'id' => $data->id, 'is_archive' => $data->is_archive],
                                    ['data-confirm' => "Вы уверены, что хотите перенести в активные?"]);
                            } else {
                                return '';
                            }
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
