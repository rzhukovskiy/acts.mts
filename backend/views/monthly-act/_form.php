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
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>
        <?= $form->field($model->client, 'name')
            ->input('text', ['class' => 'form-control', 'disabled' => 'disabled']) ?>
        <?= $form->field($model->client, 'address')
            ->input('text', ['class' => 'form-control', 'disabled' => 'disabled']) ?>

        <?= $form->field($model, 'image')->fileInput(['class' => 'form-control'])->error(false) ?>

        <?= $form->field($model, 'profit')->input('text', ['class' => 'form-control', 'disabled' => 'disabled']) ?>
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
                ]
            ])->error(false) ?>
        <?= $form->field($model, 'act_status')->dropDownList(MonthlyAct::$actStatus, ['class' => 'form-control']) ?>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::hiddenInput('__returnUrl', Yii::$app->request->referrer) ?>
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>