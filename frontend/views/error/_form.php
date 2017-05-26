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
            <?= $model->service_type == Service::TYPE_WASH ? $form->field($model, 'image')->fileInput(['class' => 'form-control'])->error(false) : '' ?>
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
        </td>
        <td>
            <?= $form->field($model, 'type_id')->dropdownList(Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column())->error(false) ?>
        </td>
            </tr>
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