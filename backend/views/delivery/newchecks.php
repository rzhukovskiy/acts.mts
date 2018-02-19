<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use \kartik\date\DatePicker;
use common\models\Company;

$this->title = 'Добавление';

echo Tabs::widget([
    'items' => [
        ['label' => 'Список', 'url' => ['delivery/listchecks']],
        ['label' => 'Добавление', 'url' => ['delivery/newchecks'], 'active' => Yii::$app->controller->action->id == 'newchecks'],
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
            'action' => $model->isNewRecord ? ['/delivery/newchecks'] : ['/delivery/updatechecks'],
            'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-3 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>

        <?= $form->field($model, 'company_id')->dropDownList($companyWash, ['class' => 'form-control', 'prompt' => 'Выберите мойку']) ?>
        <?= $form->field($model, 'serial_number')->input('text', ['class' => 'form-control', 'placeholder' => 'Например: 900-999']) ?>
        <?= $form->field($model, 'date_send')->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'autoclose' => true,
                'weekStart' => 1,
            ]
        ]) ?>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']); ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>