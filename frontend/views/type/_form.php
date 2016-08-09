<?php

    use yii\helpers\Html;
    use yii\widgets\ActiveForm;

    /* @var $this yii\web\View */
    /* @var $model common\models\Type */
    /* @var $form yii\widgets\ActiveForm */
?>
<div class="type-form">
    <?php
        $form = ActiveForm::begin();

        echo $form->field( $model, 'name' )->textInput( [ 'maxlength' => true ] );
        //echo $form->field( $model, 'image' )->textInput( [ 'maxlength' => true ] );
    ?>
    <div class="form-group">
        <?= Html::submitButton( $model->isNewRecord ? 'Создать' : 'Сохранить', [ 'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary' ] ) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
