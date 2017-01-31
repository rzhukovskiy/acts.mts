<?php

use common\models\Company;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $act_id integer */
?>
<div class="row" style="margin: 10px -10px">
    <?php $form = ActiveForm::begin([
        'action' => [
            '/car/create',
            'act_id' => $act_id
        ],
        'id'     => 'add-car-model-form',
    ]); ?>

    <div class="col-xs-6">
        <?= $form->field($model, 'company_id')
            ->dropdownList(Company::dataDropDownList(Company::TYPE_OWNER), ['class' => 'form-control'])
            ->label(false) ?>
        <?= Html::activeHiddenInput($model, 'number'); ?>
        <?= Html::activeHiddenInput($model, 'mark_id'); ?>
        <?= Html::activeHiddenInput($model, 'type_id'); ?>
        <?= Html::hiddenInput('_returnUrl', Url::current()); ?>
    </div>
    <div class="form-group">
        <?= Html::submitButton('Добавить ТС',
            ['class' => 'btn btn-success col-xs-2']) ?>
        <?= $form->field($model, 'is_infected')
            ->checkbox(['label' => '', 'style' => 'height: 32px; float: left; margin-left: 10px']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
