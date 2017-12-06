<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;
use \kartik\date\DatePicker;
use \yii\web\View;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use common\models\TenderLists;

$actionGetListItems = Url::to('@web/company/listitems');
$actionSaveNewItem = Url::to('@web/company/newitemlist');
$actionDelItem = Url::to('@web/company/deleteitemlist');
$actionEditItem = Url::to('@web/company/edititemlist');

$script = <<< JS

var selectType = 0;
var selectID = 0;

// открываем модальное окно с названиями списков
$('.listSettings').on('click', function() {
$('#showListsName').modal('show');
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

JS;
$this->registerJs($script, View::POS_READY);

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

$form = ActiveForm::begin([
    'action' => $model->isNewRecord ? ['/company/newcontroltender'] : ['/company/updatecontroltender'],
    'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
        'labelOptions' => ['class' => 'col-sm-3 control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]); ?>

<?= $form->field($model, 'user_id')->dropDownList(isset($usersList) ? $usersList : [], ['class' => 'form-control', 'prompt' => 'Выберите сотрудника']) ?>
<?= $form->field($model, 'send')->input('text', ['class' => 'form-control', 'placeholder' => 'Отправили']) ?>
<?= $form->field($model, 'date_send')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Дата отправки'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'date_enlistment')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Дата зачисления'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'site_address')->dropDownList($arrsite, ['class' => 'form-control', 'prompt' => 'Выберите адрес площадки']) ?>
<?= $form->field($model, 'platform')->input('text', ['class' => 'form-control', 'placeholder' => 'Площадка']) ?>
<?= $form->field($model, 'customer')->input('text', ['class' => 'form-control', 'placeholder' => 'Заказчик']) ?>
<?= $form->field($model, 'purchase')->input('text', ['class' => 'form-control', 'placeholder' => 'Закупка']) ?>
<?= $form->field($model, 'eis_platform')->input('text', ['class' => 'form-control', 'placeholder' => '№ ЕИС на площадке']) ?>
<?= $form->field($model, 'type_payment')->dropDownList($arrtype, ['class' => 'form-control', 'prompt' => 'Выберите тип платежа']) ?>
<?= $form->field($model, 'money_unblocking')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Дата разблокировки денег'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'return')->input('text', ['class' => 'form-control', 'placeholder' => 'Возврат']) ?>
<?= $form->field($model, 'date_return')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Дата возврата'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'comment')->textarea(['maxlength' => true, 'rows' => '4', 'placeholder' => 'Комментарий']) ?>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) . '<span class="btn btn-warning btn-sm listSettings" style="margin-left:10px;">Управление списками</span>' ?>
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

echo "<div class='site_address' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('site_address') . "</div>";
echo "<div class='type_payment' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('type_payment') . "</div>";

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