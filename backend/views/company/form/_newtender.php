<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;
use \kartik\date\DatePicker;
use \yii\web\View;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use common\models\TenderLists;

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

$actionGetListItems = Url::to('@web/company/listitems');
$actionSaveNewItem = Url::to('@web/company/newitemlist');
$actionDelItem = Url::to('@web/company/deleteitemlist');
$actionEditItem = Url::to('@web/company/edititemlist');

$script = <<< JS

var selectType = 0;
var selectID = 0;

// При вводе в поле "максимальная стоимость закупки" вводится в поле ниже с подсчетом стоимости без НДС
$('#tender-price_nds').bind('input',function(){
   $('#tender-maximum_purchase_price').val(($(this).val() / 1.18).toFixed(2));
});

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

// открываем модальное окно управления списками user_id
$('.user_id').on('click', function() {
    
$('#showListsName').modal('hide');
$('#showSettingsList').modal('show');

$('.settings_name').text('Управление списками: ' + $(this).text());

loadListsItems(1);

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

// открываем модальное окно управления списками key_type
$('.key_type').on('click', function() {
    
$('#showListsName').modal('hide');
$('#showSettingsList').modal('show');

$('.settings_name').text('Управление списками: ' + $(this).text());

loadListsItems(5);

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
                    case 1: {
                        selectObj = $("#tender-user_id");
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
                    case 5: {
                        selectObj = $("#tender-key_type");
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
                }
                
                selectObj.empty();
                // Очищаем текущий селект
                    
                var itemsArr = jQuery.parseJSON(response.items);
                
                    // добавляем placeholder
                    if(type == 0) {
                        selectObj.append($("<option></option>").text("Выберите статус закупки"));
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
<?= $form->field($model, 'comment_status_proc')->textarea(['maxlength' => true, 'rows' => '7', 'placeholder' => 'Введите комментарий к статусу закупки']) ?>
<?= $form->field($model, 'user_id')->dropDownList(isset($arrLists[1]) ? $arrLists[1] : [], ['class' => 'form-control', 'multiple' => 'true']) ?>
<?= $form->field($model, 'date_search')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Дата нахождения закупки', 'value' => date('d.m.Y')],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'date_request_start')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите начало подачи заявки'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'date_request_end')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите окончание подачи заявки'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:i',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>
<?= $form->field($model, 'time_request_process')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату и время рассмотрения заявок'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:i',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>
<?= $form->field($model, 'time_bidding_start')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату и время начала торгов'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:i',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>

<?= $form->field($model, 'time_bidding_end')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату и время подведения итогов'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:i',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>
<?= $form->field($model, 'customer')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите название заказчика']) ?>
<?= $form->field($model, 'comment_customer')->textarea(['maxlength' => true, 'rows' => '7', 'placeholder' => 'Введите комментарий к полю "Заказчик"']) ?>
<?= $form->field($model, 'inn_customer')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите ИНН заказчика']) ?>
<?= $form->field($model, 'contacts_resp_customer')->textarea(['maxlength' => true, 'rows' => '4', 'placeholder' => 'Введите контакты ответственных лиц заказчика']) ?>
<?= $form->field($model, 'method_purchase')->dropDownList(isset($arrLists[2]) ? $arrLists[2] : [], ['class' => 'form-control', 'multiple' => 'true']) ?>
<?= $form->field($model, 'city')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите название города, области поставки']) ?>
<?= $form->field($model, 'service_type')->dropDownList(isset($arrLists[3]) ? $arrLists[3] : [], ['class' => 'form-control', 'multiple' => 'true']) ?>
<?= $form->field($model, 'federal_law')->dropDownList(isset($arrLists[4]) ? $arrLists[4] : [], ['class' => 'form-control', 'multiple' => 'true']) ?>
<?= $form->field($model, 'notice_eis')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите номер извещения в ЕИС']) ?>
<?= $form->field($model, 'number_purchase')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите номер закупки на площадке']) ?>
<?= $form->field($model, 'place')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите адрес сайта']) ?>
<?= $form->field($model, 'key_type')->dropDownList(isset($arrLists[5]) ? $arrLists[5] : [], ['class' => 'form-control', 'multiple' => 'true']) ?>
<?= $form->field($model, 'price_nds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите максимальную стоимость закупки']) ?>
<?= $form->field($model, 'maximum_purchase_price')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите максимальную начальную стоимость закупки без НДС']) ?>
<?= $form->field($model, 'final_price')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите стоимость закупки по завершению закупки с НДС']) ?>
<?= $form->field($model, 'cost_purchase_completion')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите стоимость закупки по завершению закупки без НДС']) ?>
<?= $form->field($model, 'pre_income')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите предварительную прибыль от закупки']) ?>
<?= $form->field($model, 'last_sentence_nds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите наше последнее предложение с НДС']) ?>
<?= $form->field($model, 'last_sentence_nonds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите наше последнее предложение без НДС']) ?>
<?= $form->field($model, 'percent_down')->dropDownList([0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20, 21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25, 26 => 26, 27 => 27, 28 => 28, 29 => 29, 30 => 30, 31 => 31, 32 => 32, 33 => 33, 34 => 34, 35 => 35, 36 => 36, 37 => 37, 38 => 38, 39 => 39, 40 => 40, 41 => 41, 42 => 42, 43 => 43, 44 => 44, 45 => 45, 46 => 46, 47 => 47, 48 => 48, 49 => 49, 50 => 50, 51 => 51, 52 => 52, 53 => 53, 54 => 54, 55 => 55, 56 => 56, 57 => 57, 58 => 58, 59 => 59, 60 => 60, 61 => 61, 62 => 62, 63 => 63, 64 => 64, 65 => 65, 66 => 66, 67 => 67, 68 => 68, 69 => 69, 70 => 70, 71 => 71, 72 => 72, 73 => 73, 74 => 74, 75 => 75, 76 => 76, 77 => 77, 78 => 78, 79 => 79, 80 => 80, 81 => 81, 82 => 82, 83 => 83, 84 => 84, 85 => 85, 86 => 86, 87 => 87, 88 => 88, 89 => 89, 90 => 90, 91 => 91, 92 => 92, 93 => 93, 94 => 94, 95 => 95, 96 => 96, 97 => 97, 98 => 98, 99 => 99, 100 => 100], ['class' => 'form-control', 'prompt' => 'Выберите процентное снижение по завершению закупки в процентах']) ?>
<?= $form->field($model, 'maximum_purchase_nds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите снижение от максимальной начальной стоимости закупки по завершению закупки в рублях с НДС']) ?>
<?= $form->field($model, 'maximum_purchase_notnds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите снижение от максимальной начальной стоимости закупки по завершению закупки в рублях без НДС']) ?>
<?= $form->field($model, 'percent_max')->dropDownList([0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20, 21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25, 26 => 26, 27 => 27, 28 => 28, 29 => 29, 30 => 30, 31 => 31, 32 => 32, 33 => 33, 34 => 34, 35 => 35, 36 => 36, 37 => 37, 38 => 38, 39 => 39, 40 => 40, 41 => 41, 42 => 42, 43 => 43, 44 => 44, 45 => 45, 46 => 46, 47 => 47, 48 => 48, 49 => 49, 50 => 50, 51 => 51, 52 => 52, 53 => 53, 54 => 54, 55 => 55, 56 => 56, 57 => 57, 58 => 58, 59 => 59, 60 => 60, 61 => 61, 62 => 62, 63 => 63, 64 => 64, 65 => 65, 66 => 66, 67 => 67, 68 => 68, 69 => 69, 70 => 70, 71 => 71, 72 => 72, 73 => 73, 74 => 74, 75 => 75, 76 => 76, 77 => 77, 78 => 78, 79 => 79, 80 => 80, 81 => 81, 82 => 82, 83 => 83, 84 => 84, 85 => 85, 86 => 86, 87 => 87, 88 => 88, 89 => 89, 90 => 90, 91 => 91, 92 => 92, 93 => 93, 94 => 94, 95 => 95, 96 => 96, 97 => 97, 98 => 98, 99 => 99, 100 => 100], ['class' => 'form-control', 'prompt' => 'Выберите максимальное согласованное расчетное снижение в процентах']) ?>
<?= $form->field($model, 'maximum_agreed_calcnds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите максимальное согласованное расчетное снижение в рублях с НДС']) ?>
<?= $form->field($model, 'maximum_agreed_calcnotnds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите максимальное согласованное расчетное снижение в рублях без НДС']) ?>
<?= $form->field($model, 'site_fee_participation')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите плату площадке за участи']) ?>
<?= $form->field($model, 'ensuring_application')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите обеспечение заявки']) ?>
<?= $form->field($model, 'status_request_security')->dropDownList(isset($arrLists[6]) ? $arrLists[6] : [], ['class' => 'form-control', 'multiple' => 'true']) ?>
<?= $form->field($model, 'contract_security')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите обеспечение контракта']) ?>
<?= $form->field($model, 'status_contract_security')->dropDownList(isset($arrLists[7]) ? $arrLists[7] : [], ['class' => 'form-control', 'multiple' => 'true']) ?>
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
echo "<div class='user_id' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('user_id') . "</div>";
echo "<div class='method_purchase' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('method_purchase') . "</div>";
echo "<div class='service_type' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('service_type') . "</div>";
echo "<div class='federal_law' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('federal_law') . "</div>";
echo "<div class='key_type' style='font-size: 15px; margin-left:15px; margin-bottom:15px; cursor: pointer;'>" . $model->getAttributeLabel('key_type') . "</div>";
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