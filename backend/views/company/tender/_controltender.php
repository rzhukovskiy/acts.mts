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
$controlisarchive = Url::to('@web/company/controlisarchive');

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

var idKds = 0;
// Клик отправить в архив
$('.glyphicon-floppy-save').on('click', function(){
    var checkIsArchive = confirm("Вы уверены, что хотите перенести в архив?");
    idKds = $(this).data('id');
    
    if(checkIsArchive == true) { 
       sendIsArchive();
    }
});

// Клик отправить из архив
$('.glyphicon-floppy-open').on('click', function(){
    var checkIsArchive = confirm("Вы уверены, что хотите перенести в активные?");
    idKds = $(this).data('id');
    
    if(checkIsArchive == true) {
      sendIsArchive();
     } 
});

function sendIsArchive() {
          $.ajax({
                type     :'POST',
                cache    : true,
                data: 'id=' + idKds,
                url  : '$controlisarchive',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                $('tr[data-key="' + idKds + '"]').hide();
                
                } else {
                // Неудачно
                }
                
                }
                });
}
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

$css = "
.glyphicon-floppy-save:hover {
cursor:pointer;
}
.glyphicon-floppy-open:hover {
cursor:pointer;
}
";
$this->registerCss($css);

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

/**
 * Виджет выбора диапазона дат
 */

$halfs = [
    '1е полугодие',
    '2е полугодие'
];
$quarters = [
    '1й квартал',
    '2й квартал',
    '3й квартал',
    '4й квартал',
];
$months = [
    'январь',
    'февраль',
    'март',
    'апрель',
    'май',
    'июнь',
    'июль',
    'август',
    'сентябрь',
    'октябрь',
    'ноябрь',
    'декабрь',
];

$ts1 = strtotime($searchModel->dateFrom);
$ts2 = strtotime($searchModel->dateTo);

$year1 = date('Y', $ts1);
$year2 = date('Y', $ts2);

$month1 = date('m', $ts1);
$month2 = date('m', $ts2);

$diff = (($year2 - $year1) * 12) + ($month2 - $month1);
switch ($diff) {
    case 1:
        $period = 1;
        break;
    case 3:
        $period = 2;
        break;
    case 6:
        $period = 3;
        break;
    case 12:
        $period = 4;
        break;
    default:
        $period = 0;
}
$rangeYear = range(date('Y') - 10, date('Y'));
$currentYear = isset($searchModel->dateFrom)
    ? date('Y', strtotime($searchModel->dateFrom))
    : date('Y');

$currentMonth = isset($searchModel->dateFrom)
    ? date('n', strtotime($searchModel->dateFrom))
    : date('n');
$currentMonth--;

$filters = '';
$periodForm = '';
$periodForm .= Html::dropDownList('period', $period, \common\models\Tender::$periodList, [
    'class' =>'select-period form-control',
    'style' => 'margin-right: 10px;'
]);
$periodForm .= Html::dropDownList('month', $currentMonth, $months, [
    'id' => 'month',
    'class' => 'autoinput form-control',
    'style' => $diff == 1 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('half', $currentMonth < 5 ? 0 : 1, $halfs, [
    'id' => 'half',
    'class' => 'autoinput form-control',
    'style' => $diff == 6 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('quarter', floor($currentMonth / 3), $quarters, [
    'id' => 'quarter',
    'class' => 'autoinput form-control',
    'style' => $diff == 3 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('year', array_search($currentYear, $rangeYear), range(date('Y') - 10, date('Y')), [
    'id' => 'year',
    'class' => 'autoinput form-control',
    'style' => $diff && $diff <= 12 ? '' : 'display:none'
]);
$periodForm .= Html::activeTextInput($searchModel, 'dateFrom', ['class' => 'date-from ext-filter hidden']);
$periodForm .= Html::activeTextInput($searchModel, 'dateTo',  ['class' => 'date-to ext-filter hidden']);
$periodForm .= Html::submitButton('Показать', ['class' => 'btn btn-primary date-send', 'style' => 'margin-left: 10px;']);

$filters = 'Выбор периода: ' . $periodForm;

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
            'resizableColumns' => false,
            'showPageSummary' => true,
            'emptyText' => '',
            'layout' => '{items}',
            'filterSelector' => '.ext-filter',
            'beforeHeader' => [
                [
                    'columns' => [
                        [
                            'content' => $filters,
                            'options' => [
                                'style' => 'vertical-align: middle',
                                'colspan' => 15,
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
                                'colspan' => 15,
                            ]
                        ]
                    ],
                    'options' => ['class' => 'kv-group-header'],
                ],
            ],
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
                    'attribute' => 'site_address',
                    'filter' => Html::activeDropDownList($searchModel, 'site_address', isset($GLOBALS['arrLists'][8]) ? $GLOBALS['arrLists'][8] : [], ['class' => 'form-control', 'prompt' => 'Все площадки']),
                    'vAlign'=>'middle',
                    'header' => 'Площадка',
                    'value' => function ($data) {

                        if ($data->site_address) {
                            return $GLOBALS['arrLists'][8][$data->site_address];
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
                                return '<span class="glyphicon glyphicon-floppy-save" data-id="' . $data->id . '" style="margin-left: 5px; font-size: 16px;"></span>';
                            } else if (($data->is_archive == 1) && (Yii::$app->user->identity->role == User::ROLE_ADMIN)) {
                                return '<span class="glyphicon glyphicon-floppy-open" data-id="' . $data->id . '" style="margin-left: 5px; font-size: 16px;"></span>';
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
