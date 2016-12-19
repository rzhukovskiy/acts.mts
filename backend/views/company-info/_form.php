<?php

use kartik\datetime\DateTimePicker;
use kartik\time\TimePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CompanyInfo */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?=$model->company->name?> :: Инфо
    </div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['/company-info/create'] : ['/company-info/update', 'id' => $model->id],
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-3 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>

        <?= $form->field($model, 'company_id')->hiddenInput()->label(false) ?>

        <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'index')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'city')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'street')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'house')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'address_mail')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'pay')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'contract')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'contract_date_str')->widget(DateTimePicker::classname(), [
            'removeButton' => false,
            'options' => [
                'class' => 'form-control',
            ],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'dd-mm-yyyy hh:ii'
            ]
        ])->error(false) ?>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>