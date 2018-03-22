<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use \common\models\Informing;
use yii\bootstrap\Tabs;
use yii\helpers\Url;
use \common\models\User;
use yii\bootstrap\Modal;

$this->title = 'Информирование';

$changestatus = Url::to('@web/informing/change-status');
$isarchive = Url::to('@web/informing/isarchive');
$fullText = Url::to('@web/informing/fulltext');
$fullUsers = Url::to('@web/informing/fullusers');

$script = <<< JS

var td3 = $('td[data-col-seq="3"]');
  $(td3).each(function (id, value) {
      var percent = (parseInt($(this).text())/(parseInt($(this).next('td').text()) + parseInt($(this).text()))) * 100;
      $(this).next('td').next('td').text(percent.toFixed(2) + ' %');
});

$('.change-agree_status').each(function (id, value) {
       var thisId = $(this);
       if (thisId.data('agreestatus') == 1) {
          thisId.parent('td').addClass("btn-success");
          thisId.val('1');
       } else {
          thisId.parent('td').addClass("btn-danger");
          thisId.val('0');
       }
       
});

    $(function() {
        $('.change-agree_status').change(function(){
          var thisId = $(this);
          var informingID = thisId.data('id');
          var informingVal = thisId.val();
          
          $.ajax({
                type     :'POST',
                cache    : true,
                data: 'id=' + informingID,
                url  : '$changestatus',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                thisId.parent('td').removeClass();
                 if (informingVal == 1) {
                      thisId.parent('td').addClass("btn-success");
                 } else {
                      thisId.parent('td').addClass("btn-danger");
                 }
                
                } else {
                // Неудачно
                }
                
                }
                });
     
        });
    });
    

    
    // Клик отправить в архив
$('.glyphicon-floppy-save').on('click', function(){
    var checkIsArchive = confirm("Вы уверены, что хотите перенести в архив?");
    idKds = $(this).data('id');
    
    if(checkIsArchive == true) { 
       $.ajax({
                type     :'POST',
                cache    : true,
                data: 'id=' + idKds,
                url  : '$isarchive',
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
});
    // Открываем список сотрудников
    $('.glyphicon-eye-open').on('click', function(){
    $('#showFullUsers').modal('show');
    id = $(this).data('id');
               $.ajax({
                type     :'POST',
                cache    : true,
                data: 'id=' + id,
                url  : '$fullUsers',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                var usersList = $.parseJSON(response.result);
                
                 $('.place_listUsers').html('<table>' + usersList + '</table>');
                
                } else {
                // Неудачно
                }
                
                }
                });
    });
    
    // Открываем полный текст
    $('.glyphicon-search').on('click', function(){
    $('#showFullText').modal('show');
    id = $(this).data('id');
           $.ajax({
                type     :'POST',
                cache    : true,
                data: 'id=' + id,
                url  : '$fullText',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                var text = $.parseJSON(response.result);
                
                 $('.place_listText').html(text);
                } else {
                // Неудачно
                }
                
                }
                });
           
    });
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

$css = "
.glyphicon-floppy-save:hover {
cursor:pointer;
}
.glyphicon-search:hover {
cursor:pointer;
}
.glyphicon-eye-open:hover {
cursor:pointer;
}
.modal {
    overflow-y: auto;
    font-size:16px;
}
.modal-sm {
width: 500px;
}
.modal-lg {
width: 700px;
}
";
$this->registerCss($css);

$GLOBALS['usersList'] = $userLists;
$GLOBALS['informingUsers'] = $informingUsers;
$GLOBALS['allCount'] = $allCount;
$GLOBALS['allCountAgree'] = $allCountAgree;

$tabs = [
    ['label' => 'Активные', 'url' => ['informing/list?type=1'], 'active' => $type == 1],
    ['label' => 'Архив', 'url' => ['informing/list?type=2'], 'active' => $type == 2],
];

echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $tabs,
]);

$column = [

    [
        'header' => '№',
        'vAlign'=>'middle',
        'class' => 'kartik\grid\SerialColumn'
    ],
    [
        'attribute' => 'text',
        'contentOptions' => ['style' => 'min-width: 300px'],
        'format'    => 'raw',
        'value'     => function ($data) {
            return Editable::widget([
                'model'           => $data,
                'placement'       => PopoverX::ALIGN_RIGHT,
                'inputType'       => Editable::INPUT_TEXTAREA,
                'formOptions'     => [
                    'action' => ['/informing/update', 'id' => $data->id]
                ],
                'valueIfNull'     => '(не задано)',
                'buttonsTemplate' => '{submit}',
                'displayValue'    => mb_substr(nl2br($data->text), 0, 300) . (mb_strlen($data->text) > 300 ? ('.........') : ''),
                'disabled'        => (($data->is_archive == 0) && (Yii::$app->user->identity->id == $data->from_user || Yii::$app->user->identity->role == User::ROLE_ADMIN)) ? false : true,
                'submitButton'    => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute'       => 'text',
                'asPopover'       => true,
                'size'            => 'lg',
                'options'         => [
                    'class'       => 'form-control',
                    'id'          => 'text' . $data->id,
                ],
            ]);
        },
    ],
    [
        'attribute' => 'from_user',
        'contentOptions' => ['style' => 'max-width: 200px'],
        'filter' => false,
        'vAlign'=>'middle',
        'value' => function ($data) {

            if ($GLOBALS['usersList'][$data->from_user]) {
                return $GLOBALS['usersList'][$data->from_user];
            } else {
                return '-';
            }

        },
    ],
    [
        'header' => 'Да',
        'contentOptions' => ['style' => 'width: 80px'],
        'filter' => false,
        'vAlign'=>'middle',
        'value' => function ($data) {

            return isset($GLOBALS['allCountAgree'][$data->id]) ? $GLOBALS['allCountAgree'][$data->id] : 0;

        },
    ],
    [
        'header' => 'Нет',
        'contentOptions' => ['style' => 'width: 80px'],
        'vAlign'=>'middle',
        'value' => function ($data) {
                $count = 0;
                if (isset($GLOBALS['allCountAgree'][$data->id])) {
                    $count = $GLOBALS['allCountAgree'][$data->id];
                }
                return $GLOBALS['allCount'][$data->id] - $count;

        },
    ],
    [
        'header' => '%',
        'contentOptions' => ['style' => 'width: 80px'],
        'vAlign'=>'middle',
        'value' => function ($data) {

            return '-';

        },
    ],
    [
        'header' => 'Статус',
        'contentOptions' => ['style' => 'width: 180px; vertical-align:middle'],
        'filter' => false,
        'format' => 'raw',
        'vAlign' =>'middle',
        'visible' => ($type == 1) ? true : false,
        'value' => function ($data, $key, $index, $column) {

        $status = [];
        for ($i = 0; $i < count($GLOBALS['informingUsers']); $i++) {
            if (($GLOBALS['informingUsers'][$i]['informing_id'] == $data->id) && ($GLOBALS['informingUsers'][$i]['user_id'] == Yii::$app->user->identity->id)) {
                $id = $GLOBALS['informingUsers'][$i]['informing_id'];
                $status[$id] = $GLOBALS['informingUsers'][$i]['status'];
            }
        }
            $GLOBALS['status'] = $status;
        if ($data->from_user != Yii::$app->user->identity->id) {
            return Html::dropDownList("status", 'status', Informing::$agreeStatus, [
                'class'              => 'form-control change-agree_status',
                'data-id'            => $data->id,
                'data-agreeStatus'   => isset($GLOBALS['status'][$data->id]) ? $GLOBALS['status'][$data->id] : '0',
            ]);
        } else {
            return '-';
        }
        },

    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'header' => 'Действие',
        'vAlign'=>'middle',
        'template' => '{fullusers}{fullsearch}{archive}',
        'contentOptions' => ['style' => 'max-width: 100px'],
        'buttons' => [
            'fullusers' => function ($url, $data, $key) {
                return '<span class="glyphicon glyphicon-eye-open" style="font-size: 18px;" data-id="' . $data->id . '"></span>';
            },

            'fullsearch' => function ($url, $data, $key) {
            return (mb_strlen($data->text) > 300 ? ('<span class="glyphicon glyphicon-search" data-id="' . $data->id . '"></span>') : '');
            },
            'archive' => function ($url, $data, $key) {
            if (Yii::$app->user->identity->role == User::ROLE_ADMIN || Yii::$app->user->identity->id == $data->from_user) {
            return ($data->is_archive == 0 ? ('<span class="glyphicon glyphicon-floppy-save" style="font-size: 16px;padding-left:7px;" data-id="' . $data->id . '"></span>') : '');
            }
            },
        ],
    ],
];

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Информирование сотрудников
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', ['informing/create'], ['class' => 'btn btn-success btn-sm']) ?>
        </div>
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
            'columns' => $column,
        ]);
        ?>
    </div>
</div>

<?php
// Модальное окно полного текста
$modalListsName = Modal::begin([
    'header' => '<h5>Полный текст</h5>',
    'id' => 'showFullText',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
    'size'=>'modal-lg',
]);


echo "<div class='place_listText' style='margin-left:15px; margin-right:15px;'></div>";

Modal::end();
// Модальное окно полного текста

// Модальное окно списка сотрудников
$modalListsName = Modal::begin([
    'header' => '<h5>Список сотрудников</h5>',
    'id' => 'showFullUsers',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
    'size'=>'modal-sm',
]);


echo "<div class='place_listUsers' style='margin-left:15px; margin-right:15px;' align='center'></div>";

Modal::end();
// Модальное окно списка сотрудников
?>