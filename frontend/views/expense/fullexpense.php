<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use yii\bootstrap\Tabs;

$this->title = 'Редактировать затраты';

echo Tabs::widget([
    'items' => [
        ['label' => 'Полный список', 'url' => ['addexpensecomp', 'type' => $model->type], 'active' => Yii::$app->controller->action->id == 'addexpensecomp'],
        ['label' => 'Редактирование', 'url' => ['expensecomp', 'id' => $model->id], 'active' => Yii::$app->controller->action->id == 'expensecomp'],
        ['label' => 'Редактирование затрат', 'url' => ['fullexpense', 'id' => $model->id], 'active' => Yii::$app->controller->action->id == 'fullexpense'],
    ],
]);

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Редактировать затраты' ?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['expense/addexpense', 'id' => $model->id] : ['expense/updateexp', 'id' => $model->id],
            'id' => 'company-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>

        <?php if ($model->type !== 1) { echo $form->field($model, 'description')->input('text', ['class' => 'form-control', 'placeholder' => 'Наименование']); } ?>
        <?= $form->field($model, 'sum')->input('text', ['class' => 'form-control', 'placeholder' => 'Сумма']) ?>


        <?= $form->field($model, 'date')->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'options' => ['placeholder' => 'Выберите дату', 'value' => date('d.m.Y', $model->date)],
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'autoclose'=>true,
                'weekStart'=>1,
            ]
        ]) ?>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>