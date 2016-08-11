<?php

    use yii\bootstrap\ActiveForm;
    use yii\helpers\Html;

    /* @var $this yii\web\View */
    /* @var $model common\models\Mark */
    /* @var $form yii\widgets\ActiveForm */
?>
<div class="mark-form">
    <?php
        $form = ActiveForm::begin();
        echo $form->field( $model, 'name' )->textInput( [ 'maxlength' => true ] );

        if (!empty($model->image))
            echo Html::img('/images/cars/'.$model->image, ['style' => 'height: 100px']);

        echo $form->field( $model, 'imageFile' )->fileInput();
    ?>
    <div class="form-group">
        <?= Html::submitButton( $model->isNewRecord ? 'Добавить' : 'Сохранить', [ 'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary' ] ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
