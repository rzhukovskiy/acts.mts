<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Mark */
/* @var $form yii\widgets\ActiveForm */
?>
<div class="mark-form">
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
    echo $form->field($model, 'name')->textInput(['maxlength' => true]);
    ?>
    <div class="form-group">
        <div class="col-sm-6 col-sm-offset-2">
            <?php
            if (!empty($model->image))
                echo Html::img('/images/cars/' . $model->image, ['style' => 'height: 100px']);
            ?>
        </div>
    </div>
    <?php
    echo $form->field($model, 'imageFile')->fileInput(['class' => false]);
    ?>
    <div class="form-group">
        <div class="col-sm-6 col-sm-offset-2">
            <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
