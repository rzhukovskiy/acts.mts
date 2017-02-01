<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View
 * @var $form yii\widgets\ActiveForm
 */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Добавление карты' ?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => ['card/lost'],
            'id' => 'card-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ])
        ?>

        <div class="form-group field-card-number required">
            <label class="col-sm-2 control-label" for="card-number">Номер карты</label>
            <div class="col-sm-6">
                <?= Html::textInput('number', '', ['class' => 'form-control input-sm']) ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>

        <?php ActiveForm::end() ?>
    </div>
</div>