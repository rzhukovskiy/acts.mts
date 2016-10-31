<?php

use common\models\Mark;
use common\models\Type;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

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

<?= $form->field($model, 'mark_id')->dropDownList(
    Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
    ['prompt' => 'выберите марку ТС']
) ?>
<?= $form->field($model, 'type_id')->dropDownList(
    Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
    ['prompt' => 'выберите тип ТС']
) ?>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>