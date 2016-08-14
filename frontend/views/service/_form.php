<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Service;

/* @var $this yii\web\View */
/* @var $model common\models\Service */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $model->isNewRecord ? 'Добавление услуги' : 'Редактирование услуги ' . $model->description ?>
    </div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['service/create'] : ['service/update', 'id' => $model->id],
            'id' => 'service-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>

        <?= $form->field($model, 'type')->dropDownList(ArrayHelper::map(Service::$listType, 'id', 'ru')) ?>

        <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'is_fixed')->checkbox([], false) ?>


        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Обновить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>

    </div>
</div>
