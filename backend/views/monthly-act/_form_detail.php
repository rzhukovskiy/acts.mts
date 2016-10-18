<?php

/**
 * @var $model \common\models\MonthlyAct
 */

use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Детализация акта ' . $model->id ?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action'      => ['monthly-act/detail', 'id' => $model->id],
            'id'          => 'monthly-act-form',
            'options'     => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template'     => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>
        <?= $form->field($model, 'act_comment')->textarea(['class' => 'form-control']) ?>
        <?= $form->field($model, 'act_send_date')->widget(DatePicker::classname(),
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
        <?= $form->field($model, 'act_client_get_date')->widget(DatePicker::classname(),
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
        <?= $form->field($model, 'act_we_get_date')->widget(DatePicker::classname(),
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
        <?= $form->field($model, 'payment_comment')->textarea(['class' => 'form-control']) ?>
        <?= $form->field($model, 'payment_estimate_date')->widget(DatePicker::classname(),
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

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::hiddenInput('__returnUrl', Yii::$app->request->referrer) ?>
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>