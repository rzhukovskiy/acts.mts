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
            'action' => ['act/create', 'type' => $model->service_type],
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
                        ]
                    ])->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'number')->widget(AutoComplete::classname(), [
                        'options' => ['class' => 'form-control', 'autocomplete' => 'on'],
                        'clientOptions' => [
                            'source' => Car::find()->select('number as value')->asArray()->all(),
                            'minLength'=>'2',
                            'autoFill'=>true,
                        ],
                    ])->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'mark_id')->dropdownList(Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column())->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'type_id')->dropdownList(Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(), ['max-width'])->error(false) ?>
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
                    <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-sm']) ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php ActiveForm::end() ?>
    </div>
</div>