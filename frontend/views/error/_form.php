<?php

/**
 * @var $model \common\models\Act
 * @var $serviceList array
 * @var $clientScopes \common\models\ActScope[]
 * @var $partnerScopes \common\models\ActScope[]
 */

use common\models\Card;
use common\models\Company;
use common\models\Mark;
use yii\helpers\Url;
use common\models\Service;
use common\models\Type;
use common\models\Car;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\jui\AutoComplete;
use yii\web\View;
use yii\bootstrap\Modal;

$actionLink = Url::to('@web/error/numberlist');

$css = ".varNumber {text-decoration:underline; font-size:13px;} .varNumber:hover {text-decoration:none; font-size:13px; cursor:pointer;}";
$this->registerCss($css);

$serviceType = $model->service_type;

$script = <<< JS

    //alert($(".field-act-car_number input").val());

// получаем список похожих номеров
/*$('#act-car_number').bind('input',function() {
    
var number, region;

// получаем цифры номера
if($(this).val().length >= 4) {
number = $(this).val().charAt(1) + $(this).val().charAt(2) + $(this).val().charAt(3);
number = Number(number);
}

// получаем регион
if($(this).val().length == 8) {
region = $(this).val().charAt(6) + $(this).val().charAt(7);
} else if($(this).val().length == 9) {
region = $(this).val().charAt(6) + $(this).val().charAt(7) + $(this).val().charAt(8);
}
region = Number(region);
});*/

// Проверяем что в номере ТС ошибка
var errorMessage = $('.act-error-message');
var numberInput = $('#act-car_number');
var cardInput = $('#act-card_number');

if((errorMessage.text().indexOf('Некорректный номер ТС') + 1) || (errorMessage.text().indexOf('Не совпадает номер карты с номером ТС') + 1)) {
    
    if(numberInput.val().length > 0) {
        
        var numberCard = 0;
        var carMark = 0;
        var carType = 0;
        var numberCar = numberInput.val();
        
        if($('#act-mark_id').val() > 0) {
        carMark = $('#act-mark_id').val();
        }
        
        if($('#act-type_id').val() > 0) {
        carType = $('#act-type_id').val();
        }
        
        if(cardInput.val().length > 0) {
            numberCard = cardInput.val();  
        }
        
        $.ajax({
        type     :'GET',
        cache    : false,
        data:'number=' + numberCar + '&mark=' + carMark + '&type=' + carType + '&card=' + numberCard + '&actType=' + $serviceType,
        url  : '$actionLink',
        success  : function(data) {
            
        var response = $.parseJSON(data);
        
        if (response.success == 'true') { 
        // Удачно
        
        // Если получили не пустой список
        var arrList = response.listCar;
        
        if(arrList.length > 0) {
            
            var textTitle = '';
            var checkWritenNumber = false;
            
            if(response.resType == 0) {
                textTitle = 'Похожие номера:';
            } else {
                textTitle = 'Обслуживаемые номера:';
            }
            
            var showListNumbers = '<table border="0"><tr><td colspan="2" style="color:#000; text-size:13px;"><b>' + textTitle + '</b></td></tr>';
            
            for(var i = 0; i < arrList.length; i++) {
                
                if(arrList[i] == numberCar) {
                    checkWritenNumber = true;
                }
                
            if(arrList[i] != numberCar) {
            showListNumbers += '<tr><td width="85px" class="varNumber">' + arrList[i] + '</td><td></td></tr>';
            }
            
            }
            
            showListNumbers += '</table>';
            
            if((checkWritenNumber == false) || ((checkWritenNumber == true) && (arrList.length > 1))) {
            $('.field-act-car_number').after('<div class="numberList">' + showListNumbers + '</div>');
            }
            
        }
        
        } else {
        }
        
        }
        });
        
    }
    
    // При нажатии на предложенный номер заполняем его в форму
    
    $("tr td").on('click', '.varNumber', function () {
        numberInput.val($(this).text())
    });
    
}
    
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Редактировать акт
    </div>
    <div class="panel-body">
        <?= $this->render('_message',
        [
            'model' =>$model,
        ]);
        ?>
        <?php
        $form = ActiveForm::begin([
            'action' => ['act/update', 'id' => $model->id],
            'id' => 'act-form',
        ]) ?>
        <table class="table table-bordered">
            <tbody>
            <tr>
        <td>
            <?= $form->field($model, 'time_str')->widget(DatePicker::classname(), [
                'type' => DatePicker::TYPE_INPUT,
                'language' => 'ru',
                'pluginOptions' => [
                    'autoclose' => true,
                    'format' => 'dd-mm-yyyy'
                ],
                'options' => [
                    'class' => 'form-control',
                    'value' => $model->time_str ? $model->time_str : date('d-m-Y'),
                ]
            ])->error(false) ?>
        </td>
        <td>
            <label class="control-label" for="act-time_str">Партнер</label>
            <?= Html::textInput('partner', $model->partner->name, ['class' => 'form-control', 'disabled' => 'disabled']) ?>
        </td>
        <td>
            <?= $model->service_type == Service::TYPE_WASH ? $form->field($model, 'check')->error(false) : '' ?>
        </td>
        <td>
            <?php

            // выводим форму загрузки чеков для моек и превью
            if($model->service_type == Service::TYPE_WASH) {
                echo '<table border="0">';
                echo '<tr><td valign="middle">';

                $linkCheck = $model->getImageLink();

                if (isset($linkCheck)) {

                    $pathLink = dirname(__FILE__);
                    $pathLink = str_replace('views/error', '', $pathLink);
                    $pathLink .= 'web' . $model->getImageLink();

                    if ((file_exists($pathLink)) && (mb_strlen($model->getImageLink()) > 0)) {

$css = ".glyphicon-arrow-left {
font-size:16px;
}
.glyphicon-arrow-right {
font-size:16px;
}
.glyphicon-arrow-left:hover {
cursor:pointer;
}
.glyphicon-arrow-right:hover {
cursor:pointer;
}";
                        $this->registerCSS($css);


                        $actionLinkRotate = Url::to('@web/act/rotate');

                        $script = <<< JS

function rotateImg(type) {
  
                $.ajax({
                type     :'POST',
                cache    : true,
                data:'name=' + '$linkCheck' + '&type=' + type,
                url  : '$actionLinkRotate',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                
                var d = new Date();
                $('.previewCheck').attr('src', '$linkCheck' + '?' + d.getTime());
                
                } else {
                // Неудачно
                }
                
                }
                });
    
}

// Клик повернуть налево
$('.glyphicon-arrow-left').on('click', function(){
    rotateImg(1);
});
// Клик повернуть налево

// Клик повернуть направо
$('.glyphicon-arrow-right').on('click', function(){
    rotateImg(2);
});
// Клик повернуть направо
JS;
                        $this->registerJs($script, View::POS_READY);

                        echo '<a href="' . $linkCheck . '" target="_blank"><img class="previewCheck" width="70px" src="' . $linkCheck . '" style="margin-left:3px; margin-right:10px;" /></a><div align="center" style="margin-top:10px;"><span class="glyphicon glyphicon-arrow-left"></span><span class="glyphicon glyphicon-arrow-right" style="margin-left:10px;"></span></div>';
                    }

                }

                echo '</td><td>';
                echo $form->field($model, 'image')->fileInput(['class' => 'form-control'])->error(false);
                echo '</td></tr></table>';
            }

            ?>
        </td>
            </tr>
            <tr>
        <td>
            <?= $form->field($model, 'card_number')->textInput(); ?>
        </td>
        <td>
            <?= $form->field($model, 'car_number')->widget(AutoComplete::classname(), [
                'options' => ['class' => 'form-control', 'autocomplete' => 'on'],
                'clientOptions' => [
                    'source' => Car::find()->select('number as value')->asArray()->all(),
                    'minLength'=>'3',
                    'autoFill'=>true,
                ],
            ])->error(false) ?>
        </td>
        <td>
            <?= $form->field($model, 'mark_id')->dropdownList(Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column())->error(false) ?>
            <?php

$css = ".moveCarButt:hover {
cursor:pointer;
}";
$this->registerCSS($css);

            $actionLinkMove = Url::to('@web/car/movecar');
            $company_id = isset($model->car->company_id) ? $model->car->company_id : 0;

            $script = <<< JS

var car_id = 0;

// Удаляем ненужную кнопку открыть модальное окно
$('.hideButtonRemove').remove();

// открываем модальное окно перенести тс
$('.moveCarButt').on('click', function(){
car_id = $(this).data('id');

$('.removeList').html('<b>Номер ТС:</b> ' + $(this).data('number'));

$('#showModal').modal('show');
});

$('#save_new_company').on('click', function(){

                if(($('#new_company').val() > 0) && ($('#new_company').val() != $company_id) && (car_id > 0)) {

                $.ajax({
                type     :'POST',
                cache    : false,
                data:'id=' + car_id + '&company_from=' + '$company_id' + '&company_id=' + $('#new_company').val(),
                url  : '$actionLinkMove',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == 'true') { 
                // Удачно
                car_id = 0;
                alert('Успешно');
                window.location.reload();
                } else {
                // Неудачно
                car_id = 0;
                alert('Ошибка переноса');
                }
                
                }
                });
                
                }
    
});

JS;
            $this->registerJs($script, View::POS_READY);

            ?>
            <?= '<div class="moveCarButt" data-id="' . (isset($model->car->id) ? $model->car->id : 0) . '" data-number="' . (isset($model->car->number) ? $model->car->number : '') . '">Перенести в другой филиал <span class="glyphicon glyphicon-sort"></span></div>'?>
        </td>
        <td>
            <?= $form->field($model, 'type_id')->dropdownList(Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column())->error(false) ?>
        </td>
            </tr>

            <?php if($model->service_type == 3) { ?>

                <tr>
                    <td colspan="4">
                        <div class="col-sm-12">
                            <label class="control-label">Услуги партнера (<?= $model->partner->name ?>)</label>

                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-8"><label class="control-label" style="margin:10px 0px 5px 0px;">Запасные части</label></div>
                            </div>

                            <?php
                            $partnerSum=0;
                            foreach ($partsPartnerScopes as $scope) {
                                $partnerSum+=$scope->amount*$scope->price
                                ?>
                                <div class="form-group" style="height: 25px;">
                                    <div class="col-xs-8">
                                        <?php if (!empty($serviceList)) { ?>
                                            <?= Html::dropDownList("Act[partnerServiceList][$scope->id][service_id]", $scope->service_id,
                                                $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите наименование']) ?>
                                        <?php } else { ?>
                                            <?= Html::textInput("Act[partnerServiceList][$scope->id][description]", $scope->description, ['class' => 'form-control input-sm', 'placeholder' => 'Наименование']) ?>
                                        <?php } ?>
                                    </div>
                                    <div class="col-xs-2">
                                        <?= Html::input('text', "Act[partnerServiceList][$scope->id][amount]", $scope->amount, ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <?= Html::textInput("Act[partnerServiceList][$scope->id][price]", $scope->price, ['class' => 'form-control input-sm resetable', 'placeholder' => 'цена']) ?>
                                    </div>
                                    <div class="col-xs-1" style="display: none;">
                                        <?= Html::input('text', "Act[partnerServiceList][$scope->id][parts]", '1', ['class' => 'form-control input-sm']) ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <button type="button" class="btn btn-primary input-sm removeButton">
                                            <i class="glyphicon glyphicon-minus"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-8">
                                    <?php if (!empty($serviceList)) { ?>
                                        <?= Html::dropDownList("Act[partnerServiceList][71][service_id]", '',
                                            $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите наименование']) ?>
                                    <?php } else { ?>
                                        <?= Html::textInput("Act[partnerServiceList][71][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Наименование']) ?>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-2">
                                    <?= Html::input('text', "Act[partnerServiceList][71][amount]", '1', ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                                </div>
                                <div class="col-xs-1">
                                    <?= Html::textInput("Act[partnerServiceList][71][price]", '', ['class' => 'form-control input-sm resetable', 'placeholder' => 'цена']) ?>
                                </div>
                                <div class="col-xs-1" style="display: none;">
                                    <?= Html::input('text', "Act[partnerServiceList][71][parts]", '1', ['class' => 'form-control input-sm']) ?>
                                </div>
                                <div class="col-xs-1">
                                    <button type="button" class="btn btn-primary input-sm addButton">
                                        <i class="glyphicon glyphicon-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-2 col-xs-offset-8" style="font-weight: bold;font-size: 1.2em">
                                    Сумма
                                </div>
                                <div class="col-xs-1"  style="font-size: 1.2em">
                                    <strong style="padding-left: 5px"><?= $partnerSum ?></strong>
                                </div>
                            </div>

                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-8"><label class="control-label" style="margin:10px 0px 5px 0px;">Услуги</label></div>
                            </div>

                            <?php
                            $partnerSumService =0;
                            foreach ($partnerScopes as $scope) {
                                $partnerSumService+=$scope->amount*$scope->price
                                ?>
                                <div class="form-group" style="height: 25px;">
                                    <div class="col-xs-8">
                                        <?php if (!empty($serviceList)) { ?>
                                            <?= Html::dropDownList("Act[partnerServiceList][$scope->id][service_id]", $scope->service_id,
                                                $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                                        <?php } else { ?>
                                            <?= Html::textInput("Act[partnerServiceList][$scope->id][description]", $scope->description, ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                        <?php } ?>
                                    </div>
                                    <div class="col-xs-2">
                                        <?= Html::dropDownList("Act[partnerServiceList][$scope->id][amount]", (isset($scope->amount)) ? $scope->amount : '1.0', ['0.1' => '0.1', '0.2' => '0.2', '0.3' => '0.3', '0.4' => '0.4', '0.5' => '0.5', '0.6' => '0.6', '0.7' => '0.7', '0.8' => '0.8', '0.9' => '0.9', '1.0' => '1.0', '1.1' => '1.1', '1.2' => '1.2', '1.3' => '1.3', '1.4' => '1.4', '1.5' => '1.5', '1.6' => '1.6', '1.7' => '1.7', '1.8' => '1.8', '1.9' => '1.9', '2.0' => '2.0', '2.1' => '2.1', '2.2' => '2.2', '2.3' => '2.3', '2.4' => '2.4', '2.5' => '2.5', '2.6' => '2.6', '2.7' => '2.7', '2.8' => '2.8', '2.9' => '2.9', '3.0' => '3.0', '3.1' => '3.1', '3.2' => '3.2', '3.3' => '3.3', '3.4' => '3.4', '3.5' => '3.5', '3.6' => '3.6', '3.7' => '3.7', '3.8' => '3.8', '3.9' => '3.9', '4.0' => '4.0', '4.1' => '4.1', '4.2' => '4.2', '4.3' => '4.3', '4.4' => '4.4', '4.5' => '4.5', '4.6' => '4.6', '4.7' => '4.7', '4.8' => '4.8', '4.9' => '4.9', '5.0' => '5.0'], ['class' => 'form-control input-sm scope-num', 'prompt' => 'выберите кол-во н/ч']) ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <?= Html::textInput("Act[partnerServiceList][$scope->id][price]", $scope->price, ['class' => 'form-control input-sm resetable', 'placeholder' => 'цена']) ?>
                                    </div>
                                    <div class="col-xs-1" style="display: none;">
                                        <?= Html::input('text', "Act[partnerServiceList][$scope->id][parts]", '0', ['class' => 'form-control input-sm']) ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <button type="button" class="btn btn-primary input-sm removeButton">
                                            <i class="glyphicon glyphicon-minus"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-8">
                                    <?php if (!empty($serviceList)) { ?>
                                        <?= Html::dropDownList("Act[partnerServiceList][0][service_id]", '',
                                            $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                                    <?php } else { ?>
                                        <?= Html::textInput("Act[partnerServiceList][0][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-2">
                                    <?= Html::dropDownList("Act[partnerServiceList][0][amount]", '1.0', ['0.1' => '0.1', '0.2' => '0.2', '0.3' => '0.3', '0.4' => '0.4', '0.5' => '0.5', '0.6' => '0.6', '0.7' => '0.7', '0.8' => '0.8', '0.9' => '0.9', '1.0' => '1.0', '1.1' => '1.1', '1.2' => '1.2', '1.3' => '1.3', '1.4' => '1.4', '1.5' => '1.5', '1.6' => '1.6', '1.7' => '1.7', '1.8' => '1.8', '1.9' => '1.9', '2.0' => '2.0', '2.1' => '2.1', '2.2' => '2.2', '2.3' => '2.3', '2.4' => '2.4', '2.5' => '2.5', '2.6' => '2.6', '2.7' => '2.7', '2.8' => '2.8', '2.9' => '2.9', '3.0' => '3.0', '3.1' => '3.1', '3.2' => '3.2', '3.3' => '3.3', '3.4' => '3.4', '3.5' => '3.5', '3.6' => '3.6', '3.7' => '3.7', '3.8' => '3.8', '3.9' => '3.9', '4.0' => '4.0', '4.1' => '4.1', '4.2' => '4.2', '4.3' => '4.3', '4.4' => '4.4', '4.5' => '4.5', '4.6' => '4.6', '4.7' => '4.7', '4.8' => '4.8', '4.9' => '4.9', '5.0' => '5.0'], ['class' => 'form-control input-sm scope-num', 'prompt' => 'выберите кол-во н/ч']) ?>
                                </div>
                                <div class="col-xs-1">
                                    <?= Html::textInput("Act[partnerServiceList][0][price]", '', ['class' => 'form-control input-sm resetable', 'placeholder' => 'цена']) ?>
                                </div>
                                <div class="col-xs-1" style="display: none;">
                                    <?= Html::input('text', "Act[partnerServiceList][0][parts]", '0', ['class' => 'form-control input-sm']) ?>
                                </div>
                                <div class="col-xs-1">
                                    <button type="button" class="btn btn-primary input-sm addButton">
                                        <i class="glyphicon glyphicon-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-2 col-xs-offset-8" style="font-weight: bold;font-size: 1.2em">
                                    Сумма
                                </div>
                                <div class="col-xs-1"  style="font-size: 1.2em">
                                    <strong style="padding-left: 5px"><?= $partnerSumService ?></strong>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-2 col-xs-offset-8" style="font-weight: bold;font-size: 1.2em">
                                    Итого
                                </div>
                                <div class="col-xs-1"  style="font-size: 1.2em">
                                    <strong style="padding-left: 5px"><?= ($partnerSum + $partnerSumService) ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12" style="margin-top: 30px;">
                            <label class="control-label">Услуги клиента (<?= $model->client->name ?>)</label>

                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-8"><label class="control-label" style="margin:10px 0px 5px 0px;">Запасные части</label></div>
                            </div>

                            <?php
                            $clientSum=0;
                            foreach ($partsClientScopes as $scope) {
                                $clientSum+=$scope->amount*$scope->price
                                ?>
                                <div class="form-group" style="height: 25px;">
                                    <div class="col-xs-8">
                                        <?php if (!empty($serviceList)) { ?>
                                            <?= Html::dropDownList("Act[clientServiceList][$scope->id][service_id]", $scope->service_id,
                                                $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите наименование']) ?>
                                        <?php } else { ?>
                                            <?= Html::textInput("Act[clientServiceList][$scope->id][description]", $scope->description, ['class' => 'form-control input-sm', 'placeholder' => 'Наименование']) ?>
                                        <?php } ?>
                                    </div>
                                    <div class="col-xs-2">
                                        <?= Html::input('text', "Act[clientServiceList][$scope->id][amount]", $scope->amount, ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <?= Html::textInput("Act[clientServiceList][$scope->id][price]", $scope->price, ['class' => 'form-control input-sm resetable', 'placeholder' => 'цена']) ?>
                                    </div>
                                    <div class="col-xs-1" style="display: none;">
                                        <?= Html::input('text', "Act[clientServiceList][$scope->id][parts]", '1', ['class' => 'form-control input-sm']) ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <button type="button" class="btn btn-primary input-sm removeButton">
                                            <i class="glyphicon glyphicon-minus"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-8">
                                    <?php if (!empty($serviceList)) { ?>
                                        <?= Html::dropDownList("Act[clientServiceList][71][service_id]", '', $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите наименование']) ?>
                                    <?php } else { ?>
                                        <?= Html::textInput("Act[clientServiceList][71][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Наименование']) ?>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-2">
                                    <?= Html::input('text', "Act[clientServiceList][71][amount]", '1', ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                                </div>
                                <div class="col-xs-1">
                                    <?= Html::textInput("Act[clientServiceList][71][price]", '', ['class' => 'form-control input-sm', 'placeholder' => 'цена']) ?>
                                </div>
                                <div class="col-xs-1" style="display: none;">
                                    <?= Html::input('text', "Act[clientServiceList][71][parts]", '1', ['class' => 'form-control input-sm']) ?>
                                </div>
                                <div class="col-xs-1">
                                    <button type="button" class="btn btn-primary input-sm addButton">
                                        <i class="glyphicon glyphicon-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-2 col-xs-offset-8" style="font-weight: bold;font-size: 1.2em">
                                    Сумма
                                </div>
                                <div class="col-xs-1"  style="font-size: 1.2em">
                                    <strong style="padding-left: 5px"><?= $clientSum ?></strong>
                                </div>
                            </div>

                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-8"><label class="control-label" style="margin:10px 0px 5px 0px;">Услуги</label></div>
                            </div>

                            <?php
                            $clientSumService =0;
                            foreach ($clientScopes as $scope) {
                                $clientSumService+=$scope->amount*$scope->price
                                ?>
                                <div class="form-group" style="height: 25px;">
                                    <div class="col-xs-8">
                                        <?php if (!empty($serviceList)) { ?>
                                            <?= Html::dropDownList("Act[clientServiceList][$scope->id][service_id]", $scope->service_id,
                                                $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                                        <?php } else { ?>
                                            <?= Html::textInput("Act[clientServiceList][$scope->id][description]", $scope->description, ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                        <?php } ?>
                                    </div>
                                    <div class="col-xs-2">
                                        <?= Html::dropDownList("Act[clientServiceList][$scope->id][amount]", (isset($scope->amount)) ? $scope->amount : '1.0', ['0.1' => '0.1', '0.2' => '0.2', '0.3' => '0.3', '0.4' => '0.4', '0.5' => '0.5', '0.6' => '0.6', '0.7' => '0.7', '0.8' => '0.8', '0.9' => '0.9', '1.0' => '1.0', '1.1' => '1.1', '1.2' => '1.2', '1.3' => '1.3', '1.4' => '1.4', '1.5' => '1.5', '1.6' => '1.6', '1.7' => '1.7', '1.8' => '1.8', '1.9' => '1.9', '2.0' => '2.0', '2.1' => '2.1', '2.2' => '2.2', '2.3' => '2.3', '2.4' => '2.4', '2.5' => '2.5', '2.6' => '2.6', '2.7' => '2.7', '2.8' => '2.8', '2.9' => '2.9', '3.0' => '3.0', '3.1' => '3.1', '3.2' => '3.2', '3.3' => '3.3', '3.4' => '3.4', '3.5' => '3.5', '3.6' => '3.6', '3.7' => '3.7', '3.8' => '3.8', '3.9' => '3.9', '4.0' => '4.0', '4.1' => '4.1', '4.2' => '4.2', '4.3' => '4.3', '4.4' => '4.4', '4.5' => '4.5', '4.6' => '4.6', '4.7' => '4.7', '4.8' => '4.8', '4.9' => '4.9', '5.0' => '5.0'], ['class' => 'form-control input-sm scope-num', 'prompt' => 'выберите кол-во н/ч']) ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <?= Html::textInput("Act[clientServiceList][$scope->id][price]", $scope->price, ['class' => 'form-control input-sm resetable', 'placeholder' => 'цена']) ?>
                                    </div>
                                    <div class="col-xs-1" style="display: none;">
                                        <?= Html::input('text', "Act[clientServiceList][$scope->id][parts]", '0', ['class' => 'form-control input-sm']) ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <button type="button" class="btn btn-primary input-sm removeButton">
                                            <i class="glyphicon glyphicon-minus"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-8">
                                    <?php if (!empty($serviceList)) { ?>
                                        <?= Html::dropDownList("Act[clientServiceList][0][service_id]", '', $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                                    <?php } else { ?>
                                        <?= Html::textInput("Act[clientServiceList][0][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-2">
                                    <?= Html::dropDownList("Act[clientServiceList][0][amount]", '1.0', ['0.1' => '0.1', '0.2' => '0.2', '0.3' => '0.3', '0.4' => '0.4', '0.5' => '0.5', '0.6' => '0.6', '0.7' => '0.7', '0.8' => '0.8', '0.9' => '0.9', '1.0' => '1.0', '1.1' => '1.1', '1.2' => '1.2', '1.3' => '1.3', '1.4' => '1.4', '1.5' => '1.5', '1.6' => '1.6', '1.7' => '1.7', '1.8' => '1.8', '1.9' => '1.9', '2.0' => '2.0', '2.1' => '2.1', '2.2' => '2.2', '2.3' => '2.3', '2.4' => '2.4', '2.5' => '2.5', '2.6' => '2.6', '2.7' => '2.7', '2.8' => '2.8', '2.9' => '2.9', '3.0' => '3.0', '3.1' => '3.1', '3.2' => '3.2', '3.3' => '3.3', '3.4' => '3.4', '3.5' => '3.5', '3.6' => '3.6', '3.7' => '3.7', '3.8' => '3.8', '3.9' => '3.9', '4.0' => '4.0', '4.1' => '4.1', '4.2' => '4.2', '4.3' => '4.3', '4.4' => '4.4', '4.5' => '4.5', '4.6' => '4.6', '4.7' => '4.7', '4.8' => '4.8', '4.9' => '4.9', '5.0' => '5.0'], ['class' => 'form-control input-sm scope-num', 'prompt' => 'выберите кол-во н/ч']) ?>
                                </div>
                                <div class="col-xs-1">
                                    <?= Html::textInput("Act[clientServiceList][0][price]", '', ['class' => 'form-control input-sm', 'placeholder' => 'цена']) ?>
                                </div>
                                <div class="col-xs-1" style="display: none;">
                                    <?= Html::input('text', "Act[clientServiceList][0][parts]", '0', ['class' => 'form-control input-sm']) ?>
                                </div>
                                <div class="col-xs-1">
                                    <button type="button" class="btn btn-primary input-sm addButton">
                                        <i class="glyphicon glyphicon-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-2 col-xs-offset-8" style="font-weight: bold;font-size: 1.2em">
                                    Сумма
                                </div>
                                <div class="col-xs-1"  style="font-size: 1.2em">
                                    <strong style="padding-left: 5px"><?= $clientSum ?></strong>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-2 col-xs-offset-8" style="font-weight: bold;font-size: 1.2em">
                                    Итого
                                </div>
                                <div class="col-xs-1"  style="font-size: 1.2em">
                                    <strong style="padding-left: 5px"><?= ($clientSum + $clientSumService) ?></strong>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

            <?php } else { ?>

                <tr>
                    <td colspan="4">
                        <div class="col-sm-12">
                            <label class="control-label">Услуги партнера</label>
                            <?php foreach ($partnerScopes as $scope) { ?>
                                <div class="form-group" style="height: 25px;">
                                    <div class="col-xs-8">
                                        <?php if (!empty($serviceList)) { ?>
                                            <?= Html::dropDownList("Act[partnerServiceList][$scope->id][service_id]", $scope->service_id,
                                                $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                                        <?php } else { ?>
                                            <?= Html::textInput("Act[partnerServiceList][$scope->id][description]", $scope->description, ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                        <?php } ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <?= Html::input('number', "Act[partnerServiceList][$scope->id][amount]", $scope->amount, ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                                    </div>
                                    <div class="col-xs-2">
                                        <?= Html::textInput("Act[partnerServiceList][$scope->id][price]", $scope->price, ['class' => 'form-control input-sm', 'placeholder' => 'цена']) ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <button type="button" class="btn btn-primary input-sm removeButton">
                                            <i class="glyphicon glyphicon-minus"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-8">
                                    <?php if (!empty($serviceList)) { ?>
                                        <?= Html::dropDownList("Act[partnerServiceList][0][service_id]", '',
                                            $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                                    <?php } else { ?>
                                        <?= Html::textInput("Act[partnerServiceList][0][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-1">
                                    <?= Html::input('number', "Act[partnerServiceList][0][amount]", '1', ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                                </div>
                                <div class="col-xs-2">
                                    <?= Html::textInput("Act[partnerServiceList][0][price]", '', ['class' => 'form-control input-sm', 'placeholder' => 'цена']) ?>
                                </div>
                                <div class="col-xs-1">
                                    <button type="button" class="btn btn-primary input-sm addButton">
                                        <i class="glyphicon glyphicon-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12" style="margin-top: 30px;">
                            <label class="control-label">Услуги клиента</label>
                            <?php foreach ($clientScopes as $scope) { ?>
                                <div class="form-group" style="height: 25px;">
                                    <div class="col-xs-8">
                                        <?php if (!empty($serviceList)) { ?>
                                            <?= Html::dropDownList("Act[clientServiceList][$scope->id][service_id]", $scope->service_id,
                                                $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                                        <?php } else { ?>
                                            <?= Html::textInput("Act[clientServiceList][$scope->id][description]", $scope->description, ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                        <?php } ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <?= Html::input('number', "Act[clientServiceList][$scope->id][amount]", $scope->amount, ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                                    </div>
                                    <div class="col-xs-2">
                                        <?= Html::textInput("Act[clientServiceList][$scope->id][price]", $scope->price, ['class' => 'form-control input-sm', 'placeholder' => 'цена']) ?>
                                    </div>
                                    <div class="col-xs-1">
                                        <button type="button" class="btn btn-primary input-sm removeButton">
                                            <i class="glyphicon glyphicon-minus"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-8">
                                    <?php if (!empty($serviceList)) { ?>
                                        <?= Html::dropDownList("Act[clientServiceList][0][service_id]", '', $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                                    <?php } else { ?>
                                        <?= Html::textInput("Act[clientServiceList][0][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-1">
                                    <?= Html::input('number', "Act[clientServiceList][0][amount]", '1', ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                                </div>
                                <div class="col-xs-2">
                                    <?= Html::textInput("Act[clientServiceList][0][price]", '', ['class' => 'form-control input-sm', 'placeholder' => 'цена']) ?>
                                </div>
                                <div class="col-xs-1">
                                    <button type="button" class="btn btn-primary input-sm addButton">
                                        <i class="glyphicon glyphicon-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>

            <?php } ?>

            <tr>
        <td colspan="7">
            <?= Html::hiddenInput('__returnUrl', Yii::$app->request->referrer) ?>
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
        </td>
            </tr>
            </tbody>
        </table>
        <?php ActiveForm::end() ?>
    </div>
</div>
<?php

$modal = Modal::begin([
    'header' => '<h4>Перенести машину в другой филиал</h4>',
    'id' => 'showModal',
    'toggleButton' => ['label' => 'открыть окно', 'class' => 'btn btn-default hideButtonRemove', 'style' => 'display:none;'],
    'size' => 'modal-lg',
]);

$arrCompany = \frontend\controllers\CompanyController::getCompanyParents($model->client_id);

echo "<div class='removeList' style='margin-bottom:15px; font-size:15px; color:#000;'></div>";

echo Html::dropDownList("new_company", $model->car->company_id, $arrCompany, ['id' => 'new_company', 'class' => 'form-control']);
echo Html::buttonInput("Сохранить", ['id' => 'save_new_company', 'class' => 'btn btn-primary', 'style' => 'margin-top:20px; padding:7px 16px 6px 16px;']);

Modal::end();

?>