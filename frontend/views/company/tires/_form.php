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
        <?=$model->isNewRecord ? 'Добавление шиномонтажа' : 'Редактирование шиномонтажа ' . $model->name?>
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
            Company::getSortedItemsForDropdown(),
            ['prompt'=>'выберите компанию']
        ) ?>
        <?= $form->field($model, 'address') ?>


        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <span data-toggle="collapse" data-target="#details" class="pseudo">Подробнее</span>
            </div>
        </div>

        <div id="details" class="collapse">
            <?= $form->field($model, 'is_sign')->checkbox([], false) ?>
            <?php

            if($model->isNewRecord) {
                $model->is_act_sign = 1;
            }

            echo $form->field($model, 'is_act_sign')->radioList([
                '0' => 'Нет',
                '1' => 'Подпись и печать',
                '2' => 'Только подпись',
            ]); ?>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-6">
                    <strong>Для актов</strong>
                </div>
            </div>
            <?= $form->field($model, 'director') ?>
            <?php
                $id = $model->type;
                $type = Service::$listType[$model->type];
                $existed = $model->isNewRecord ? null : Requisites::findOne(['company_id' => $model->id, 'type' => $id])
            ?>
            <div class="form-group">
                <label class="col-sm-2 control-label">Договор</label>
                <div class="col-sm-6">
                    <?= Html::textInput("Company[requisitesList][$id][Requisites][contract]", $existed ? $existed->contract : '', ['class' => 'form-control input-sm'])?>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Текст заголовка</label>
                <div class="col-sm-8">
                    <?= Html::textarea("Company[requisitesList][$id][Requisites][header]", $existed ? $existed->header : '', ['rows' => 10, 'class' => 'form-control input-sm'])?>
                </div>
            </div>
            <?= Html::hiddenInput("Company[requisitesList][$id][Requisites][type]", $id)?>
            <?= Html::hiddenInput("Company[requisitesList][$id][Requisites][id]", $existed ? $existed->id : '')?>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>