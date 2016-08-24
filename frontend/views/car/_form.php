<?php

/**
 * @var $companyModel \common\models\Company
 * @var $model \common\models\Car
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Mark;
use common\models\Type;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $model->isNewRecord ? 'Добавление машины' : 'Редактирование машины' ?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['car/create'] : ['car/update', 'id' => $model->id],
            'id' => 'company-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]) ?>
        <?= $form->field($model, 'number') ?>
        <?= $form->field($model, 'mark_id')->dropdownList(
            Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
            ['prompt' => 'выберите марку ТС']
        ) ?>
        <?= $form->field($model, 'type_id')->dropdownList(
            Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
            ['prompt' => 'выберите тип ТС']
        ) ?>
        <?= $form->field($model, 'is_infected')->checkbox([], false) ?>
        <?= Html::hiddenInput('Car[company_id]' , $companyModel->id) ?>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>

        <?= $model->isNewRecord ? $this->render('/car/_list', [
            'dataProvider' => $companyModel->getCarDataProvider(),
        ]) : ''?>
    </div>
</div>