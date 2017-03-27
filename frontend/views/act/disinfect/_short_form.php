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
use common\models\Card;
use common\models\Car;
use kartik\select2\Select2;
use yii\jui\AutoComplete;

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
                    <?= $form->field($model, 'number')->widget(AutoComplete::classname(),
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

                    <div class="form-group" style="height: 25px;">
                        <div class="col-xs-6">
                            <?php if (!empty($serviceList)) { ?>
                                <?= Html::dropDownList("Act[serviceList][0][service_id]", '', $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                            <?php } else { ?>
                                <?= Html::textInput("Act[serviceList][0][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                            <?php } ?>
                        </div>
                        <div class="col-xs-1">
                            <?= Html::hiddenInput("Act[serviceList][0][amount]", 1, ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                            <?= Html::hiddenInput("Act[serviceList][0][price]", 0, ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                            <button type="button" class="btn btn-primary input-sm addButton"><i class="glyphicon glyphicon-plus"></i></button>
                        </div>
                    </div>
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

            if(($model->service_type == 2) || ($model->service_type == 3) || ($model->service_type == 4) || ($model->service_type == 5)) {

                // Текушая дата
                $DateNow = time();

                // Текущий день недели
                $DayNow = date("j", $DateNow);

                // Если сегодня первый день месяца
                if ($DayNow == 1) {

                    // Дата прошлого дня
                    $DateYesterday = $DateNow - 86400;

                    $LockedLisk = \common\models\Lock::CheckLocked(date('n-Y', $DateYesterday), $model->service_type);
                    $is_locked = false;

                    if (count($LockedLisk) > 0) {

                        $CloseAll = false;
                        $CloseCompany = false;

                        for ($c = 0; $c < count($LockedLisk); $c++) {
                            if ($LockedLisk[$c]["company_id"] == 0) {
                                $CloseAll = true;
                            }
                            if ($LockedLisk[$c]["company_id"] == Yii::$app->user->identity->company_id) {
                                $CloseCompany = true;
                            }
                        }

                        if (($CloseAll == true) && ($CloseCompany == false)) {
                            $is_locked = true;
                        } elseif (($CloseAll == false) && ($CloseCompany == true)) {
                            $is_locked = true;
                        }

                    }

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

                    // Название прошлого месяца
                    $MountYesterday = date("n", $DateYesterday) - 1;
                    $MountYesterday = $months[$MountYesterday];

                    if ($is_locked == false) {

                        echo "<tr><td colspan=\"7\">Если Вы загрузили всю необходимую информацию за " . $MountYesterday . " месяц и Вам нечего больше добавить, то просим Вас нажать на кнопку  \"Закрыть загрузку\". После нажатия на эту кнопку, возможностей добавить или изменить какие либо данные за этот период не будет.
                        <br /><br /><a class=\"btn btn-danger btn-sm\" href=\"/act/closeload?type=" . $model->service_type . "&company=" . Yii::$app->user->identity->company_id . "&period=" . date('n-Y', $DateYesterday) . "\" onclick=\"
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

                    } else {
                        echo "<tr><td colspan=\"7\">Вы не можете загрузить информацию за " . $MountYesterday . " месяц, так как загрузка за этот месяц завершена. При возникновении вопросом, просим связаться с нами. Контакты указаны в программе в боковом меню, в разделе контакты.</td></tr>";
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