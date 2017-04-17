<?php

/**
 * @var $model \common\models\Act
 * @var $serviceList array
 */

use common\components\ArrayHelper;
use common\models\Car;
use common\models\Mark;
use common\models\Type;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
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
                <td style="width: 100px">
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
                <td style="width: 100px">
                    <?= $form->field($model, 'card_number')->textInput(); ?>
                </td>
                <td class="complex-number" >
                    <label class="control-label" for="act-card_id">Номер <span class="extra-number" style="display:none">и номер прицепа</span></label>
                    <div class="input-group">
                        <?= AutoComplete::widget([
                            'model' => $model,
                            'attribute' => 'car_number',
                            'options' => ['class' => 'form-control main-number', 'autocomplete' => 'on', 'style' => 'width: 50%'],
                            'clientOptions' => [
                                'source' => Car::find()->where(['!=', 'type_id', 7])->select('number as value')->asArray()->all(),
                                'minLength' => '2',
                                'autoFill' => true,
                            ],
                            'clientEvents' => [
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
                        ]) ?>
                        <?= AutoComplete::widget([
                            'model' => $model,
                            'attribute' => 'extra_car_number',
                            'options' => ['class' => 'form-control input-group-addon extra-number', 'autocomplete' => 'on', 'style' => 'display:none; width: 50%'],
                            'clientOptions' => [
                                'source' => Car::find()->where(['type_id' => 7])->select('number as value')->asArray()->all(),
                                'minLength' => '2',
                                'autoFill' => true,
                            ]
                        ]) ?>
                    </div>
                </td>
                <td>
                    <?= $form->field($model, 'mark_id')->dropdownList(Mark::getMarkList(), ['style'=>'display:none'])->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'type_id')->dropdownList(Type::getTypeList(), ['max-width','style'=>'display:none'])->error(false) ?>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="form-group row" style="height: 5px;">
                        <div class="col-xs-12">
                            <label class="control-label">Услуга</label>
                        </div>
                    </div>

                    <div class="form-group" style="height: 25px;">
                        <?php if (!empty($serviceList)) {

                            $serviceList = ArrayHelper::perMutate($serviceList);
                            $newServiceList = [];
                            $checkNewArr = false;
                            $numNeedVal = 0;

                            while ($checkNewArr == false) {
                                foreach ($serviceList as $key => $value) {

                                    if (($value == 'снаружи') && ($numNeedVal == 0)) {
                                        $newServiceList[$key] = $value;
                                        $numNeedVal = 1;
                                    }

                                    if (($value == 'внутри') && ($numNeedVal == 1)) {
                                        $newServiceList[$key] = $value;
                                        $numNeedVal = 2;
                                    }

                                    if (($value == 'внутри+снаружи') && ($numNeedVal == 2)) {
                                        $newServiceList[$key] = $value;
                                        $numNeedVal = 3;
                                    }

                                    if (($value == 'отогрев') && ($numNeedVal == 3)) {
                                        $newServiceList[$key] = $value;
                                        $numNeedVal = 4;
                                    }

                                    if (($value == 'двигатель') && ($numNeedVal == 4)) {
                                        $newServiceList[$key] = $value;
                                        $numNeedVal = 5;
                                    }

                                    if (($value == 'химчистка') && ($numNeedVal == 5)) {
                                        $newServiceList[$key] = $value;
                                        $numNeedVal = 6;
                                        $checkNewArr = true;
                                    }

                                }
                            }

                            foreach ($serviceList as $key => $value) {

                                if (($value == 'снаружи') || ($value == 'внутри') || ($value == 'внутри+снаружи') || ($value == 'отогрев') || ($value == 'двигатель') || ($value == 'химчистка')) {
                                } else {
                                    $newServiceList[$key] = $value;
                                }

                            }

                            ?>
                            <?= Html::dropDownList("Act[serviceList][0][service_id]", '', $newServiceList, ['class' => 'form-control']) ?>
                        <?php } else { ?>
                            <?= Html::textInput("Act[serviceList][0][description]", '', ['class' => 'form-control', 'placeholder' => 'Услуга']) ?>
                        <?php } ?>
                        <?= Html::hiddenInput("Act[serviceList][0][amount]", 1, ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                        <?= Html::hiddenInput("Act[serviceList][0][price]", 0, ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                    </div>
                </td>
                <td>
                    <?= $form->field($model, 'check')->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'image')->fileInput(['class' => 'form-control'])->error(false) ?>
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
                    $dateYesterday = $dateNow - 1555200;


                    $lockedList = \common\models\Lock::checkLocked(date('n-Y', $dateYesterday), $model->service_type);
                    $is_locked = false;

                    if (count($lockedList) > 0) {

                        $closeAll = false;
                        $closeCompany = false;

                        for ($c = 0; $c < count($lockedList); $c++) {
                            if ($lockedList[$c]["company_id"] == 0) {
                                $closeAll = true;
                            }
                            if ($lockedList[$c]["company_id"] == Yii::$app->user->identity->company_id) {
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