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
                            'attribute' => 'number',
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
                            'attribute' => 'extra_number',
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
                        <?php if (!empty($serviceList)) { ?>
                            <?= Html::dropDownList("Act[serviceList][0][service_id]", '', ArrayHelper::perMutate($serviceList), ['class' => 'form-control']) ?>
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
            </tbody>
        </table>
        <?php ActiveForm::end() ?>
    </div>
</div>