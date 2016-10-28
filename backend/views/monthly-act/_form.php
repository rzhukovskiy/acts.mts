<?php

/**
 * @var $model \common\models\MonthlyAct
 * @var $type integer
 */

use common\models\MonthlyAct;
use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Редактирование акта ' . $model->id ?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action'      => ['monthly-act/update', 'id' => $model->id],
            'id'          => 'monthly-act-form',
            'options'     => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template'     => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-3 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>
        <div class="form-group field-company-name required field-disabled">
            <?= Html::activeLabel($model->client, 'name', ['class' => 'col-sm-3 control-label']) ?>
            <div class="col-sm-6 plain-field-value">
                <?= Html::activeHiddenInput($model->client, 'name') ?>
                <?= $model->client->name ?>
            </div>
        </div>

        <div class="form-group field-company-address required field-disabled">
            <?= Html::activeLabel($model->client, 'address', ['class' => 'col-sm-3 control-label']) ?>
            <div class="col-sm-6 plain-field-value">
                <?= Html::activeHiddenInput($model->client, 'address') ?>
                <?= $model->client->address ?>
            </div>
        </div>
        <!--
        <?= $form->field($model, 'image[]')->fileInput([
            'multiple' => true,
            'accept'   => 'image/*',
            'class'    => 'form-control'
        ])->error(false) ?>
        -->
        <div class="form-group field-monthlyact-profit required field-disabled">
            <?= Html::activeLabel($model, 'profit', ['class' => 'col-sm-3 control-label']) ?>
            <div class="col-sm-6 plain-field-value">
                <?= Html::activeHiddenInput($model, 'profit') ?>
                <?= $model->profit ?>
            </div>
        </div>

        <?= $form->field($model, 'act_status')->dropDownList(MonthlyAct::$actStatus, ['class' => 'form-control']) ?>
        <?= $form->field($model, 'payment_status')
            ->dropDownList(MonthlyAct::$paymentStatus, ['class' => 'form-control']) ?>
        <?= $form->field($model, 'payment_date')->widget(DatePicker::classname(),
            [
                'type'          => DatePicker::TYPE_INPUT,
                'language'      => 'ru',
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'dd-mm-yyyy'
                ],
                'options'       => [
                    'class' => 'form-control',
                    'value' => date('d-m-Y'),
                ]
            ])->error(false) ?>


        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6">
                <?= Html::hiddenInput('__returnUrl', Yii::$app->request->referrer) ?>
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>