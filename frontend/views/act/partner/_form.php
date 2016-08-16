<?php

/**
 * @var $model \common\models\Act
 * @var $serviceList array
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\jui\DatePicker;
use common\models\Mark;
use common\models\Type;
use common\models\Card;

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
        <table class="table table-striped table-bordered">
            <tbody>
            <tr>
                <td>
                    <?= $form->field($model, 'time_str')->widget(DatePicker::classname(), [
                        'language' => 'ru',
                        'dateFormat' => 'dd-MM-yyyy',
                        'options' => [
                            'class' => 'form-control',
                        ]
                    ])->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'card_id')->dropdownList(Card::find()->select(['number', 'id'])->indexBy('id')->column())->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'number')->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'mark_id')->dropdownList(Mark::find()->select(['name', 'id'])->indexBy('id')->column())->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'type_id')->dropdownList(Type::find()->select(['name', 'id'])->indexBy('id')->column(), ['max-width'])->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'check')->error(false) ?>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <div class="form-group">
                        <div class="col-xs-6">
                            <?php if (!empty($serviceList)) { ?>
                                <?= Html::dropDownList("Act[serviceList][0][service_id]", '', $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'Услуга']) ?>
                            <?php } else { ?>
                                <?= Html::textInput("Act[serviceList][0][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                            <?php } ?>
                        </div>
                        <div class="col-xs-1">
                            <?= Html::input('number', "Act[serviceList][0][amount]", 1, ['class' => 'form-control input-sm', 'placeholder' => 'Количество']) ?>
                        </div>
                        <div class="col-xs-1">
                            <?= Html::textInput("Act[serviceList][0][price]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Цена']) ?>
                        </div>
                        <div class="col-xs-1">
                            <button type="button" class="btn btn-primary input-sm addButton"><i class="glyphicon glyphicon-plus"></i></button>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-sm']) ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php ActiveForm::end() ?>
    </div>
</div>