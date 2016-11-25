<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View
 * @var $model common\models\Plan
 * @var $form yii\widgets\ActiveForm
 * @var $searchModel common\models\search\ServiceSearch
 */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавление задачи
    </div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'action'      => ['plan/create'],
            'id'          => 'service-form',
            'options'     => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template'     => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>
        <?= $form->field($model, 'user_id')->hiddenInput()->label(false) ?>

        <?= $form->field($model, 'task_name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'status')->dropDownList(\common\models\Plan::$listStatus) ?>

        <?= $form->field($model, 'comment')->textarea(['maxlength' => true]) ?>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>

    </div>
</div>
