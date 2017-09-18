<?php

/**
 * @var $model \common\models\Act
 * @var $serviceList array
 */

use common\models\Car;
use common\models\Mark;
use common\models\Service;
use common\models\Type;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\jui\AutoComplete;
use yii\web\View;

if (!empty($serviceList)) {
    $fixedList = json_encode(Service::find()
        ->andWhere(['type' => $model->service_type])
        ->select('is_fixed')->indexBy('id')->column());

    // получаем значение фиксированных цен
    $compServList = json_encode(CompanyService::find()->where('`company_id`=' . Yii::$app->user->identity->company_id)->select('price')->indexBy('service_id')->orderBy('service_id ASC')->column());

    $script = <<< JS
    var serviceList = $fixedList;
    var compServList = $compServList;
    //$('.scope-price').hide();
    
    for (var i = 0; i < $('.scope-price').length; i++) {
        
        var fixed = serviceList[$('.scope-service').eq(i).val()];
        
        if(fixed > 0) {
            $('.scope-price').eq(i).attr('readonly', true);
        } else {
        $('.scope-price').eq(i).attr('readonly', false);
        }
        
        fixed = 1;
        
    }

    $(document).on('change', '.scope-service', function () {
        var fixed = serviceList[$(this).val()];
        if (fixed > 0) {
            //$(this).parent().parent().find('.scope-price').hide();
            $(this).parent().parent().find('.scope-price').attr('readonly', true);
            
            if ((typeof(compServList[$(this).val()]) != "undefined") && (compServList[$(this).val()] !== null)) {
            $(this).parent().parent().find('.scope-price').val(compServList[$(this).val()]);
            } else {
            $(this).parent().parent().find('.scope-price').val('0');
            }
            
        } else {
            //$(this).parent().parent().find('.scope-price').show();
            $(this).parent().parent().find('.scope-price').attr('readonly', false);
            $(this).parent().parent().find('.scope-price').val('');
        }
        
    });
    
    // Итоговая сумму
    var allPageTotalInput = $('.all_page_total');
    var allPageTotal = 0;
    
    function updateTotalPage() {
    allPageTotal = 0;
    var alltotalInputParts = $('.parts_all_total');
    var alltotalInput = $('.all_total');
        
    allPageTotal = Number(alltotalInputParts.val()) + Number(alltotalInput.val());
    allPageTotalInput.val(allPageTotal);
    }
    
    // Итоговая сумму
    
JS;
} else {
    $script = <<< JS
    
    // Итоговая сумму
    var allPageTotalInput = $('.all_page_total');
    var allPageTotal = 0;
    
    function updateTotalPage() {
    allPageTotal = 0;
    var alltotalInputParts = $('.parts_all_total');
    var alltotalInput = $('.all_total');
        
    allPageTotal = Number(alltotalInputParts.val()) + Number(alltotalInput.val());
    allPageTotalInput.val(allPageTotal);
    }
    
    // Итоговая сумму
    
JS;

}

$script .= <<< JS
    
    // Запасные части  - Итоговая сумма расчет
    var totalSumParts = 0;
    var allTotalParts = 0;
    var priceScopeParts = $('.parts-price');
    var numScopeParts = $('.parts-num');
    var totalInputParts = $('.parts_total_sum');
    var alltotalInputParts = $('.parts_all_total');

    if(totalInputParts.length == 1) {
        
    if((Number(priceScopeParts.val()) > 0) && (Number(numScopeParts.val()) > 0)) {
        totalSumParts = Number(priceScopeParts.val()) * Number(numScopeParts.val());
    }

    totalInputParts.val(totalSumParts);
    alltotalInputParts.val(totalSumParts);
    
    } else {
        
       allTotalParts = 0;
            
            for(var z = 0; z < totalInputParts.length; z++) {
                
                totalSumParts = 0;
                
                if((Number(priceScopeParts.eq(z).val()) > 0) && (Number(numScopeParts.eq(z).val()) > 0)) {
                    totalSumParts = Number(priceScopeParts.eq(z).val()) * Number(numScopeParts.eq(z).val());
                }

                totalInputParts.eq(z).val(totalSumParts);
                
                allTotalParts += totalSumParts;
                
            }
            
            alltotalInputParts.val(allTotalParts); 
        
    }
    
    $(document).on('change', '.parts-price', function () {
        
        priceScopeParts = $('.parts-price');
        numScopeParts = $('.parts-num');
        totalInputParts = $('.parts_total_sum');
        
        if(totalInputParts.length == 1) {
        
            totalSumParts = 0;
            
            if((Number(priceScopeParts.val()) > 0) && (Number(numScopeParts.val()) > 0)) {
               totalSumParts = Number(priceScopeParts.val()) * Number(numScopeParts.val());
            }

            totalInputParts.val(totalSumParts);
            alltotalInputParts.val(totalSumParts);
        
        } else {
            
            allTotalParts = 0;
            
            for(var z = 0; z < totalInputParts.length; z++) {
                
                totalSumParts = 0;
                
                if((Number(priceScopeParts.eq(z).val()) > 0) && (Number(numScopeParts.eq(z).val()) > 0)) {
                    totalSumParts = Number(priceScopeParts.eq(z).val()) * Number(numScopeParts.eq(z).val());
                }

                totalInputParts.eq(z).val(totalSumParts);
                
                allTotalParts += totalSumParts;
                
            }
            
            alltotalInputParts.val(allTotalParts);
            
        }
        updateTotalPage();
    });
    
    $(document).on('change', '.parts-num', function () {

        priceScopeParts = $('.parts-price');
        numScopeParts = $('.parts-num');
        totalInputParts = $('.parts_total_sum');
        
        if(totalInputParts.length == 1) {
        
            totalSumParts = 0;
            
            if((Number(priceScopeParts.val()) > 0) && (Number(numScopeParts.val()) > 0)) {
                totalSumParts = Number(priceScopeParts.val()) * Number(numScopeParts.val());
            }

            totalInputParts.val(totalSumParts);
            alltotalInputParts.val(totalSumParts);
        
        } else {
            
            allTotalParts = 0;
            
            for(var z = 0; z < totalInputParts.length; z++) {
                
                totalSumParts = 0;
                
                if((Number(priceScopeParts.eq(z).val()) > 0) && (Number(numScopeParts.eq(z).val()) > 0)) {
                    totalSumParts = Number(priceScopeParts.eq(z).val()) * Number(numScopeParts.eq(z).val());
                }

                totalInputParts.eq(z).val(totalSumParts);
                
                allTotalParts += totalSumParts;
                
            }
            
            alltotalInputParts.val(allTotalParts);
            
        }
        updateTotalPage();
    });
    
    $('.table-bordered tbody tr:eq(2) td[colspan=5]').bind("DOMSubtreeModified",function(){

        priceScopeParts = $('.parts-price');
        numScopeParts = $('.parts-num');
        totalInputParts = $('.parts_total_sum');
        
        if(totalInputParts.length == 1) {
        
            totalSumParts = 0;
            
            if((Number(priceScopeParts.val()) > 0) && (Number(numScopeParts.val()) > 0)) {
                totalSumParts = Number(priceScopeParts.val()) * Number(numScopeParts.val());
            }

            totalInputParts.val(totalSumParts);
            alltotalInputParts.val(totalSumParts);
        
        } else {
            
            allTotalParts = 0;
            
            for(var z = 0; z < totalInputParts.length; z++) {
                
                totalSumParts = 0;
                
                if((Number(priceScopeParts.eq(z).val()) > 0) && (Number(numScopeParts.eq(z).val()) > 0)) {
                    totalSumParts = Number(priceScopeParts.eq(z).val()) * Number(numScopeParts.eq(z).val());
                }

                totalInputParts.eq(z).val(totalSumParts);
                
                allTotalParts += totalSumParts;
                
            }
            
            alltotalInputParts.val(allTotalParts);
            
        }
        updateTotalPage();
    });
    
    // Запасные части  - Итоговая сумма расчет
    
    // Услуги  - Итоговая сумма расчет
    var totalSum = 0;
    var allTotal = 0;
    var priceScope = $('.scope-price');
    var numScope = $('.scope-num');
    var totalInput = $('.total_sum');
    var alltotalInput = $('.all_total');

    if(totalInput.length == 1) {
        
    if((Number(priceScope.val()) > 0) && (Number(numScope.val()) > 0)) {
        totalSum = Number(priceScope.val()) * Number(numScope.val());
    }

    totalInput.val(totalSum);
    alltotalInput.val(totalSum);
    
    } else {
        
       allTotal = 0;
            
            for(var z = 0; z < totalInput.length; z++) {
                
                totalSum = 0;
                
                if((Number(priceScope.eq(z).val()) > 0) && (Number(numScope.eq(z).val()) > 0)) {
                    totalSum = Number(priceScope.eq(z).val()) * Number(numScope.eq(z).val());
                }

                totalInput.eq(z).val(totalSum);
                
                allTotal += totalSum;
                
            }
            
            alltotalInput.val(allTotal); 
        
    }
    
    $(document).on('change', '.scope-price', function () {
        
        priceScope = $('.scope-price');
        numScope = $('.scope-num');
        totalInput = $('.total_sum');
        
        if(totalInput.length == 1) {
        
            totalSum = 0;
            
            if((Number(priceScope.val()) > 0) && (Number(numScope.val()) > 0)) {
               totalSum = Number(priceScope.val()) * Number(numScope.val());
            }

            totalInput.val(totalSum);
            alltotalInput.val(totalSum);
        
        } else {
            
            allTotal = 0;
            
            for(var z = 0; z < totalInput.length; z++) {
                
                totalSum = 0;
                
                if((Number(priceScope.eq(z).val()) > 0) && (Number(numScope.eq(z).val()) > 0)) {
                    totalSum = Number(priceScope.eq(z).val()) * Number(numScope.eq(z).val());
                }

                totalInput.eq(z).val(totalSum);
                
                allTotal += totalSum;
                
            }
            
            alltotalInput.val(allTotal);
            
        }
        updateTotalPage();
    });
    
    $(document).on('change', '.scope-num', function () {

        priceScope = $('.scope-price');
        numScope = $('.scope-num');
        totalInput = $('.total_sum');
        
        if(totalInput.length == 1) {
        
            totalSum = 0;
            
            if((Number(priceScope.val()) > 0) && (Number(numScope.val()) > 0)) {
                totalSum = Number(priceScope.val()) * Number(numScope.val());
            }

            totalInput.val(totalSum);
            alltotalInput.val(totalSum);
        
        } else {
            
            allTotal = 0;
            
            for(var z = 0; z < totalInput.length; z++) {
                
                totalSum = 0;
                
                if((Number(priceScope.eq(z).val()) > 0) && (Number(numScope.eq(z).val()) > 0)) {
                    totalSum = Number(priceScope.eq(z).val()) * Number(numScope.eq(z).val());
                }

                totalInput.eq(z).val(totalSum);
                
                allTotal += totalSum;
                
            }
            
            alltotalInput.val(allTotal);
            
        }
        updateTotalPage();
    });
    
    $('.table-bordered tbody tr:eq(4) td[colspan=5]').bind("DOMSubtreeModified",function(){

        priceScope = $('.scope-price');
        numScope = $('.scope-num');
        totalInput = $('.total_sum');
        
        if(totalInput.length == 1) {
        
            totalSum = 0;
            
            if((Number(priceScope.val()) > 0) && (Number(numScope.val()) > 0)) {
                totalSum = Number(priceScope.val()) * Number(numScope.val());
            }

            totalInput.val(totalSum);
            alltotalInput.val(totalSum);
        
        } else {
            
            allTotal = 0;
            
            for(var z = 0; z < totalInput.length; z++) {
                
                totalSum = 0;
                
                if((Number(priceScope.eq(z).val()) > 0) && (Number(numScope.eq(z).val()) > 0)) {
                    totalSum = Number(priceScope.eq(z).val()) * Number(numScope.eq(z).val());
                }

                totalInput.eq(z).val(totalSum);
                
                allTotal += totalSum;
                
            }
            
            alltotalInput.val(allTotal);
            
        }
        updateTotalPage();
    });
    
    // Услуги  - Итоговая сумма расчет
    
    // Итоговая сумму    
    allPageTotal = totalSumParts + totalSum;
    allPageTotalInput.val(allPageTotal);
    // Итоговая сумму
    
JS;

$this->registerJs($script, View::POS_READY);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавить машину
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $model->isNewRecord ?  [
                'act/create',
                'type' => $model->service_type,
            ] : [
                'act/update',
                'id' => $model->id
            ],
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
                            'value' => date('d-m-Y'),
                        ]
                    ])->error(false) ?>
                </td>
                <td style="min-width: 100px">
                    <?= $form->field($model, 'card_number')->textInput(); ?>
                </td>
                <td>
                    <?= $form->field($model, 'car_number')->widget(AutoComplete::classname(),
                    [
                        'options'       => ['class' => 'form-control', 'autocomplete' => 'on'],
                        'clientOptions' => [
                            'source'    => Car::find()->select('number as value')->asArray()->all(),
                            'minLength' => '2',
                            'autoFill'  => true,
                        ],
                        'clientEvents'  => [
                            'response' => 'function (event, ui) {
                                if(ui.content.length==0){
                                    $("#act-mark_id").show();
                                    $("#act-type_id").show();
                                }else{
                                    $("#act-mark_id").hide();
                                    $("#act-type_id").hide();
                                }
                            }'
                        ],
                    ])->error(false) ?>
                </td>
                <td style="width: 20%">
                    <?= $form->field($model, 'mark_id')
                        ->dropdownList(Mark::getMarkList(), ['style' => 'display:none'])
                        ->error(false) ?>
                </td>
                <td style="width: 20%">
                    <?= $form->field($model, 'type_id')
                        ->dropdownList(Type::getTypeList(), ['max-width', 'style' => 'display:none'])
                        ->error(false) ?>
                </td>
            </tr>
            <tr><td colspan="5" style="color:#000; font-size:14px; padding-left:22px;"><label class="control-label">Запасные части</label></td></tr>
            <tr>
                <td colspan="5">

                    <div class="form-group" style="height: 5px;">
                        <div class="col-xs-6">
                            <label class="control-label">Наименование</label>
                        </div>
                        <div class="col-xs-1">
                            <label class="control-label">Стоимость</label>
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label">Количество</label>
                        </div>
                        <div class="col-xs-1"><label class="control-label">Итого</label></div>
                        <div class="col-xs-1"></div>
                    </div>

                    <?php if(isset($partsPartnerScopes)) {
                        $ipS = 0;
                        foreach ($partsPartnerScopes as $scope) {?>
                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-6">
                                    <?php if (!empty($serviceList)) { ?>
                                        <?= Html::dropDownList("Act[serviceList][$scope->id][service_id]", (isset($scope->service_id)) ? $scope->service_id : '', $serviceList, ['class' => 'form-control input-sm scope-parts', 'prompt' => 'выберите наименование']) ?>
                                    <?php } else { ?>
                                        <?= Html::textInput("Act[serviceList][$scope->id][description]", (isset($scope->description)) ? $scope->description : '', ['class' => 'form-control input-sm', 'placeholder' => 'Наименование']) ?>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-1">
                                    <?= Html::input('number', "Act[serviceList][$scope->id][price]", (isset($scope->price)) ? $scope->price : 0, ['class' => 'form-control input-sm parts-price', 'placeholder' => 'Цена']) ?>
                                </div>
                                <div class="col-xs-2">
                                    <?= Html::input('text', "Act[serviceList][$scope->id][amount]", (isset($scope->amount)) ? $scope->amount : 1, ['class' => 'not-null form-control input-sm parts-num', 'placeholder' => 'Количество']) ?>
                                </div>
                                <div class="col-xs-1">
                                    <?= Html::input('text', 'total', '0', ['class' => 'form-control input-sm parts_total_sum', 'placeholder' => 'Итого', 'disabled' => 'disabled']) ?>
                                </div>
                                <div class="col-xs-1" style="display: none;">
                                    <?= Html::input('text', "Act[serviceList][$scope->id][parts]", '1', ['class' => 'not-null form-control input-sm']) ?>
                                </div>
                                <div class="col-xs-1">

                                    <?php
                                    if(($ipS + 1) == count($partsPartnerScopes)) {
                                        ?>

                                        <button type="button" class="btn btn-primary input-sm addButton"><i
                                                    class="glyphicon glyphicon-plus"></i></button>

                                    <?php } else { ?>

                                        <button type="button" class="btn btn-primary input-sm removeButton">
                                            <i class="glyphicon glyphicon-minus"></i>
                                        </button>

                                    <?php } ?>

                                </div>
                            </div>

                            <?php

                            $ipS++;

                        } ?>

                        <div style="height: 30px;">
                            <div class="col-xs-6">
                            </div>
                            <div class="col-xs-1">
                            </div>
                            <div class="col-xs-2" align="right" style="padding-top: 8px; font-size: 13px;"><label class="control-label">Всего:</label></div>
                            <div class="col-xs-1">
                                <?= Html::input('number', 'parts_all_total', '0', ['class' => 'form-control input-sm parts_all_total', 'placeholder' => 'Всего', 'disabled' => 'disabled']) ?>
                            </div>
                            <div class="col-xs-1">
                            </div>
                        </div>

                    <?php } else { ?>

                        <div class="form-group" style="height: 25px;">
                            <div class="col-xs-6">
                                <?php if (!empty($serviceList)) { ?>
                                    <?= Html::dropDownList("Act[serviceList][71][service_id]", '', $serviceList, ['class' => 'form-control input-sm scope-parts', 'prompt' => 'выберите наименование']) ?>
                                <?php } else { ?>
                                    <?= Html::textInput("Act[serviceList][71][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Наименование']) ?>
                                <?php } ?>
                            </div>
                            <div class="col-xs-1">
                                <?= Html::input('number', "Act[serviceList][71][price]", 0, ['class' => 'form-control input-sm parts-price', 'placeholder' => 'Цена']) ?>
                            </div>
                            <div class="col-xs-2">
                                <?= Html::input('text', "Act[serviceList][71][amount]", 1, ['class' => 'not-null form-control input-sm parts-num', 'placeholder' => 'Количество']) ?>
                            </div>
                            <div class="col-xs-1">
                                <?= Html::input('text', 'total', '0', ['class' => 'form-control input-sm parts_total_sum', 'placeholder' => 'Итого', 'disabled' => 'disabled']) ?>
                            </div>
                            <div class="col-xs-1" style="display: none;">
                                <?= Html::input('text', 'Act[serviceList][71][parts]', '1', ['class' => 'not-null form-control input-sm']) ?>
                            </div>
                            <div class="col-xs-1">
                                <button type="button" class="btn btn-primary input-sm addButton"><i
                                            class="glyphicon glyphicon-plus"></i></button>
                            </div>
                        </div>

                        <div style="height: 30px;">
                            <div class="col-xs-6">
                            </div>
                            <div class="col-xs-1">
                            </div>
                            <div class="col-xs-2" align="right" style="padding-top: 8px; font-size: 13px;"><label class="control-label">Всего:</label></div>
                            <div class="col-xs-1">
                                <?= Html::input('number', 'parts_all_total', '0', ['class' => 'form-control input-sm parts_all_total', 'placeholder' => 'Всего', 'disabled' => 'disabled']) ?>
                            </div>
                            <div class="col-xs-1">
                            </div>
                        </div>

                    <?php } ?>

                </td>
            </tr>
            <tr><td colspan="5" style="color:#000; font-size:14px; padding-left:22px;"><label class="control-label">Услуги</label></td></tr>
            <tr>
                <td colspan="5">

                    <div class="form-group" style="height: 5px;">
                        <div class="col-xs-6">
                            <label class="control-label">Услуга</label>
                        </div>
                        <div class="col-xs-1">
                            <label class="control-label">Стоимость н/ч</label>
                        </div>
                        <div class="col-xs-2">
                            <label class="control-label">Кол-во н/ч</label>
                        </div>
                        <div class="col-xs-1"><label class="control-label">Итого</label></div>
                        <div class="col-xs-1"></div>
                    </div>

                    <?php if(isset($partnerScopes)) {
                        $ipS = 0;
                        foreach ($partnerScopes as $scope) {?>
                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-6">
                                    <?php if (!empty($serviceList)) { ?>
                                        <?= Html::dropDownList("Act[serviceList][$scope->id][service_id]", (isset($scope->service_id)) ? $scope->service_id : '', $serviceList, ['class' => 'form-control input-sm scope-service', 'prompt' => 'выберите услугу']) ?>
                                    <?php } else { ?>
                                        <?= Html::textInput("Act[serviceList][$scope->id][description]", (isset($scope->description)) ? $scope->description : '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-1">
                                    <?= Html::input('number', "Act[serviceList][$scope->id][price]", (isset($scope->price)) ? $scope->price : 0, ['class' => 'form-control input-sm scope-price', 'placeholder' => 'Стоимость']) ?>
                                </div>
                                <div class="col-xs-2">
                                    <?= Html::dropDownList("Act[serviceList][$scope->id][amount]", (isset($scope->amount)) ? $scope->amount : '1.0', ['0.1' => '0.1', '0.2' => '0.2', '0.3' => '0.3', '0.4' => '0.4', '0.5' => '0.5', '0.6' => '0.6', '0.7' => '0.7', '0.8' => '0.8', '0.9' => '0.9', '1.0' => '1.0', '1.1' => '1.1', '1.2' => '1.2', '1.3' => '1.3', '1.4' => '1.4', '1.5' => '1.5', '1.6' => '1.6', '1.7' => '1.7', '1.8' => '1.8', '1.9' => '1.9', '2.0' => '2.0', '2.1' => '2.1', '2.2' => '2.2', '2.3' => '2.3', '2.4' => '2.4', '2.5' => '2.5', '2.6' => '2.6', '2.7' => '2.7', '2.8' => '2.8', '2.9' => '2.9', '3.0' => '3.0', '3.1' => '3.1', '3.2' => '3.2', '3.3' => '3.3', '3.4' => '3.4', '3.5' => '3.5', '3.6' => '3.6', '3.7' => '3.7', '3.8' => '3.8', '3.9' => '3.9', '4.0' => '4.0', '4.1' => '4.1', '4.2' => '4.2', '4.3' => '4.3', '4.4' => '4.4', '4.5' => '4.5', '4.6' => '4.6', '4.7' => '4.7', '4.8' => '4.8', '4.9' => '4.9', '5.0' => '5.0'], ['class' => 'form-control input-sm scope-num', 'prompt' => 'выберите кол-во н/ч']) ?>
                                </div>
                                <div class="col-xs-1">
                                    <?= Html::input('text', 'total', '0', ['class' => 'form-control input-sm total_sum', 'placeholder' => 'Итого', 'disabled' => 'disabled']) ?>
                                </div>
                                <div class="col-xs-1" style="display: none;">
                                    <?= Html::input('text', "Act[serviceList][$scope->id][parts]", '0', ['class' => 'form-control input-sm']) ?>
                                </div>
                                <div class="col-xs-1">

                                    <?php
                                    if(($ipS + 1) == count($partnerScopes)) {
                                        ?>

                                        <button type="button" class="btn btn-primary input-sm addButton"><i
                                                    class="glyphicon glyphicon-plus"></i></button>

                                    <?php } else { ?>

                                        <button type="button" class="btn btn-primary input-sm removeButton">
                                            <i class="glyphicon glyphicon-minus"></i>
                                        </button>

                                    <?php } ?>

                                </div>
                            </div>

                            <?php

                            $ipS++;

                        } ?>

                        <div style="height: 30px;">
                            <div class="col-xs-6">
                            </div>
                            <div class="col-xs-1">
                            </div>
                            <div class="col-xs-2" align="right" style="padding-top: 8px; font-size: 13px;"><label class="control-label">Всего:</label></div>
                            <div class="col-xs-1">
                                <?= Html::input('number', 'all_total', '0', ['class' => 'form-control input-sm all_total', 'placeholder' => 'Всего', 'disabled' => 'disabled']) ?>
                            </div>
                            <div class="col-xs-1">
                            </div>
                        </div>

                    <?php } else { ?>

                        <div class="form-group" style="height: 25px;">
                            <div class="col-xs-6">
                                <?php if (!empty($serviceList)) { ?>
                                    <?= Html::dropDownList("Act[serviceList][0][service_id]", '', $serviceList, ['class' => 'form-control input-sm scope-service', 'prompt' => 'выберите услугу']) ?>
                                <?php } else { ?>
                                    <?= Html::textInput("Act[serviceList][0][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                <?php } ?>
                            </div>
                            <div class="col-xs-1">
                                <?= Html::input('number', "Act[serviceList][0][price]", 0, ['class' => 'form-control input-sm scope-price', 'placeholder' => 'Стоимость']) ?>
                            </div>
                            <div class="col-xs-2">
                                <?= Html::dropDownList("Act[serviceList][0][amount]", '1.0', ['0.1' => '0.1', '0.2' => '0.2', '0.3' => '0.3', '0.4' => '0.4', '0.5' => '0.5', '0.6' => '0.6', '0.7' => '0.7', '0.8' => '0.8', '0.9' => '0.9', '1.0' => '1.0', '1.1' => '1.1', '1.2' => '1.2', '1.3' => '1.3', '1.4' => '1.4', '1.5' => '1.5', '1.6' => '1.6', '1.7' => '1.7', '1.8' => '1.8', '1.9' => '1.9', '2.0' => '2.0', '2.1' => '2.1', '2.2' => '2.2', '2.3' => '2.3', '2.4' => '2.4', '2.5' => '2.5', '2.6' => '2.6', '2.7' => '2.7', '2.8' => '2.8', '2.9' => '2.9', '3.0' => '3.0', '3.1' => '3.1', '3.2' => '3.2', '3.3' => '3.3', '3.4' => '3.4', '3.5' => '3.5', '3.6' => '3.6', '3.7' => '3.7', '3.8' => '3.8', '3.9' => '3.9', '4.0' => '4.0', '4.1' => '4.1', '4.2' => '4.2', '4.3' => '4.3', '4.4' => '4.4', '4.5' => '4.5', '4.6' => '4.6', '4.7' => '4.7', '4.8' => '4.8', '4.9' => '4.9', '5.0' => '5.0'], ['class' => 'form-control input-sm scope-num', 'prompt' => 'выберите кол-во н/ч']) ?>
                            </div>
                            <div class="col-xs-1">
                                <?= Html::input('text', 'total', '0', ['class' => 'form-control input-sm total_sum', 'placeholder' => 'Итого', 'disabled' => 'disabled']) ?>
                            </div>
                            <div class="col-xs-1" style="display: none;">
                                <?= Html::input('text', 'Act[serviceList][0][parts]', '0', ['class' => 'form-control input-sm']) ?>
                            </div>
                            <div class="col-xs-1">
                                <button type="button" class="btn btn-primary input-sm addButton"><i
                                            class="glyphicon glyphicon-plus"></i></button>
                            </div>
                        </div>

                        <div style="height: 30px;">
                            <div class="col-xs-6">
                            </div>
                            <div class="col-xs-1">
                            </div>
                            <div class="col-xs-2" align="right" style="padding-top: 8px; font-size: 13px;"><label class="control-label">Всего:</label></div>
                            <div class="col-xs-1">
                                <?= Html::input('number', 'all_total', '0', ['class' => 'form-control input-sm all_total', 'placeholder' => 'Всего', 'disabled' => 'disabled']) ?>
                            </div>
                            <div class="col-xs-1">
                            </div>
                        </div>

                    <?php } ?>

                </td>
            </tr>
            <tr>
                <td colspan="7">
                    <div style="height: 30px;">
                        <div class="col-xs-6">
                            <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
                        </div>
                        <div class="col-xs-1">
                            <?= Html::hiddenInput('__returnUrl', Yii::$app->request->referrer) ?>
                        </div>
                        <div class="col-xs-2" align="right" style="padding-top: 8px; font-size: 13px;"><label class="control-label">Итого:</label></div>
                        <div class="col-xs-1">
                            <?= Html::input('number', 'all_page_total', '0', ['class' => 'form-control input-sm all_page_total', 'placeholder' => 'Всего', 'disabled' => 'disabled']) ?>
                        </div>
                        <div class="col-xs-1">
                        </div>
                    </div>
                </td>
            </tr>

            <!-- Выводим кнопку для преждевременного закрытия загрузок -->
            <?php

            if(($model->service_type == 2) || ($model->service_type == 3) || ($model->service_type == 4) || ($model->service_type == 5)) {

                // Текушая дата
                $dateNow = time();

                // Текущий день недели
                $dayNow = date("j", $dateNow);

                // Название месяцев
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

                // Если сегодня первый день месяца
                if (($dayNow >= 1) && ($dayNow < 15)) {

                    // Дата прошлого месяца
                    $dateYesterday = strtotime("-1 month");

                    $lockedLisk = \common\models\Lock::checkLocked(date('n-Y', $dateYesterday), $model->service_type);
                    $is_locked = false;

                    if (count($lockedLisk) > 0) {

                        $closeAll = false;
                        $closeCompany = false;

                        for ($c = 0; $c < count($lockedLisk); $c++) {
                            if ($lockedLisk[$c]["company_id"] == 0) {
                                $closeAll = true;
                            }
                            if ($lockedLisk[$c]["company_id"] == Yii::$app->user->identity->company_id) {
                                $closeCompany = true;
                            }
                        }

                        if (($closeAll == true) && ($closeCompany == false)) {
                            $is_locked = true;
                        } elseif (($closeAll == false) && ($closeCompany == true)) {
                            $is_locked = true;
                        }

                    }

                    // Название прошлого месяца
                    $mountYesterday = date("n", $dateYesterday) - 1;
                    $mountYesterday = $months[$mountYesterday];

                    if ($is_locked == false) {

                        echo "<tr><td colspan=\"7\">Если Вы загрузили всю необходимую информацию за " . $mountYesterday . " месяц и Вам нечего больше добавить, то просим Вас нажать на кнопку  \"Закрыть загрузку\". После нажатия на эту кнопку, возможностей добавить или изменить какие либо данные за этот период не будет.
                        <br /><br /><a class=\"btn btn-danger btn-sm\" href=\"/act/closeload?type=" . $model->service_type . "&company=" . Yii::$app->user->identity->company_id . "&period=" . date('n-Y', $dateYesterday) . "\" onclick=\"
                        button = $(this); $.ajax({
                type     :'GET',
                cache    : false,
                url  : $(this).attr('href'),
                success  : function(response) {
                if(response == 1) {
                location.reload();
                }
                                    
                }
                });
                return false;
                        \">Закрыть загрузку</a>
                        
                        </td></tr>";

                    }

                }

                if((isset($showError)) && ($showError != '')) {
                    if(isset($model->getErrors()['period'][0])) {
                        $mountYesterday = $model->getErrors()['period'][0] - 1;
                        $mountYesterday = $months[$mountYesterday];
                        echo "<tr><td colspan=\"7\" style=\"color:#ff0000;\">Вы не можете загрузить информацию за " . $mountYesterday . " месяц, так как загрузка за этот месяц завершена. При возникновении вопросом, просим связаться с нами. Контакты указаны в программе в боковом меню, в разделе контакты.</td></tr>";
                    } else if(isset($model->getErrors()['client'][0])) {
                        echo "<tr><td colspan=\"7\" style=\"color:#ff0000;\">" . $model->getErrors()['client'][0] . "</td></tr>";
                    }
                }

            }
            ?>
            <!-- END Выводим кнопку для преждевременного закрытия загрузок -->

            </tbody>
        </table>
        <?php ActiveForm::end() ?>
    </div>
</div>