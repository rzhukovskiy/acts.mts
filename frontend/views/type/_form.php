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
        'options' => ['class' => 'form-horizontal col-sm-12'],
        'fieldConfig' => [
            'template' => '{label}<div class="col-sm-6 input-sm">{input}{error}</div>',
            'labelOptions' => ['class' => 'col-sm-2 control-label'],
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
    echo $form->field($model, 'imageFile')->fileInput();
    ?>
    <div class="form-group">
        <div class="col-sm-6 col-sm-offset-2">
            <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
