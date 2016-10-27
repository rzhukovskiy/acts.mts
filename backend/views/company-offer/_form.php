<?php

use kartik\datetime\DateTimePicker;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View
 * @var $model common\models\CompanyOffer
 * @var $form yii\widgets\ActiveForm
 */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?=$model->company->name?> :: Процесс
    </div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['/company-offer/create'] : ['/company-offer/update', 'id' => $model->id],
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-3 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>

        <?= $form->field($model, 'company_id')->hiddenInput()->label(false) ?>

        <?= $form->field($model, 'communication_str')->widget(DateTimePicker::classname(), [
            'removeButton' => false,
            'options' => [
                'class' => 'form-control',
            ],
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'dd-mm-yyyy hh:ii'
            ]
        ])->error(false) ?>

        <?= $form->field($model, 'mail_number')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'process')->textarea(['rows' => 20]) ?>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>