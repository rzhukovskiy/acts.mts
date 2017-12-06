<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'action' => $model->isNewRecord ? ['/company/newtenderlinks'] : ['/company/updatetenderlinks'],
    'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
        'labelOptions' => ['class' => 'col-sm-3 control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]); ?>

<?= $form->field($model, 'member_id')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите ID конкурента']) ?>
<?= $form->field($model, 'tender_id')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите ID тендера']) ?>

<div class="form-group">
    <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
    </div>
</div>

<?php ActiveForm::end();


?>
