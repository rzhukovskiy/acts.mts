<?php

use common\models\Car;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$script = <<< JS

// Поиск по номеру ТС
var arrOptions = [];

$("#companydriver-car_id option").each(function()
{
    if($(this).val() > 0) {
    arrOptions[$(this).val()] = $(this).text();
    }
});

$('.selectNumber').on('input',function(e){
if($(this).val().length > 0) {
    
    var searchNumber = $(this).val().toUpperCase();
    
    var checkHaveNumber = false;
    
    arrOptions.forEach(function(item, i, arrOptions) {
        
        if(item.indexOf(searchNumber) + 1) {
            $("#companydriver-car_id").val(i);
            checkHaveNumber = true;
            return false;
        }
        
    });
    
    if(checkHaveNumber == false) {
        $("#companydriver-car_id").val('');
    }
    
} else {
    $("#companydriver-car_id").val('');
}

});
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

/* @var $this yii\web\View
 * @var $model common\models\CompanyDriver
 * @var $form yii\widgets\ActiveForm
 */
$form = ActiveForm::begin([
    'action' => $model->isNewRecord ? ['/company-driver/create'] : ['/company-driver/update', 'id' => $model->id],
    'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
        'labelOptions' => ['class' => 'col-sm-3 control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]); ?>

<?= $form->field($model, 'company_id')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <label class="col-sm-3 control-label" style="margin-top: 30px;">Поиск по номеру ТС</label>
        <div class="col-sm-6">
            <?= Html::textInput("act_number", '',['id' => 'searchActNum', 'class' => 'form-control selectNumber', 'style' => 'height:auto; padding:3px 0px; margin-top:30px;']) ?>
        </div>
    </div>

<?= $form->field($model, 'car_id')->dropDownList(
    Car::find()->select(['number', 'id'])->where(['company_id' => $model->company_id])->orderBy('id ASC')->indexBy('id')->column(),
    ['prompt' => 'выберите номер ТС']
) ?>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>