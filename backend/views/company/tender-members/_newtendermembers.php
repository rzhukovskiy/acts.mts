<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'action' => $model->isNewRecord ? ['/company/newtendermembers'] : ['/company/updatetendermembers'],
    'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
        'labelOptions' => ['class' => 'col-sm-3 control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]); ?>

<?= $form->field($model, 'company_name')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите компанию']) ?>
<?= $form->field($model, 'inn')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите ИНН']) ?>
<?= $form->field($model, 'city')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите город']) ?>
<?= $form->field($model, 'comment')->textarea(['maxlength' => true, 'rows' => '4', 'placeholder' => 'Введите комментарий']) ?>

<div class="form-group">
    <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
    </div>
</div>

<?php ActiveForm::end();


?>
