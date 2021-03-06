<?php

/**
 * @var $model \common\models\Act
 * @var $serviceList array
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\date\DatePicker;
use common\models\Mark;
use common\models\Type;
use common\models\Car;
use yii\jui\AutoComplete;
use yii\web\View;
use yii\helpers\Url;

$actionLinkType = Url::to('@web/car/gettypeid');

$script = <<< JS

    // получаем тип тс по номеру
    $(document).on('change', '#act-car_number', function () {
                  $.ajax({
                type     :'POST',
                cache    : false,
                data: 'number=' + $(this).val(),
                url  : '$actionLinkType',
                success  : function(data) {
                    
                var response = $.parseJSON(data);
                
                if (response.success == true) { 
                // Удачно
                $("#act-type_id").val(response.type_id);
                
                } else {
                // Неудачно
                $("#act-type_id").val('');
                $("#act-mark_id").val('');
                }
                
                }
                });
    });
    

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
                        ->dropdownList(Type::getTypeList(), ['max-width'])
                        ->error(false) ?>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    <div class="form-group row" style="height: 5px;">
                        <div class="col-xs-12">
                            <label class="control-label">Услуга</label>
                        </div>
                    </div>

                    <?php if(isset($partnerScopes)) {
                        $ipS = 0;
                        foreach ($partnerScopes as $scope) {

                            // Убираем нули после запятой если указано целое число
                            $intVal = (Int) $scope->price;
                            $checkVal = $scope->price - $intVal;

                            if($checkVal > 0) {
                            } else {
                                $scope->price = $intVal;
                            }

                            ?>
                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-6">
                                    <?php if (!empty($serviceList)) { ?>
                                        <?= Html::dropDownList("Act[serviceList][$scope->id][service_id]", (isset($scope->service_id)) ? $scope->service_id : '', $serviceList, ['class' => 'form-control input-sm scope-service', 'prompt' => 'выберите услугу']) ?>
                                    <?php } else { ?>
                                        <?= Html::textInput("Act[serviceList][$scope->id][description]", (isset($scope->description)) ? $scope->description : '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-2">
                                    <?= Html::input('number', "Act[serviceList][$scope->id][amount]", (isset($scope->amount)) ? $scope->amount : 1, ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                                </div>
                                <div class="col-xs-2">
                                    <?= Html::input('text', "Act[serviceList][$scope->id][price]", (isset($scope->price)) ? $scope->price : 0, ['class' => 'form-control input-sm scope-price', 'placeholder' => 'Цена']) ?>
                                </div>
                                <div class="col-xs-2">

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

                    <?php } else { ?>

                        <div class="form-group" style="height: 25px;">
                            <div class="col-xs-6">
                                <?php if (!empty($serviceList)) { ?>
                                    <?= Html::dropDownList("Act[serviceList][0][service_id]", '', $serviceList, ['class' => 'form-control input-sm scope-service', 'prompt' => 'выберите услугу']) ?>
                                <?php } else { ?>
                                    <?= Html::textInput("Act[serviceList][0][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                <?php } ?>
                            </div>
                            <div class="col-xs-2">
                                <?= Html::input('number', "Act[serviceList][0][amount]", 1, ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                            </div>
                            <div class="col-xs-2">
                                <?= Html::input('text', "Act[serviceList][0][price]", 0, ['class' => 'form-control input-sm scope-price', 'placeholder' => 'Цена']) ?>
                            </div>
                            <div class="col-xs-2">
                                <button type="button" class="btn btn-primary input-sm addButton"><i
                                            class="glyphicon glyphicon-plus"></i></button>
                            </div>
                        </div>

                    <?php } ?>

                </td>
            </tr>
            <tr>
                <td colspan="7">
                    <?= Html::hiddenInput('__returnUrl', Yii::$app->request->referrer) ?>
                    <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
                </td>
            </tr>

            <!-- Выводим кнопку для преждевременного закрытия загрузок -->
            <?php

            if(($model->service_type == 2) || ($model->service_type == 3) || ($model->service_type == 4) || ($model->service_type == 5) || ($model->service_type == 6)) {

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