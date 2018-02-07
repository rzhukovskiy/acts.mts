<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use \kartik\date\DatePicker;

$this->title = 'Добавление';

echo Tabs::widget([
    'items' => [
        ['label' => 'Новые', 'url' => ['company/tenderownerlist?win=1']],
        ['label' => 'В работе', 'url' => ['company/tenderownerlist?win=0']],
        ['label' => 'Архив', 'url' => ['company/tenderownerlist?win=2']],
        ['label' => 'Не взяли', 'url' => ['company/tenderownerlist?win=3']],
        ['label' => 'Добавление', 'url' => ['company/tenderowneradd'], 'active' => Yii::$app->controller->action->id == 'tenderowneradd'],
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
    'action' => $model->isNewRecord ? ['/company/tenderowneradd'] : ['/company/tenderownerupdate'],
    'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
    'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
    'labelOptions' => ['class' => 'col-sm-3 control-label'],
    'inputOptions' => ['class' => 'form-control input-sm'],
    ],
    ]); ?>

    <?= $form->field($model, 'customer')->input('text', ['class' => 'form-control']) ?>
    <?= $form->field($model, 'customer_full')->textarea(['maxlength' => true, 'rows' => '4', 'placeholder' => 'Введите полное наименование заказчика']) ?>
    <?= $form->field($model, 'purchase_name')->textarea(['maxlength' => true, 'rows' => '4', 'placeholder' => 'Введите что закупают']) ?>
    <?= $form->field($model, 'purchase')->input('text', ['class' => 'form-control']) ?>
    <?= $form->field($model, 'request_security')->input('text', ['class' => 'form-control']) ?>
    <?= $form->field($model, 'city')->input('text', ['class' => 'form-control']) ?>
    <?= $form->field($model, 'inn_customer')->input('text', ['class' => 'form-control']) ?>
    <?= $form->field($model, 'fz')->input('text', ['class' => 'form-control']) ?>
    <?= $form->field($model, 'date_from')->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'options' => ['placeholder' => 'Дата начала'],
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'autoclose'=>true,
                'weekStart'=>1,
            ]
        ]) ?>
    <?= $form->field($model, 'date_to')->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'options' => ['placeholder' => 'Дата окончания'],
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'autoclose'=>true,
                'weekStart'=>1,
            ]
        ]) ?>

    <?= $form->field($model, 'date_bidding')->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'options' => ['placeholder' => 'Дата и время начала торгов'],
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'autoclose'=>true,
                'weekStart'=>1,
            ]
        ]) ?>

    <?= $form->field($model, 'date_consideration')->widget(DatePicker::className(), [
            'type' => DatePicker::TYPE_INPUT,
            'options' => ['placeholder' => 'Дата и время рассмотрения заявок'],
            'pluginOptions' => [
                'format' => 'dd.mm.yyyy',
                'autoclose'=>true,
                'weekStart'=>1,
            ]
        ]) ?>

    <?= $form->field($model, 'link_official')->input('text', ['class' => 'form-control']) ?>
    <?= $form->field($model, 'electronic_platform')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите ссылку с http://']) ?>
    <?= $form->field($model, 'link')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите ссылку с http://']) ?>

    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']); ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>
    </div>
</div>