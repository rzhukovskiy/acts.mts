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
use common\models\Type;
use common\models\Car;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\jui\AutoComplete;

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Редактировать акт
    </div>
    <div class="panel-body">
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
                        ]
                    ])->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'partner_id')->dropDownList(Company::find()->where(['type' => $model->service_type])->select(['name', 'id'])->indexBy('id')->column())->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'check')->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'image')->fileInput(['class' => 'form-control'])->error(false) ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?= $form->field($model, 'card_id')->widget(Select2::classname(), [
                        'data' => Card::find()->select(['number', 'id'])->indexBy('id')->column(),
                        'options' => ['class' => 'form-control', 'style' => 'min-width: 60px'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'number')->widget(AutoComplete::classname(), [
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
                    <div class="col-sm-6">
                        Услуги партнера
                        <?php foreach ($partnerScopes as $scope) { ?>
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <?php if (!empty($serviceList)) { ?>
                                        <?= Html::dropDownList("Act[partnerServiceList][$scope->id][service_id]", $scope->service_id,
                                            $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                                    <?php } else { ?>
                                        <?= Html::textInput("Act[partnerServiceList][$scope->id][description]", $scope->description, ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-2">
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

                        <div class="form-group">
                            <div class="col-xs-6">
                                <?php if (!empty($serviceList)) { ?>
                                    <?= Html::dropDownList("Act[partnerServiceList][0][service_id]", '',
                                        $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                                <?php } else { ?>
                                    <?= Html::textInput("Act[partnerServiceList][0][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                <?php } ?>
                            </div>
                            <div class="col-xs-2">
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

                    <div class="col-sm-6">
                        Услуги клиента
                        <?php foreach ($clientScopes as $scope) { ?>
                            <div class="form-group">
                                <div class="col-xs-6">
                                    <?php if (!empty($serviceList)) { ?>
                                        <?= Html::dropDownList("Act[clientServiceList][$scope->id][service_id]", $scope->service_id,
                                            $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                                    <?php } else { ?>
                                        <?= Html::textInput("Act[clientServiceList][$scope->id][description]", $scope->description, ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                    <?php } ?>
                                </div>
                                <div class="col-xs-2">
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

                        <div class="form-group">
                            <div class="col-xs-6">
                                <?php if (!empty($serviceList)) { ?>
                                    <?= Html::dropDownList("Act[clientServiceList][0][service_id]", '', $serviceList, ['class' => 'form-control input-sm', 'prompt' => 'выберите услугу']) ?>
                                <?php } else { ?>
                                    <?= Html::textInput("Act[clientServiceList][0][description]", '', ['class' => 'form-control input-sm', 'placeholder' => 'Услуга']) ?>
                                <?php } ?>
                            </div>
                            <div class="col-xs-2">
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