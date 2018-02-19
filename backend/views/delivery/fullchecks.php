<?php

use yii\bootstrap\Tabs;
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\helpers\Url;

$actionActCount = Url::to('@web/delivery/actcount');
$script = <<< JS
// считаем общее количетво по 4 столбцу
  var oldvalue = 0;
  var summ = 0;
  var formatSum5 = $('td[data-col-seq="5"]');
  $(formatSum5).each(function (id, value) {
       summ = parseInt($(this).text()) + oldvalue;
       oldvalue = parseInt($(this).text());
});
  
// записываем самую маленькую дату из всех
  var olddate = 0;
  var date = 0;
  var convert = '';
  var formatSum3 = $('td[data-col-seq="3"] .kv-editable .kv-editable-value');
  $(formatSum3).each(function (id, value) {
      var thisid = $(this).text();
      thisid = thisid.split('.');
      convert = thisid[1] + '.' + thisid[0] + '.' + thisid[2];
      var unixtime = Date.parse(convert)/1000;
      
       if ((olddate > parseInt(unixtime)) || (olddate == 0)) {
           olddate = parseInt(unixtime);
       }  
  
});
  sendActCount();
  function sendActCount() {
          $.ajax({
                type     :'POST',
                cache    : true,
                data: 'company_id=' + '$company_id' + '&date=' + olddate,
                url  : '$actionActCount',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                if ((summ - response.result) > 50) {
                 $('.kv-grid-group-filter').html("<span style='color:#2d6f31;'>Оставшееся количество чеков: " + (summ - response.result) + "</span>");   
                } else {
                 $('.kv-grid-group-filter').html("<span style='color:#8e3532;'>Оставшееся количество чеков: " + (summ - response.result) + "</span>");
                }
                
                } else {
                // Неудачно
                }
                
                }
                });
}
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

$this->title = 'Отправка чеков';

$GLOBALS['companyWash'] = $companyWash;
$GLOBALS['usersList'] = $usersList;

echo Tabs::widget([
    'items' => [
        ['label' => 'Список', 'url' => ['delivery/listchecks']],
        ['label' => $GLOBALS['companyWash'][$company_id], 'url' => ['delivery/fullchecks'], 'active' => Yii::$app->controller->action->id == 'fullchecks'],
    ],
]);


$column = [
    [
        'attribute' => 'company_id',
        'content' => function ($data) {

            if (isset($GLOBALS['companyWash'][$data->company_id])) {
                return $GLOBALS['companyWash'][$data->company_id];
            } else {
                return '-';
            }
        },
        'group' => true,
        'groupedRow' => true,
        'groupOddCssClass' => 'kv-group-header',
        'groupEvenCssClass' => 'kv-group-header',
    ],
    [
        'header' => '№',
        'vAlign'=>'middle',
        'class' => 'kartik\grid\SerialColumn'
    ],
    [
        'attribute' => 'user_id',
        'filter' => false,
        'vAlign'=>'middle',
        'value' => function ($data) {

            if ($GLOBALS['usersList'][$data->user_id]) {
                return $GLOBALS['usersList'][$data->user_id];
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'date_send',
        'format' => 'raw',
        'value'     => function ($data) {
            return Editable::widget([
                'name'            => 'date_send',
                'placement'       => PopoverX::ALIGN_LEFT,
                'inputType'       => Editable::INPUT_DATE,
                'asPopover'       => true,
                'value'           => ($data->date_send) ? date('d.m.Y', $data->date_send) : '-',
                'valueIfNull'     => '(не задано)',
                'buttonsTemplate' => '{submit}',
                'submitButton'    => [
                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'size'            => 'md',
                'formOptions'     => [
                    'action'      => ['/delivery/updatechecks', 'id' => $data->id]
                ],
                'options'         => [
                    'class'         => 'form-control',
                    'id'            => 'date_send' . $data->id,
                    'removeButton'  => false,
                    'pluginOptions' => [
                        'format'         => 'dd.mm.yyyy',
                        'autoclose'      => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options' => ['value' => ($data->date_send) ? date('d.m.Y', $data->date_send) : '']
                ],
            ]);

        },
    ],
    [
        'attribute' => 'serial_number',
        'format'    => 'raw',
        'value'     => function ($data) {
            return Editable::widget([
                'model'           => $data,
                'placement'       => PopoverX::ALIGN_LEFT,
                'inputType'       => Editable::INPUT_TEXT,
                'formOptions'     => [
                    'action'      => ['/delivery/updatechecks', 'id' => $data->id]
                ],
                'displayValue'    => isset($data->serial_number) ? $data->serial_number : '',
                'valueIfNull'     => '(не задано)',
                'buttonsTemplate' => '{submit}',
                'submitButton'    => [
                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute'       => 'serial_number',
                'asPopover'       => true,
                'size'            => 'md',
                'options'         => [
                    'class'       => 'form-control',
                    'placeholder' => 'Например: 900-999',
                    'id'          => 'serial_number' . $data->id,
                    'value'       => $data->serial_number
                ],
            ]);
        },
    ],
    [
        'header' => 'Количество',
        'vAlign'=>'middle',
        'value' => function ($data) {

            if ($data->serial_number) {
                $serial_number = str_replace(' ', '', $data->serial_number);
                $serial_number = explode('-', $serial_number);
                return $serial_number[1]-$serial_number[0];
            } else {
                return '-';
            }

        },
    ],
];

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        История отправки чеков
    </div>
    <div class="panel-body">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'emptyText' => '',
            'layout' => '{items}',
            'beforeHeader' => [
                [
                    'columns' => [
                        [

                            'options' => [
                                'style' => 'vertical-align: middle;',
                                'colspan' => count($column),
                                'class' => 'kv-grid-group-filter',
                            ],
                        ]
                    ],
                    'options' => ['class' => 'extend-header'],
                ],

            ],
            'columns' => $column,
        ]);
        ?>
    </div>
</div>
