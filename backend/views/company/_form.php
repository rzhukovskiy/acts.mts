<?php

/**
 * @var $model \common\models\Company
 * @var $type integer
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Company;
use common\models\Service;
use common\models\Requisites;
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?=$model->isNewRecord ? 'Добавление мойки' : 'Редактирование мойки ' . $model->name?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['company/create', 'type' => $type] : ['company/update', 'id' => $model->id],
            'id' => 'company-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>
        <?= $form->field($model, 'name') ?>
        <?= $form->field($model, 'parent_id')->dropdownList(
            Company::find()->active()->byType(Company::TYPE_OWNER)->active()->select(['name', 'id'])->indexBy('id')->column(),
            ['prompt'=>'выберите компанию']
        ) ?>
        <?= $form->field($model, 'address') ?>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>