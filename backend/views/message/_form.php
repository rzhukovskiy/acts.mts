<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View
 * @var $model common\models\Message
 * @var $form yii\widgets\ActiveForm
 * @var $listUser array
 */
?>

<div class="message-form">
    <?php
    $form = ActiveForm::begin([
        'action' => $model->isNewRecord ? ['message/create'] : ['message/update', 'id' => $model->id],
        'id' => 'company-form',
        'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
        'fieldConfig' => [
            'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
            'labelOptions' => ['class' => 'col-sm-2 control-label'],
            'inputOptions' => ['class' => 'form-control input-sm'],
        ],
    ]) ?>

    <?= $form->field($model, 'recipient')->dropDownList($listUser) ?>
    <?= $form->field($model, 'topic_str')->textInput() ?>
    <?= $form->field($model, 'text')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-6">
            <?= Html::submitButton('Отправить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
