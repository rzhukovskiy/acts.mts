<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Добавление';


    $tabs = [
        ['label' => 'Активные', 'url' => ['plan/tasklist?type=0']],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Добавление', 'url' => ['informing/create'], 'active' => Yii::$app->controller->action->id == 'create'],
    ];

echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $tabs,
]);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавление
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => ['/informing/create'],
            'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-3 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>
        <?= $form->field($model, 'text')->textarea(['maxlength' => true, 'rows' => '4', 'placeholder' => 'Введите информацию']) ?>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']); ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>