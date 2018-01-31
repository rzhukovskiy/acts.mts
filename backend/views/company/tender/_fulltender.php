<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\helpers\Html;
use yii\web\View;
use common\models\Company;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use common\models\TenderLists;
use common\models\User;
use common\models\TenderControl;
use kartik\datetime\DateTimePicker;
use \kartik\date\DatePicker;
use kartik\grid\GridView;

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

// сортировка
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

$actionGetListItems = Url::to('@web/company/listitems');
$actionSaveNewItem = Url::to('@web/company/newitemlist');
$actionDelItem = Url::to('@web/company/deleteitemlist');
$actionEditItem = Url::to('@web/company/edititemlist');
$actionLinkCloseDownload = Url::to('@web/company/closedownload');

$isAdmin = (\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN) ? 1 : 0;
$ajaxpaymentstatus = Url::to('@web/company/ajaxpaymentstatus');

$tender_id = $model->id;
$tender_close = $model->tender_close;

$script = <<< JS



// Клик закрыть загрузку в тендере
$('.closeTender').on('click', function(){
    var checkCloseDownload = confirm("Вы уверены что хотите закрыть загрузку?");
    
    if(checkCloseDownload == true) {     
       sendCloseDownload();
    }
});
$('.openTender').on('click', function(){
    var checkCloseDefault = confirm("Вы уверены что хотите открыть загрузку?");
  
    if(checkCloseDefault == true) {
      sendCloseDownload();
     } 
});

function sendCloseDownload() {
          $.ajax({
                type     :'POST',
                cache    : true,
                data: 'tender_id=' + '$tender_id' + '&tender_close=' + '$tender_close',
                url  : '$actionLinkCloseDownload',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                location.reload();
                
                } else {
                // Неудачно
                }
                
                }
                });
}

// открываем модальное окно добавить вложения
$('.showFormAttachButt').on('click', function(){
$('#showFormAttach').modal('show');
});

$('#showFormAttach div[class="modal-dialog modal-lg"] div[class="modal-content"] div[class="modal-body"]').css('padding', '20px 0px 120px 25px');

// Скрыть иконку ссылки если поле ссылки пустое
    if(($('#tender-site-targ').text() == '') || ($('#tender-site-targ').text() == 'не задано')) {
        $('.glyphicon-new-window').hide();
    }
    // Скрыть иконку ссылки если поле ссылки пустое
    $('.glyphicon-new-window').css({'cursor':'pointer'});
    // Отобразить иконку ссылки если данное поле изменили
    var websiteOld = $('#tender-site-targ').text();
    $('#tender-site-targ').bind("DOMSubtreeModified",function() {
        var websiteNew = $('#tender-site-targ').text();
        if((websiteOld != websiteNew) && (websiteNew != '')) {
        websiteOld = websiteNew;
        
        if((websiteNew.length > 0) && (websiteNew != 'не задано')) {
        if(websiteNew.indexOf('http') + 1) {
            $('.glyphicon-new-window').show();
        }
        }
        
        } else {
          $('.glyphicon-new-window').hide();  
        }
    });
    // Отобразить иконку ссылки если данное поле изменили

    // Клик по ссылке website
    $('table tbody tr td').on('click', '.glyphicon-new-window', function() {
        var website = $('#tender-site-targ').text();
       
        if((website.length > 0) && (website != 'не задано')) {
        if(website.indexOf('http') + 1) {
            window.open(website, '_blank');
        }
        }
    });
    // Клик по ссылке website
    
    // Управление списками
var selectType = 0;
var selectID = 0;

// открываем модальное окно с названиями списков
$('.listSettings').on('click', function() {
$('#showListsName').modal('show');
});

// открываем модальное окно управления списками purchase_status
$('.purchase_status').on('click', function() {
    
$('#showListsName').modal('hide');
$('#showSettingsList').modal('show');

$('.settings_name').text('Управление списками: ' + $(this).text());

loadListsItems(0);

});

// открываем модальное окно управления списками method_purchase
$('.method_purchase').on('click', function() {
    
$('#showListsName').modal('hide');
$('#showSettingsList').modal('show');

$('.settings_name').text('Управление списками: ' + $(this).text());

loadListsItems(2);

});

// открываем модальное окно управления списками service_type
$('.service_type').on('click', function() {
    
$('#showListsName').modal('hide');
$('#showSettingsList').modal('show');

$('.settings_name').text('Управление списками: ' + $(this).text());

loadListsItems(3);

});

// открываем модальное окно управления списками federal_law
$('.federal_law').on('click', function() {
    
$('#showListsName').modal('hide');
$('#showSettingsList').modal('show');

$('.settings_name').text('Управление списками: ' + $(this).text());

loadListsItems(4);

});

// открываем модальное окно управления списками status_request_security
$('.status_request_security').on('click', function() {
    
$('#showListsName').modal('hide');
$('#showSettingsList').modal('show');

$('.settings_name').text('Управление списками: ' + $(this).text());

loadListsItems(6);

});

// открываем модальное окно управления списками status_contract_security
$('.status_contract_security').on('click', function() {
    
$('#showListsName').modal('hide');
$('#showSettingsList').modal('show');

$('.settings_name').text('Управление списками: ' + $(this).text());

loadListsItems(7);

});

// открываем модальное окно  site_address
$('.site_address').on('click', function() {
    
$('#showListsName').modal('hide');
$('#showSettingsList').modal('show');

$('.settings_name').text('Управление списками: ' + $(this).text());

loadListsItems(8);

});

// открываем модальное окно type_payment
$('.type_payment').on('click', function() {
    
$('#showListsName').modal('hide');
$('#showSettingsList').modal('show');

$('.settings_name').text('Управление списками: ' + $(this).text());

loadListsItems(9);

});

// Загружаем список по заданному типу
function loadListsItems(type) {
    
    selectType = 0;
    selectType = type;
    
    $('.place_list').html();
    
              $.ajax({
                type     :'POST',
                cache    : true,
                data: 'type=' + type,
                url  : '$actionGetListItems',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                // Очищаем текущий селект
                var selectObj;
                
                switch (type) {
                    case 0: {
                        selectObj = $("#tender-purchase_status");
                        break;
                    }
                    case 2: {
                        selectObj = $("#tender-method_purchase");
                        break;
                    }
                    case 3: {
                        selectObj = $("#tender-service_type");
                        break;
                    }
                    case 4: {
                        selectObj = $("#tender-federal_law");
                        break;
                    }
                    case 6: {
                        selectObj = $("#tender-status_request_security");
                        break;
                    }
                    case 7: {
                        selectObj = $("#tender-status_contract_security");
                        break;
                    }
                    case 8: {
                        selectObj = $("#tendercontrol-site_address");
                        break;
                    }
                    case 9: {
                        selectObj = $("#tendercontrol-type_payment");
                        break;
                    }
                }
                
                selectObj.empty();
                // Очищаем текущий селект
                    
                var itemsArr = jQuery.parseJSON(response.items);
                
                    // добавляем placeholder
                    if(type == 0) {
                        selectObj.append($("<option></option>").text("Выберите статус закупки"));
                    }
                    if(type == 2) {
                        selectObj.append($("<option></option>").text("Выберите способ закупки"));
                    }
                    if(type == 3) {
                        selectObj.append($("<option></option>").text("Выберите закупаемые услуги"));
                    }
                    if(type == 4) {
                        selectObj.append($("<option></option>").text("Выберите ФЗ"));
                    }
                    if(type == 6) {
                        selectObj.append($("<option></option>").text("Выберите статус обеспечения заявки"));
                    }
                    if(type == 7) {
                        selectObj.append($("<option></option>").text("Выберите статус обеспечения контракта"));
                    }
                    if(type == 8) {
                        selectObj.append($("<option></option>").text("Выберите адрес площадки"));
                    }
                    if(type == 9) {
                        selectObj.append($("<option></option>").text("Выберите тип платежа"));
                    }
                    
                if(itemsArr.length > 0) {
                    
                    var resItems = "";
                    
                    // Вывод значений списков
                    for (var i = 0; i < itemsArr.length; i++) {
                        
                    // Обновляем селект
                    
                    selectObj.append($("<option></option>").attr("value", itemsArr[i]['id']).text(itemsArr[i]['description']));
                        
                    resItems = resItems + "<div style='margin-top:5px;'>" + itemsArr[i]['description'];
                   
                    if(itemsArr[i]['required'] == 0) {
                        resItems = resItems + "<span class='editItem' data-id='" + itemsArr[i]['id'] + "' data-name='" + itemsArr[i]['description'] + "' style='color:#d08f33; margin-left: 10px; text-decoration:underline; font-size:12px; cursor:pointer;'>Изменить</span><span class='deleteItem' data-id='" + itemsArr[i]['id'] + "' data-name='" + itemsArr[i]['description'] + "' style='color:#d9534f; margin-left: 10px; text-decoration:underline; font-size:12px; cursor:pointer;'>Удалить</span>";
                    } else {
                        resItems = resItems + "<span class='editItem' data-id='" + itemsArr[i]['id'] + "' data-name='" + itemsArr[i]['description'] + "' style='color:#d08f33; margin-left: 10px; text-decoration:underline; font-size:12px; cursor:pointer;'>Изменить</span>";
                    }
                    
                    resItems = resItems + "</div>";
                   
                    }
                    
                    $('.place_list').html('<div style="font-size: 16px;"><b>Список:</b></div>' + resItems + '');
                    
                } else {
                    $('.place_list').html('<div style="font-size: 16px;"><b>Список:</b></div><br /><div>Нет данных</div>');
                }
                
                } else {
                // Неудачно
                    $('.place_list').html('<div style="font-size: 16px;"><b>Список:</b></div><div>Ошибка загрузки</div>');
                }
                
                }
                });
    
}

// кнопка добавить новый пункт в список
$('.addNewItem').on('click', function() {
    
    var newItemName = $('.itemName');
    var newItemReq = $('.required');
    var requerVal = 0;
    
    if (newItemReq.is(":checked")) {
        requerVal = 1;
    }
    
    $.ajax({
                type     :'POST',
                cache    : true,
                data: 'type=' + selectType + '&name=' + newItemName.val() + '&required=' + requerVal,
                url  : '$actionSaveNewItem',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                // Обнуление формы
                requerVal = 0;
                newItemName.val('');
                newItemReq.prop('checked', false);
                loadListsItems(selectType);
                
                } else {
                // Неудачно
                }
                
                }
                });
    
});

// Нажимаем на кнопку изменить пункт меню
$('.place_list').on('click', '.editItem', function(){
    $('#showSettingsList').modal('hide');
    
    $('.itemNameEdit').val($(this).data("name"));
    
    selectID = 0;
    selectID = $(this).data("id");
    
    $('#showEditItem').modal('show');
});

// Нажимаем на кнопку удалить пункт меню
$('.place_list').on('click', '.deleteItem', function(){
    
    var checkDeleteItem = confirm('Вы уверены что хотите удалить пункт "' + $(this).data("name") + '" из списка?');
    
    if(checkDeleteItem == true) {     
       
            $.ajax({
                type     :'POST',
                cache    : true,
                data: 'item_id=' + $(this).data("id"),
                url  : '$actionDelItem',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                loadListsItems(selectType);
                } else {
                // Неудачно
                }
                
                }
                });
        
    }
    
});

// кнопка сохранить изменение пункта меню
$('.SaveItem').on('click', function() {
    
    var newItemName = $('.itemNameEdit');
    
    $.ajax({
                type     :'POST',
                cache    : true,
                data: 'id=' + selectID + '&name=' + newItemName.val(),
                url  : '$actionEditItem',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                newItemName.val("");
                $('#showEditItem').modal('hide');
                $('#showSettingsList').modal('show');

                loadListsItems(selectType);
                
                } else {
                // Неудачно
                }
                
                }
                });
    
});

$('.field-tendercontrol-purchase').css("display", "none");
$('.field-tendercontrol-user_id').css("display", "none");
$('.field-tendercontrol-site_address').css("display", "none");
$('.field-tendercontrol-platform').css("display", "none");
$('.field-tendercontrol-customer').css("display", "none");

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
$this->registerJs($script, View::POS_READY);
?>

    <div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Тендер №' . $model->id ?>
        <div class="header-btn pull-right">
            <span class="btn btn-warning btn-sm listSettings" style="margin-left:10px;">Управление списками</span>
        </div>
    </div>
    <div class="panel-body">
<table class="table table-bordered list-data">
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('site') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'site',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите адрес сайта (с http://)'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
            <?= '<span class="glyphicon glyphicon-new-window"></span>' ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('purchase_status') ?></td>
        <td>
            <?php

            $arrPurchstatus = isset($arrLists[0]) ? $arrLists[0] : [];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'purchase_status',
                'displayValue' => isset($arrPurchstatus[$model->purchase_status]) ? $arrPurchstatus[$model->purchase_status] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $arrPurchstatus,
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>

    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('user_id') ?></td>
        <td>
            <?php

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'user_id',
                'displayValue' => isset($usersList[$model->user_id]) ? $usersList[$model->user_id] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'data' => $usersList,
                'options' => ['class' => 'form-control', 'placeholder' => 'Выберите сотрудника'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('work_user_id') ?></td>
        <td>
            <?php

            $workUserArr = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->andWhere(['OR', ['department_id' => 1], ['department_id' => 7]])->select('user.id, user.username')->asArray()->all();

            $workUserData = [];

            if(count($workUserArr) > 0) {
                $workUserData[''] = '- Выберите разработчика тех. задания';
            }

            foreach ($workUserArr as $name => $value) {
                $index = $value['id'];
                $workUserData[$index] = trim($value['username']);
            }
            asort($workUserData);

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'work_user_id',
                'displayValue' => isset($workUserData[$model->work_user_id]) ? ($model->work_user_id > 0 ?$workUserData[$model->work_user_id] : '') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' =>  ((\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN) || ((!$model->work_user_id) && ($model->tender_close == 0))) ? false : true,
                'size' => 'lg',
                'data' => $workUserData,
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_request_start') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_request_start',
                'displayValue' => $model->date_request_start ? date('d.m.Y', $model->date_request_start) : '',
                'inputType' => Editable::INPUT_DATE,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options'=>['value' => $model->date_request_start ? date('d.m.Y', $model->date_request_start) : '']
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_request_end') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_request_end',
                'displayValue' => $model->date_request_end ? date('d.m.Y H:i', $model->date_request_end) : '',
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => $model->date_request_end ? date('d.m.Y H:i', $model->date_request_end) : ''],
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy hh:i',
                        'weekStart'=>1,
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('time_request_process') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'time_request_process',
                'displayValue' => $model->time_request_process ? date('d.m.Y H:i', $model->time_request_process) : '',
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => $model->time_request_process ? date('d.m.Y H:i', $model->time_request_process) : ''],
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy hh:i',
                        'weekStart'=>1,
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('time_bidding_start') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'time_bidding_start',
                'displayValue' => $model->time_bidding_start ? date('d.m.Y H:i', $model->time_bidding_start) : '',
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => $model->time_bidding_start ? date('d.m.Y H:i', $model->time_bidding_start) : ''],
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy hh:i',
                        'weekStart'=>1,
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('time_bidding_end') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'time_bidding_end',
                'displayValue' => $model->time_bidding_end ? date('d.m.Y H:i', $model->time_bidding_end) : '',
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => $model->time_bidding_end ? date('d.m.Y H:i', $model->time_bidding_end): ''],
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy hh:i',
                        'weekStart'=>1,
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('customer') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'customer',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('comment_customer') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'comment_customer',
                'displayValue' => nl2br($model->comment_customer),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий к полю "Заказчик"'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('inn_customer') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'inn_customer',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('contacts_resp_customer') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'contacts_resp_customer',
                'displayValue' => nl2br($model->contacts_resp_customer),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите контакты ответственных лиц заказчика'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('method_purchase') ?></td>
        <td>
            <?php

            $arrMethods = isset($arrLists[2]) ? $arrLists[2] : [];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'method_purchase',
                'displayValue' => isset($arrMethods[$model->method_purchase]) ? $arrMethods[$model->method_purchase] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $arrMethods,
                'options' => ['class' => 'form-control', 'prompt' => 'Выберите способ закупки'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);  ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('city') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'city',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('service_type') ?></td>
        <td>
            <?php

            $ServicesList = isset($arrLists[3]) ? $arrLists[3] : [];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'service_type',
                'displayValue' => isset($ServicesList[$model->service_type]) ? $ServicesList[$model->service_type] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $ServicesList,
                'options' => ['class' => 'form-control', 'prompt' => 'Выберите закупаемые услуги'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('purchase') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'purchase',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'disabled' => $model->tender_close == 1 ? true : false,
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('federal_law') ?></td>
        <td>
            <?php

            $arrFZlist = isset($arrLists[4]) ? $arrLists[4] : [];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'federal_law',
                'displayValue' => isset($arrFZlist[$model->federal_law]) ? $arrFZlist[$model->federal_law] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $arrFZlist,
                'options' => ['class' => 'form-control', 'prompt' => 'Выберите ФЗ'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>

    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('number_purchase') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'number_purchase',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('site_address') ?></td>
        <td>
            <?php


            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'site_address',
                'displayValue' => isset($arrsite[$model->site_address]) ? $arrsite[$model->site_address] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'disabled' => $model->tender_close == 1 ? true : false,
                'data' => $arrsite,
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('place') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'place',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>

    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('price_nds') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'price_nds',
                'displayValue' => $model->price_nds ? ($model->price_nds . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('final_price') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'final_price',
                'displayValue' => $model->final_price ? ($model->final_price . ' ₽') : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('status_request_security') ?></td>
        <td>
            <?php

            $arrStatusRequestList = isset($arrLists[6]) ? $arrLists[6] : [];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'status_request_security',
                'displayValue' => isset($arrStatusRequestList[$model->status_request_security]) ? $arrStatusRequestList[$model->status_request_security] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $arrStatusRequestList,
                'options' => ['class' => 'form-control', 'prompt' => 'Статус обеспечения заявки'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_status_request') ?></td>
        <td>
            <?= ($model->date_status_request) ? date('d.m.Y H:i', $model->date_status_request) : '-' ?>
        </td>
    </tr>

    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('status_contract_security') ?></td>
        <td>
            <?php

            $arrStatusContractList = isset($arrLists[7]) ? $arrLists[7] : [];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'status_contract_security',
                'displayValue' => isset($arrStatusContractList[$model->status_contract_security]) ? $arrStatusContractList[$model->status_contract_security] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'data' => $arrStatusContractList,
                'options' => ['class' => 'form-control', 'prompt' => 'Дата изменения статуса заявки'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_status_contract') ?></td>
        <td>
            <?= ($model->date_status_contract) ? date('d.m.Y H:i', $model->date_status_contract) : '-' ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_contract') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_contract',
                'displayValue' => ($model->date_contract) ? date('d.m.Y', $model->date_contract) : '',
                'inputType' => Editable::INPUT_DATE,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options'=>['value' => ($model->date_contract) ? date('d.m.Y', $model->date_contract) : '']
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('term_contract') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'term_contract',
                'displayValue' => ($model->term_contract) ? date('d.m.Y', $model->term_contract) : '',
                'inputType' => Editable::INPUT_DATE,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => [
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options'=>['value' => ($model->term_contract) ? date('d.m.Y', $model->term_contract) : '']
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('comment_date_contract') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'comment_date_contract',
                'displayValue' => nl2br($model->comment_date_contract),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>




    <tr>
        <td class="list-label-md">Осталось дней до окончания действия договора</td>
        <td>

            <?php

            if($model->term_contract) {
                $timeNow = time();

                $showTotal = '';

                if ($model->term_contract > $timeNow) {

                    $totalDate = $model->term_contract - $timeNow;

                    $days = ((Int)($totalDate / 86400));
                    $totalDate -= (((Int)($totalDate / 86400)) * 86400);

                    if ($days < 0) {
                        $days = 0;
                    }

                    $showTotal .= $days . ' д.';

                } else {
                    $totalDate = $timeNow - $model->term_contract;

                    $days = ((Int)($totalDate / 86400));
                    $totalDate -= (((Int)($totalDate / 86400)) * 86400);

                    if ($days < 0) {
                        $days = 0;
                    }
                    $showTotal .= '- ' . $days . ' д.';
                }

                echo $showTotal;
            }

            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('comment') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'comment',
                'displayValue' => nl2br($model->comment),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'disabled' => $model->tender_close == 1 ? true : false,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('files') ?></td>
        <td>
            <?php

            $pathfolder = \Yii::getAlias('@webroot/files/tenders/' . $model->id . '/');
            $shortPath = '/files/tenders/' . $model->id . '/';

            if (file_exists($pathfolder)) {

                $numFiles = 0;
                $resLinksFiles = '';
                $arrStateID = [];

                foreach (\yii\helpers\FileHelper::findFiles($pathfolder) as $file) {

                    $resLinksFiles .= Html::a(basename($file), $shortPath . basename($file), ['target' => '_blank']) . '<br />';
                    $numFiles++;

                }

                if($numFiles > 0) {
                    echo $resLinksFiles;
                } else {
                    echo '-<br />';
                }

            } else {
                echo '-<br />';
            }

            ?>

            <?php
            if ($model->tender_close == 1) {

            } else {
                echo '<br /><span class="btn btn-primary btn-sm showFormAttachButt" style="margin-right:15px;">Добавить вложение</span>';
            }
            ?>
        </td>
    </tr>
   <?php
   if ($model->tender_close == 1) {
       echo "<tr> 
        <td class='list-label-md'>Закупка закрыта</td><td><span style='color:#BA0006'>Закупка была закрыта, поэтому внести изменения невозможно</span>" .
           (\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN ? "<br /><span class='btn btn-warning openTender'>Открыть закупку</span></td>" : "") . "</tr>";
   } else {
       echo "<tr>
        <td class='list-label-md'>Закрыть закупку</td>
        <td> <span class='btn btn-danger closeTender'>Закрыть закупку</span></td>
        </tr>";
   }
 ?>
</table>
    </div>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading">
            Контроль денежных средств
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
                'columns' => [
                    [
                        'header' => '№',
                        'vAlign'=>'middle',
                        'class' => 'kartik\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'user_id',
                        'header' => 'Сотрудник',
                        'format'    => 'raw',
                        'value'     => function ($data) {
                            return Editable::widget([
                                'model'           => $data,
                                'placement'       => PopoverX::ALIGN_RIGHT,
                                'inputType'       => Editable::INPUT_DROPDOWN_LIST,
                                'formOptions'     => [
                                    'action'      => ['/company/updatecontroltender', 'id' => $data->id]
                                ],
                                'displayValue'    => isset($GLOBALS['usersList'][$data->user_id]) ? $GLOBALS['usersList'][$data->user_id] : '',
                                'disabled'        => (\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN && $data->is_archive == 0) ? false : true,
                                'data'            => $GLOBALS['usersList'],
                                'valueIfNull'     => '(не задано)',
                                'buttonsTemplate' => '{submit}',
                                'contentOptions'  => ['style' => 'min-width: 100px'],
                                'submitButton'    => [
                                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                                ],
                                'attribute'       => 'user_id',
                                'asPopover'       => true,
                                'size'            => 'md',
                                'options'         => [
                                    'class'       => 'form-control',
                                    'prompt'      => 'Выберите сотрудника',
                                    'id'          => 'user_id' . $data->id,
                                    'value'       => isset($GLOBALS['usersList'][$data->user_id]) ? $GLOBALS['usersList'][$data->user_id] : ''
                                ],
                            ]);
                        },
                    ],
                    [
                        'attribute' => 'date_send',
                        'format' => 'raw',
                        'value'     => function ($data) {
                            return Editable::widget([
                                'name'            =>'date_send',
                                'placement'       => PopoverX::ALIGN_RIGHT,
                                'inputType'       => Editable::INPUT_DATE,
                                'asPopover'       => true,
                                'value'           => ($data->date_send) ? date('d.m.Y', $data->date_send) : '',
                                'disabled'        => $data->is_archive == 1 ? true : false,
                                'valueIfNull'     => '(не задано)',
                                'buttonsTemplate' => '{submit}',
                                'submitButton'    => [
                                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                                ],
                                'size'=>'md',
                                'formOptions'     => [
                                    'action'      => ['/company/updatecontroltender', 'id' => $data->id]
                                ],
                                'options' => [
                                    'class'         => 'form-control',
                                    'id'            => 'date_send' . $data->id,
                                    'removeButton'  => false,
                                    'pluginOptions' => [
                                        'format'         => 'dd.mm.yyyy',
                                        'autoclose'      => true,
                                        'pickerPosition' => 'bottom-right',
                                    ],
                                    'options'=>['value'  => ($data->date_send) ? date('d.m.Y', $data->date_send) : '']
                                ],
                            ]);
                        },
                    ],
                    [
                        'attribute' => 'date_enlistment',
                        'format' => 'raw',
                        'value'     => function ($data) {
                            return Editable::widget([
                                'name'            =>'date_enlistment',
                                'placement'       => PopoverX::ALIGN_RIGHT,
                                'inputType'       => Editable::INPUT_DATE,
                                'asPopover'       => true,
                                'value'           => ($data->date_enlistment) ? date('d.m.Y', $data->date_enlistment) : '',
                                'disabled'        => $data->is_archive == 1 ? true : false,
                                'valueIfNull'     => '(не задано)',
                                'buttonsTemplate' => '{submit}',
                                'submitButton'    => [
                                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                                ],
                                'size'=>'md',
                                'formOptions'     => [
                                    'action'      => ['/company/updatecontroltender', 'id' => $data->id]
                                ],
                                'options' => [
                                    'class'         => 'form-control',
                                    'id'            => 'date_enlistment' . $data->id,
                                    'removeButton'  => false,
                                    'pluginOptions' => [
                                        'format'         => 'dd.mm.yyyy',
                                        'autoclose'      => true,
                                        'pickerPosition' => 'bottom-right',
                                    ],
                                    'options'=>['value'  => ($data->date_enlistment) ? date('d.m.Y', $data->date_enlistment) : '']
                                ],
                            ]);
                        },
                    ],
                    [
                        'attribute' => 'site_address',
                        'format'    => 'raw',
                        'value'     => function ($data) {
                            return Editable::widget([
                                'model'           => $data,
                                'placement'       => PopoverX::ALIGN_LEFT,
                                'inputType'       => Editable::INPUT_DROPDOWN_LIST,
                                'formOptions'     => [
                                    'action'      => ['/company/updatecontroltender', 'id' => $data->id]
                                ],
                                'displayValue'    => isset($GLOBALS['arrLists'][8][$data->site_address]) ? $GLOBALS['arrLists'][8][$data->site_address] : '',
                                'data'            => $GLOBALS['arrLists'][8],
                                'disabled'        => $data->is_archive == 1 ? true : false,
                                'valueIfNull'     => '(не задано)',
                                'buttonsTemplate' => '{submit}',
                                'contentOptions'  => ['style' => 'min-width: 100px'],
                                'submitButton'    => [
                                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                                ],
                                'attribute'       => 'site_address',
                                'asPopover'       => true,
                                'size'            => 'md',
                                'options'         => [
                                    'class'       => 'form-control',
                                    'prompt'      => 'Выберите адрес площадки',
                                    'id'          => 'site_address' . $data->id,
                                    'value'       => isset($GLOBALS['arrLists'][8][$data->site_address]) ? $GLOBALS['arrLists'][8][$data->site_address] : ''
                                ],
                            ]);
                        },
                    ],
                    [
                        'attribute' => 'platform',
                        'format'    => 'raw',
                        'value'     => function ($data) {
                            return Editable::widget([
                                'model'           => $data,
                                'placement'       => PopoverX::ALIGN_LEFT,
                                'inputType'       => Editable::INPUT_TEXT,
                                'formOptions'     => [
                                    'action'      => ['/company/updatecontroltender', 'id' => $data->id]
                                ],
                                'displayValue'    => isset($data->platform) ? $data->platform : '',
                                'disabled'        => $data->is_archive == 1 ? true : false,
                                'valueIfNull'     => '(не задано)',
                                'buttonsTemplate' => '{submit}',
                                'contentOptions'  => ['style' => 'min-width: 100px'],
                                'submitButton'    => [
                                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                                ],
                                'attribute'       => 'platform',
                                'asPopover'       => true,
                                'size'            => 'md',
                                'options'         => [
                                    'class'       => 'form-control',
                                    'placeholder' => 'Введите электронную площадку',
                                    'id'          => 'platform' . $data->id,
                                    'value'       => $data->platform
                                ],
                            ]);
                        },
                    ],
                    [
                        'attribute' => 'customer',
                        'format'    => 'raw',
                        'value'     => function ($data) {
                            return Editable::widget([
                                'model'           => $data,
                                'placement'       => PopoverX::ALIGN_LEFT,
                                'inputType'       => Editable::INPUT_TEXT,
                                'formOptions'     => [
                                    'action'      => ['/company/updatecontroltender', 'id' => $data->id]
                                ],
                                'displayValue'    => isset($data->customer) ? $data->customer : '',
                                'disabled'        => $data->is_archive == 1 ? true : false,
                                'valueIfNull'     => '(не задано)',
                                'buttonsTemplate' => '{submit}',
                                'contentOptions'  => ['style' => 'min-width: 100px'],
                                'submitButton'    => [
                                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                                ],
                                'attribute'       => 'customer',
                                'asPopover'       => true,
                                'size'            => 'md',
                                'options'         => [
                                    'class'       => 'form-control',
                                    'placeholder' => 'Введите заказчика',
                                    'id'          => 'customer' . $data->id,
                                    'value'       => $data->customer
                                ],
                            ]);
                        },
                    ],
                    [
                        'attribute' => 'purchase',
                        'format'    => 'raw',
                        'value'     => function ($data) {
                            return Editable::widget([
                                'model'           => $data,
                                'placement'       => PopoverX::ALIGN_LEFT,
                                'formOptions'     => [
                                    'action' => ['/company/updatecontroltender', 'id' => $data->id]
                                ],
                                'valueIfNull'     => '(не задано)',
                                'buttonsTemplate' => '{submit}',
                                'disabled'        => $data->is_archive == 1 ? true : false,
                                'inputType'       => Editable::INPUT_TEXTAREA,
                                'submitButton'    => [
                                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                                ],
                                'attribute'       => 'purchase',
                                'asPopover'       => true,
                                'size'            => 'md',
                                'options'         => [
                                    'class'       => 'form-control',
                                    'placeholder' => 'Введите что закупается',
                                    'id'          => 'purchase' . $data->id
                                ],
                            ]);
                        },
                    ],
                    [
                        'attribute' => 'type_payment',
                        'format'    => 'raw',
                        'value'     => function ($data) {
                            return Editable::widget([
                                'model'           => $data,
                                'placement'       => PopoverX::ALIGN_LEFT,
                                'inputType'       => Editable::INPUT_DROPDOWN_LIST,
                                'formOptions'     => [
                                    'action'      => ['/company/updatecontroltender', 'id' => $data->id]
                                ],
                                'displayValue'    => isset($GLOBALS['arrLists'][9][$data->type_payment]) ? $GLOBALS['arrLists'][9][$data->type_payment] : '',
                                'disabled'        => $data->is_archive == 1 ? true : false,
                                'data'            => $GLOBALS['arrLists'][9],
                                'valueIfNull'     => '(не задано)',
                                'buttonsTemplate' => '{submit}',
                                'contentOptions'  => ['style' => 'min-width: 100px'],
                                'submitButton'    => [
                                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                                ],
                                'attribute'       => 'type_payment',
                                'asPopover'       => true,
                                'size'            => 'md',
                                'options'         => [
                                    'class'       => 'form-control',
                                    'prompt'      => 'Выберите тип платежа',
                                    'id'          => 'type_payment' . $data->id,
                                    'value'       => isset($GLOBALS['arrLists'][9][$data->type_payment]) ? $GLOBALS['arrLists'][9][$data->type_payment] : ''
                                ],
                            ]);
                        },
                    ],
                    [
                        'attribute' => 'send',
                        'format'    => 'raw',
                        'value'     => function ($data) {
                            return Editable::widget([
                                'model'           => $data,
                                'placement'       => PopoverX::ALIGN_LEFT,
                                'inputType'       => Editable::INPUT_TEXT,
                                'formOptions'     => [
                                    'action'      => ['/company/updatecontroltender', 'id' => $data->id]
                                ],
                                'displayValue'    => isset($data->send) ? $data->send : '',
                                'disabled'        => (\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN && $data->is_archive == 0) ? false : true,
                                'valueIfNull'     => '(не задано)',
                                'buttonsTemplate' => '{submit}',
                                'contentOptions'  => ['style' => 'min-width: 100px'],
                                'submitButton'    => [
                                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                                ],
                                'attribute'       => 'send',
                                'asPopover'       => true,
                                'size'            => 'md',
                                'options'         => [
                                    'class'       => 'form-control',
                                    'placeholder' => 'Введите сумму отправления',
                                    'id'          => 'send' . $data->id,
                                    'value'       => $data->send
                                ],
                            ]);
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
                        'attribute' => 'money_unblocking',
                        'format' => 'raw',
                        'value'     => function ($data) {
                            return Editable::widget([
                                'name'            => 'money_unblocking',
                                'placement'       => PopoverX::ALIGN_LEFT,
                                'inputType'       => Editable::INPUT_DATE,
                                'asPopover'       => true,
                                'value'           => ($data->money_unblocking) ? date('d.m.Y', $data->money_unblocking) : '',
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
                                    'id'            => 'money_unblocking' . $data->id,
                                    'removeButton'  => false,
                                    'pluginOptions' => [
                                        'format'         => 'dd.mm.yyyy',
                                        'autoclose'      => true,
                                        'pickerPosition' => 'bottom-right',
                                    ],
                                    'options' => ['value' => ($data->money_unblocking) ? date('d.m.Y', $data->money_unblocking) : '']
                                ],
                            ]);
                        },
                    ],
                    [
                        'attribute' => 'return',
                        'format'    => 'raw',
                        'value'     => function ($data) {
                            return Editable::widget([
                                'model'           => $data,
                                'placement'       => PopoverX::ALIGN_LEFT,
                                'formOptions'     => [
                                    'action' => ['/company/updatecontroltender', 'id' => $data->id]
                                ],
                                'valueIfNull'     => '(не задано)',
                                'buttonsTemplate' => '{submit}',
                                'displayValue' => isset($data->return) ? $data->return : '',
                                'disabled'        => (\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN && $data->is_archive == 0) ? false : true,
                                'contentOptions' => ['style' => 'min-width: 100px'],
                                'submitButton'    => [
                                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                                ],
                                'attribute'       => 'return',
                                'asPopover'       => true,
                                'size'            => 'md',
                                'options'         => [
                                    'class'       => 'form-control',
                                    'placeholder' => 'Введите сумму возврата',
                                    'id'          => 'return' . $data->id,
                                    'value'       => $data->return
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
                                'disabled'        => (\Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN && $data->is_archive == 0) ? false : true,
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
                        'attribute' => 'comment',
                        'format'    => 'raw',
                        'value'     => function ($data) {
                            return Editable::widget([
                                'model'           => $data,
                                'placement'       => PopoverX::ALIGN_LEFT,
                                'formOptions'     => [
                                    'action'      => ['/company/updatecontroltender', 'id' => $data->id]
                                ],
                                'valueIfNull'     => '(не задано)',
                                'buttonsTemplate' => '{submit}',
                                'inputType'       => Editable::INPUT_TEXTAREA,
                                'disabled'        => $data->is_archive == 1 ? true : false,
                                'submitButton'    => [
                                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                                ],
                                'attribute'       => 'comment',
                                'asPopover'       => true,
                                'size'            => 'md',
                                'options'         => [
                                    'class'       => 'form-control',
                                    'placeholder' => 'Введите комментарий',
                                    'id'          => 'comment' . $data->id
                                ],
                            ]);
                        },
                    ],
                ],
            ]);
            ?>
        </div>
    </div>

            <div class="panel panel-primary">
                <div class="panel-heading">
                    Данные для контроля денежных средств
                </div>
                <div class="panel-body">

                <?php
    $form = ActiveForm::begin([
    'action' => $newmodel->isNewRecord ? ['/company/newcontroltender', 'id' => $model->id] : '',
    'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
    'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
    'labelOptions' => ['class' => 'col-sm-3 control-label'],
    'inputOptions' => ['class' => 'form-control input-sm'],
    ],
    ]); ?>


    <?= $form->field($newmodel, 'user_id')->hiddenInput(['value' => $model->user_id])->label(false) ?>
    <?= $form->field($newmodel, 'site_address')->hiddenInput(['value' => $model->site_address])->label(false) ?>
    <?= $form->field($newmodel, 'platform')->hiddenInput(['value' => $model->place])->label(false) ?>
    <?= $form->field($newmodel, 'customer')->hiddenInput(['value' => $model->customer])->label(false) ?>
    <?= $form->field($newmodel, 'purchase')->hiddenInput(['value' => $model->purchase])->label(false) ?>

    <?= $form->field($newmodel, 'send')->input('text', ['class' => 'form-control', 'placeholder' => 'Отправили']) ?>
    <?= $form->field($newmodel, 'date_send')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_INPUT,
        'options' => ['placeholder' => 'Дата отправки'],
        'pluginOptions' => [
            'format' => 'dd.mm.yyyy',
            'autoclose'=>true,
            'weekStart'=>1,
        ]
    ]) ?>
    <?= $form->field($newmodel, 'date_enlistment')->widget(DatePicker::className(), [
        'type' => DatePicker::TYPE_INPUT,
        'options' => ['placeholder' => 'Дата зачисления'],
        'pluginOptions' => [
            'format' => 'dd.mm.yyyy',
            'autoclose'=>true,
            'weekStart'=>1,
        ]
    ]) ?>
    <?= $form->field($newmodel, 'type_payment')->dropDownList($arrtype, ['class' => 'form-control', 'prompt' => 'Выберите тип платежа']) ?>

    <?= $form->field($newmodel, 'comment')->textarea(['maxlength' => true, 'rows' => '4', 'placeholder' => 'Комментарий']) ?>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>
            </div>
            </div>

<?php ActiveForm::end();

// Модальное окно с названиями списков
$modalListsName = Modal::begin([
    'header' => '<h5>Управление списками</h5>',
    'id' => 'showListsName',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
    'size'=>'modal-sm',
]);

echo "<div class='purchase_status' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('purchase_status') . "</div>";
echo "<div class='site_address' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('site_address') . "</div>";
echo "<div class='method_purchase' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('method_purchase') . "</div>";
echo "<div class='service_type' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('service_type') . "</div>";
echo "<div class='federal_law' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('federal_law') . "</div>";
echo "<div class='type_payment' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('type_payment') . "</div>";
echo "<div class='status_request_security' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('status_request_security') . "</div>";
echo "<div class='status_contract_security' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('status_contract_security') . "</div>";

Modal::end();
// Модальное окно с названиями списков

// Модальное окно со списком пунктов
$modalListsName = Modal::begin([
    'header' => '<h5 class="settings_name">Управление списками: </h5>',
    'id' => 'showSettingsList',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
    'size'=>'modal-lg',
]);

echo "<div class='place_list' style='font-size: 15px; margin-left:15px; margin-right:15px;'></div>";
echo "<br /><div style='font-size: 16px; margin-left:15px; margin-right:15px;'><b>Добавление нового:</b>";
echo "<br /><div style='margin-top: 15px;'><input type='text' class='form-control itemName' placeholder='Название'></div>";
echo "<br /><div>Запретить удаление данного пункта: <input type='checkbox' class='required' value='0'></div>";
echo "<br /><span class='btn btn-primary btn-sm addNewItem'>Добавить</span></div>";

Modal::end();
// Модальное окно со списком пунктов

// Модальное окно изменения пункта
$modalListsName = Modal::begin([
    'header' => '<h5>Изменение пункта</h5>',
    'id' => 'showEditItem',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
    'size'=>'modal-lg',
]);

echo "<div style='font-size: 16px; margin-left:15px; margin-right:15px;'><div><input type='text' class='form-control itemNameEdit' placeholder='Название'></div>";
echo "<br /><span class='btn btn-primary btn-sm SaveItem'>Сохранить</span></div>";

Modal::end();
// Модальное окно редактирования списка

?>


<?php
// Модальное окно добавить вложения
$pathfolder = \Yii::getAlias('@webroot/files/tenders/' . $model->id . '/');
$shortPath = '/files/tenders/' . $model->id . '/';

$modalAttach = Modal::begin([
'header' => '<h5>Добавить вложения</h5>',
'id' => 'showFormAttach',
'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default hideButtonComment', 'style' => 'display:none;'],
'size'=>'modal-lg',
]);

echo "<div style='font-size: 15px; margin-left:15px;'>Выберите файлы:</div>";

$modelAddAttach = new \yii\base\DynamicModel(['files']);
$modelAddAttach->addRule(['files'], 'file', ['skipOnEmpty' => true, 'maxFiles' => 30]);

$form = ActiveForm::begin([
'action' => ['/company/newtendattach', 'id' => $model->id],
'options' => ['enctype' => 'multipart/form-data', 'accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
'fieldConfig' => [
'template' => '<div class="col-sm-6">{input}</div>',
'inputOptions' => ['class' => 'form-control input-sm'],
],
]);

echo $form->field($modelAddAttach, 'files[]')->fileInput(['multiple' => true]);

echo Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']);

ActiveForm::end();

Modal::end();
// Модальное окно добавить вложения
?>