<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model common\models\User
 * @var $form yii\widgets\ActiveForm
 */
?>
<div class="user-form">
    <?php
    $form = ActiveForm::begin([
        'options' => ['class' => 'form-horizontal col-sm-12'],
        'fieldConfig' => [
            'template' => '{label}<div class="col-sm-6 input-sm">{input}{error}</div>',
            'labelOptions' => ['class' => 'col-sm-2 control-label'],
        ],
    ]);
    echo $form->field($model, 'username')->textInput();
    echo $form->field($model, 'password')->passwordInput();
    echo $form->field($model, 'email')->textInput();
    echo $form->field($model, 'company_id')
        ->dropDownList(
            $companyDropDownData,
            [
                'class' => 'form-control',
                'prompt' => 'Все компании'
            ]
        );
    ?>
    <div class="form-group">
        <div class="col-sm-6 col-sm-offset-2">
            <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>