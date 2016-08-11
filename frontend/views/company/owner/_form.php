<?php

/**
 * @var $model \common\models\Company
 * @var $type integer
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Company;
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Добавление компании
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => ['company/create', 'type' => $type],
            'id' => 'company-form',
            'options' => ['class' => 'form-horizontal col-sm-10'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6 input-sm">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
            ],
        ]) ?>
        <?= $form->field($model, 'name') ?>
        <?= $form->field($model, 'parent_id')->dropdownList(
            Company::find()->byType(Company::TYPE_OWNER)->active()->select(['name', 'id'])->indexBy('id')->column(),
            ['prompt'=>'выберите компанию']
        ) ?>
        <?= $form->field($model, 'address') ?>

        <div class="form-group">
            <div class="col-sm-6 col-sm-offset-2">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>