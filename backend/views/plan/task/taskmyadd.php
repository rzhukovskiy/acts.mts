<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;
use common\models\User;
use common\models\TaskUser;

$this->title = 'Добавление собственной задачи';

if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 176)) {
    $tabs = [
        ['label' => 'Все задачи', 'url' => ['plan/tasklist?type=0']],
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Мне поставили задачу', 'url' => ['plan/tasklist?type=2']],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=3']],
        ['label' => 'Собственные задачи', 'url' => ['plan/taskmylist']],
        ['label' => 'Добавление cобственной задачи', 'url' => ['plan/taskmyadd'], 'active' => Yii::$app->controller->action->id == 'taskmyadd'],
    ];
} else {
    $tabs = [
        ['label' => 'Я поставил задачу', 'url' => ['plan/tasklist?type=1']],
        ['label' => 'Мне поставили задачу', 'url' => ['plan/tasklist?type=2']],
        ['label' => 'Архив', 'url' => ['plan/tasklist?type=3']],
        ['label' => 'Собственные задачи', 'url' => ['plan/taskmylist']],
        ['label' => 'Добавление cобственной задачи', 'url' => ['plan/taskmyadd'], 'active' => Yii::$app->controller->action->id == 'taskmyadd'],
    ];
}
echo Tabs::widget([
    'items' => $tabs,
]);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавление собственной задачи
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => ['/plan/taskmyadd'],
            'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-3 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>
        <?= $form->field($model, 'priority')->dropDownList(TaskUser::$priorityStatus, ['class' => 'form-control']) ?>
        <?= $form->field($model, 'task')->textarea(['maxlength' => true, 'rows' => '4', 'placeholder' => 'Введите задачу']) ?>
        <?= $form->field($model, 'data')->widget(DateTimePicker::className(), [
            'type' => DateTimePicker::TYPE_INPUT,
            'options' => ['placeholder' => 'Выберите дату и время'],
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy hh:i',
                'autoclose'=>true,
                'weekStart'=>1,
                'todayBtn'=>true,
            ]
        ]) ?>
        <?= $form->field($model, 'files[]')->fileInput(['multiple' => true]) ?>


        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']); ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>