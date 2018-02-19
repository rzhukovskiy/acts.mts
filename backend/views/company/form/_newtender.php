<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;
use \kartik\date\DatePicker;
use \yii\web\View;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use common\models\TenderLists;
use common\models\User;

/* @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $form yii\widgets\ActiveForm
 */

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

// Разработчик тех. задания - список
$workUserArr = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->andWhere(['OR', ['department_id' => 1], ['department_id' => 7]])->select('user.id, user.username')->asArray()->all();

$workUserData = [];

foreach ($workUserArr as $name => $value) {
    $index = $value['id'];
    $workUserData[$index] = trim($value['username']);
}
asort($workUserData);
// Разработчик тех. задания - список

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

$script = <<< JS

var selectType = 0;
var selectID = 0;

// открываем модальное окно с названиями списков
$('.listSettings').on('click', function() {
$('#showListsName').modal('show');
});

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

JS;
$this->registerJs($script, View::POS_READY);

$form = ActiveForm::begin([
    'action' => $model->isNewRecord ? ['/company/newtender', 'id' => $id] : ['/company/updatetender', 'id' => $model->id],
    'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
        'labelOptions' => ['class' => 'col-sm-3 control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]); ?>

<?= $form->field($model, 'company_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'site')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите адрес сайта (с http://)']) ?>
<?= $form->field($model, 'purchase_status')->dropDownList(isset($arrLists[0]) ? $arrLists[0] : [], ['class' => 'form-control', 'prompt' => 'Выберите статус закупки']) ?>
<?= $form->field($model, 'user_id')->dropDownList($usersList, ['class' => 'form-control', 'prompt' => 'Выберите сотрудника']) ?>
<?= $form->field($model, 'work_user_id')->dropDownList($workUserData, ['class' => 'form-control', 'prompt' => 'Выберите разработчика тех. задания']) ?>
<?= $form->field($model, 'date_request_start')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите начало подачи заявки'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:ii',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'date_request_end')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите окончание подачи заявки'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:ii',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>
<?= $form->field($model, 'time_request_process')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату и время рассмотрения заявок'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:ii',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>
<?= $form->field($model, 'time_bidding_start')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату и время начала торгов'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:ii',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>

<?= $form->field($model, 'time_bidding_end')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату и время подведения итогов'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:ii',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>
<?= $form->field($model, 'customer')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите название заказчика']) ?>
<?= $form->field($model, 'comment_customer')->textarea(['maxlength' => true, 'rows' => '7', 'placeholder' => 'Введите комментарий к полю "Заказчик"']) ?>
<?= $form->field($model, 'inn_customer')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите ИНН заказчика']) ?>
<?= $form->field($model, 'contacts_resp_customer')->textarea(['maxlength' => true, 'rows' => '4', 'placeholder' => 'Введите контакты ответственных лиц заказчика']) ?>
<?= $form->field($model, 'method_purchase')->dropDownList(isset($arrLists[2]) ? $arrLists[2] : [], ['class' => 'form-control', 'prompt' => 'Выберите способ закупки']) ?>
<?= $form->field($model, 'city')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите название города, области поставки']) ?>
<?= $form->field($model, 'service_type')->dropDownList(isset($arrLists[3]) ? $arrLists[3] : [], ['class' => 'form-control', 'prompt' => 'Выберите закупаемые услуги']) ?>
<?= $form->field($model, 'federal_law')->dropDownList(isset($arrLists[4]) ? $arrLists[4] : [], ['class' => 'form-control', 'prompt' => 'Выберите ФЗ']) ?>
<?= $form->field($model, 'purchase')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите что закупается']) ?>
<?= $form->field($model, 'number_purchase')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите номер закупки на площадке']) ?>
<?= $form->field($model, 'place')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите адрес сайта']) ?>
<?= $form->field($model, 'site_address')->dropDownList($arrsite, ['class' => 'form-control', 'prompt' => 'Выберите адрес площадки']) ?>
<?= $form->field($model, 'price_nds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите максимальную стоимость закупки']) ?>
<?= $form->field($model, 'final_price')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите стоимость закупки по завершению закупки с НДС']) ?>
<?= $form->field($model, 'status_request_security')->dropDownList(isset($arrLists[6]) ? $arrLists[6] : [], ['class' => 'form-control', 'prompt' => 'Выберите статус обеспечения заявки']) ?>
<?= $form->field($model, 'status_contract_security')->dropDownList(isset($arrLists[7]) ? $arrLists[7] : [], ['class' => 'form-control', 'prompt' => 'Выберите статус обеспечения контракта']) ?>
<?= $form->field($model, 'date_contract')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату заключения договора'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'term_contract')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату окончания заключенного договора'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'comment_date_contract')->textarea(['maxlength' => true, 'rows' => '7', 'placeholder' => 'Введите комментарий к сроку договора']) ?>
<?= $form->field($model, 'comment')->textarea(['maxlength' => true, 'rows' => '7', 'placeholder' => 'Введите комментарий']) ?>
<?= $form->field($model, 'files[]')->fileInput(['multiple' => true]) ?>


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