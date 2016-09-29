<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $model common\models\User
 * @var $form yii\widgets\ActiveForm
 */

?>
<div class="user-update-form">
    <?php
    $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal col-sm-12',
            'style' => 'margin-top: 20px;',
        ],
        'fieldConfig' => [
            'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
            'labelOptions' => ['class' => 'col-sm-2 control-label'],
            'inputOptions' => ['class' => 'form-control input-sm'],
        ],
    ]);
    echo $form->field($model, 'username')->textInput();
    echo $form->field($model, 'newPassword')->passwordInput();
    ?>
    <div class="form-group">
        <div class="col-sm-6 col-sm-offset-2"><?= Html::submitButton('Изменить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>