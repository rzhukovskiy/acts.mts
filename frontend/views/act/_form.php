<?php

/**
 * @var $model \common\models\Act
 * @var $serviceList array
 * @var $clientScopes \common\models\ActScope[]
 * @var $partnerScopes \common\models\ActScope[]
 */

use common\models\Car;
use common\models\Mark;
use common\models\Service;
use common\models\Type;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
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
                    <label class="control-label" for="act-card_id">Номер и номер прицепа</label>
                    <div class="input-group">
                        <?= AutoComplete::widget([
                            'model' => $model,
                            'attribute' => 'number',
                            'options' => ['class' => 'form-control', 'autocomplete' => 'on', 'style' => 'width: 50%'],
                            'clientOptions' => [
                                'source' => Car::find()->where(['!=', 'type_id', 7])->select('number as value')->asArray()->all(),
                                'minLength' => '2',
                                'autoFill' => true,
                            ]
                        ]) ?>
                        <?= AutoComplete::widget([
                            'model' => $model,
                            'attribute' => 'extra_number',
                            'options' => ['class' => 'form-control input-group-addon', 'autocomplete' => 'on', 'style' => 'width: 50%'],
                            'clientOptions' => [
                                'source' => Car::find()->where(['type_id' => 7])->select('number as value')->asArray()->all(),
                                'minLength' => '2',
                                'autoFill' => true,
                            ]
                        ]) ?>
                    </div>
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
                        <label class="control-label">Услуги партнера (<?= $model->partner->name ?>)</label>
                        <?php
                        $partnerSum=0;
                        foreach ($partnerScopes as $scope) {
                            $partnerSum+=$scope->amount*$scope->price
                            ?>
                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-8">
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
                                <div class="col-xs-1">
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
                            <div class="col-xs-2">
                                <?= Html::input('number', "Act[partnerServiceList][0][amount]", '1', ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                            </div>
                            <div class="col-xs-1">
                                <?= Html::textInput("Act[partnerServiceList][0][price]", '', ['class' => 'form-control input-sm', 'placeholder' => 'цена']) ?>
                            </div>
                            <div class="col-xs-1">
                                <button type="button" class="btn btn-primary input-sm addButton">
                                    <i class="glyphicon glyphicon-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                             <div class="col-xs-2 col-xs-offset-8" style="font-weight: bold;font-size: 1.2em">
                                Сумма
                            </div>
                            <div class="col-xs-1"  style="font-size: 1.2em">
                                <strong style="padding-left: 5px"><?= $partnerSum ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12" style="margin-top: 30px;">
                        <label class="control-label">Услуги клиента (<?= $model->client->name ?>)</label>
                        <?php
                        $clientSum=0;
                        foreach ($clientScopes as $scope) {
                            $clientSum+=$scope->amount*$scope->price
                            ?>
                            <div class="form-group" style="height: 25px;">
                                <div class="col-xs-8">
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
                                <div class="col-xs-1">
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
                            <div class="col-xs-2">
                                <?= Html::input('number', "Act[clientServiceList][0][amount]", '1', ['class' => 'not-null form-control input-sm', 'placeholder' => 'Количество']) ?>
                            </div>
                            <div class="col-xs-1">
                                <?= Html::textInput("Act[clientServiceList][0][price]", '', ['class' => 'form-control input-sm', 'placeholder' => 'цена']) ?>
                            </div>
                            <div class="col-xs-1">
                                <button type="button" class="btn btn-primary input-sm addButton">
                                    <i class="glyphicon glyphicon-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-2 col-xs-offset-8" style="font-weight: bold;font-size: 1.2em">
                                Сумма
                            </div>
                            <div class="col-xs-1"  style="font-size: 1.2em">
                                <strong style="padding-left: 5px"><?= $clientSum ?></strong>
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