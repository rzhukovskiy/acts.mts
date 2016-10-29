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
        <?= \common\widgets\Alert::widget() ?>
        <?php
        $form = ActiveForm::begin([
            'action'      => ['monthly-act/detail', 'id' => $model->id],
            'id'          => 'monthly-act-detail-form',
            'options'     => ['class' => 'form-horizontal col-sm-12', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template'     => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-6 control-label'],
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
                    'value' => date('d-m-Y'),
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
                    'value' => date('d-m-Y'),
                ]
            ])->error(false) ?>
        <?= $form->field($model, 'post_number')->input('text', ['class' => 'form-control'])->label() ?>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6">
                <?= Html::a('Проверить почтовое отправление',
                    'https://www.pochta.ru/tracking#' . $model->post_number,
                    ['target' => 'blank', 'class' => 'btn btn-primary']) ?>
            </div>
        </div>


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
                    'value' => date('d-m-Y'),
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