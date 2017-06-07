<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;

/* @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $form yii\widgets\ActiveForm
 */
$form = ActiveForm::begin([
    'action' => $model->isNewRecord ? ['/company/newstate', 'id' => $id] : ['/company/updatestate', 'id' => $model->id],
    'options' => ['enctype' => 'multipart/form-data', 'accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
        'labelOptions' => ['class' => 'col-sm-3 control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]); ?>

<?= $form->field($model, 'company_id')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'date')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату общения'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:i',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>

<?= $form->field($model, 'member_id')->dropDownList($companyMembers, ['class' => 'form-control', 'multiple' => 'true', 'size' => '4'/*, 'prompt' => 'Выберите сотрудника'*/]) ?>

<?= $form->field($model, 'author_id')->dropDownList($authorMembers, ['class' => 'form-control', 'prompt' => 'Выберите сотрудника']) ?>

<?= $form->field($model, 'type')->dropDownList(['0' => 'Исходящий звонок' , '1' => 'Входящий звонок', '2' => 'Исходящее письмо', '3' => 'Входящее письмо'], ['class' => 'form-control', 'prompt' => 'Выберите формат']) ?>

<?= $form->field($model, 'comment')->textarea(['maxlength' => true, 'placeholder' => 'Введите комментарий']) ?>

<?= $form->field($model, 'files[]')->fileInput(['multiple' => true]) ?>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>