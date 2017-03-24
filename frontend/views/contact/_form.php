<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Contact */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $model->isNewRecord ? 'Добавление контакта' : 'Редактирование контакта ' . $model->name ?>
    </div>
    <div class="panel-body">

        <?php $form = ActiveForm::begin([
            'action'      => $model->isNewRecord ? ['contact/create', 'type' => $type] :
                ['contact/update', 'id' => $model->id, 'type' => $model->type],
            'id'          => 'contact-form',
            'options'     => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template'     => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>

        <? //$form->field($model, 'company_id')->dropdownList(\common\models\Company::dataDropDownList($type, true),
        // ['prompt' => 'выберите компанию'])
        ?>
        <?= Html::activeHiddenInput($model, 'type', ['value' => $model->type]) ?>
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'position')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>



        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Обновить',
                    ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>