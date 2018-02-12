<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use \kartik\date\DatePicker;

$this->title = 'Добавление';

echo Tabs::widget([
    'items' => [
        ['label' => 'Список', 'url' => ['delivery/listchemistry']],
        ['label' => 'Добавление', 'url' => ['delivery/newchemistry'], 'active' => Yii::$app->controller->action->id == 'newchemistry'],
    ],
]);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавление
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['/delivery/newchemistry'] : ['/delivery/updatechemistry'],
            'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-3 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>

        <?= $form->field($model, 'wash_name')->input('text', ['class' => 'form-control']) ?>
        <?= $form->field($model, 'date_send')->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'autoclose'=>true,
                'weekStart'=>1,
            ]
        ]) ?>

        <?= $form->field($model, 'size')->input('text', ['class' => 'form-control']) ?>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']); ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>