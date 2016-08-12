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
    $form = ActiveForm::begin();
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
        <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>