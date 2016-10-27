<?php

use kartik\datetime\DateTimePicker;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\CompanyOffer */
/* @var $form yii\widgets\ActiveForm */
?>
    <strong>Город: </strong> <?=$model->company->address?>
<?php $form = ActiveForm::begin([
    'options' => [
        'id' => 'offer-form',
    ],
    'action' => ['/company-offer/update', 'id' => $model->id],
    'fieldConfig' => [
        'template' => '{label}{input}</div>',
        'labelOptions' => ['class' => 'control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]); ?>

<?= $form->field($model, 'communication_str')->widget(DateTimePicker::classname(), [
    'removeButton' => false,
    'options' => [
        'class' => 'form-control',
    ],
    'pluginOptions' => [
        'autoclose' => true,
        'format' => 'dd-mm-yyyy hh:ii'
    ]
])->error(false) ?>

<?= $form->field($model, 'process')->textarea(['rows' => 5]) ?>

<?php ActiveForm::end(); ?>

<?php
echo "<strong>Сотрудники: </strong><br/>";
foreach ($model->company->members as $companyMember) {
    echo "<strong>Должность: </strong> $companyMember[position]<br/>";
    echo "<strong>ФИО: </strong> $companyMember[position]<br/>";
    echo "<strong>Телефон: </strong> $companyMember->phone<br/>";
    echo "<strong>Email: </strong> $companyMember->email<br/>";
}
