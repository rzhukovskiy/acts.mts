<?php

/**
 * @var $model \common\models\Entry
 * @var $serviceList array
 */

use common\models\Car;
use common\models\Card;
use common\models\Mark;
use common\models\Type;
use kartik\time\TimePicker;
use kartik\select2\Select2;
use yii\bootstrap\ActiveForm;
use common\components\ArrayHelper;
use yii\helpers\Html;
use yii\jui\AutoComplete;

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавить запись для Международного Транспортного Сервиса
    </div>
    <div class="panel-body">

        <?php
        $form = ActiveForm::begin([
            'action' => ['act/create-entry', 'type' => $model->service_type],
            'id' => 'act-form',
        ]) ?>
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td style="width: 150px">
                    <?= $form->field($model, 'start_str')->widget(TimePicker::classname(), [
                        'pluginOptions' => [
                            'defaultTime' => gmdate('H:i', $model->company->info->start_at),
                            'showMeridian' => false,
                        ],
                        'options' => [
                            'class' => 'form-control',
                            'id' => 'start'
                        ]
                    ])->error(false) ?>
                </td>
                <td style="width: 100px">
                    <?= $form->field($model, 'card_id')->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'mark_id')->dropdownList(Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column())->error(false) ?>
                </td>
                <td class="complex-number">
                    <label class="control-label" for="act-card_id">Госномер <span class="extra-number"
                                                                                  style="display:none">и номер прицепа</span></label>

                    <div class="input-group" style="width: 100%;">
                        <?= AutoComplete::widget([
                            'model' => $model,
                            'attribute' => 'number',
                            'options' => ['class' => 'form-control main-number', 'autocomplete' => 'on', 'style' => 'width: 50%'],
                            'clientOptions' => [
                                'source' => Car::find()->where(['!=', 'type_id', 7])->select('number as value')->asArray()->all(),
                                'minLength' => '2',
                                'autoFill' => true,
                            ]
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
                <td style="width: 150px">
                    <label class="control-label">Действие</label>
                    <?= Html::submitButton('Записать', ['class' => 'btn btn-primary']) ?>
                    <?= Html::activeHiddenInput($model, 'day') ?>
                    <?= Html::activeHiddenInput($model, 'company_id') ?>
                    <?= Html::activeHiddenInput($model, 'service_type') ?>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <?= $form->field($model, 'type_id')->dropdownList(Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(), ['max-width'])->error(false) ?>
                </td>
                <td colspan="2">
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
                        <?= Html::hiddenInput("Act[serviceList][0][amount]", 1) ?>
                        <?= Html::hiddenInput("Act[serviceList][0][price]", 0) ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <?php ActiveForm::end() ?>
    </div>
</div>