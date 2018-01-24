<?php

use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model \frontend\models\forms\CarUploadXlsForm
 * @var $typeDropDownItems array
 * @var $companyDropDownItems array
 */

$form = ActiveForm::begin([
    'id' => 'car_xls_insert_form',

    'options' => [
        'class' => 'form-horizontal col-sm-12',
        'style' => 'margin-top: 20px;',
        'enctype' => 'multipart/form-data',
    ],
    'fieldConfig' => [
        'template' => '{label}<div class="col-sm-10">{input}{error}</div>',
        'labelOptions' => ['class' => 'col-sm-2 control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ]
]);

echo $form->field($model, 'company_id')
    ->dropDownList($companyDropDownItems, ['prompt' => 'Выберете компанию']);
echo $form->field($model, 'type_id')
    ->dropDownList($typeDropDownItems, ['prompt' => 'Выберете тип ТС']);
echo $form->field($model, 'file')
    ->fileInput(['class' => '', 'accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel']);
?>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-6">
            <?= Html::submitButton('Загрузить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>
<?php ActiveForm::end();