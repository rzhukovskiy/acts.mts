<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use common\models\Company;
use common\models\TenderLists;
use common\models\TenderControl;
use yii\helpers\Url;

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
  
  var formatSum10 = $('td[data-col-seq="10"]');
  $(formatSum10).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
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
  
  var formatSum10a = $('.kv-page-summary-container td:eq(10)');
  $(formatSum10a).each(function (id, value) {
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
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', ['company/newcontroltender'], ['class' => 'btn btn-success btn-sm']) ?>
        </div>
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
                    'attribute' => 'id',
                    'format' => 'raw',
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

                        if ($data->user_id) {
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
                    'header' => 'Отправили',
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
                    'attribute' => 'return',
                    'vAlign'=>'middle',
                    'header' => 'Вернули',
                    'contentOptions' => ['style' => 'min-width: 100px'],
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_SUM,
                    'value' => function ($data) {

                        if ($data->return) {
                            return $data->return;
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'date_return',
                    'vAlign'=>'middle',
                    'filter' => false,
                    'header' => 'Дата возврата',
                    'value' => function ($data) {

                        if ($data->date_return) {
                            return date('d.m.Y', $data->date_return);
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'vAlign'=>'middle',
                    'header' => 'Возвратные',
                    'contentOptions' => ['style' => 'min-width: 100px'],
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_SUM,
                    'value' => function ($data) {

            if($data->payment_status == 1 || $data->payment_status == 2) {
                if ($data->send || $data->return) {
                    return $data->send - $data->return;
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

                            if ($data->send) {
                                return $data->send;
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
                    'template' => '{update}',
                    'contentOptions' => ['style' => 'min-width: 60px'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                ['/company/fullcontroltender', 'id' => $model->id]);
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
