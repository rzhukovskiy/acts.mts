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
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
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
            <div class="col-sm-offset-2 col-sm-6">
                <span data-toggle="collapse" data-target="#details">Подробнее</span>
            </div>
        </div>

        <div id="details" class="collapse">
            <?= $form->field($model, 'is_split')->checkbox([], false) ?>
            <?= $form->field($model, 'cardList') ?>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-6">
                    <strong>Для актов</strong>
                </div>
            </div>
            <?= $form->field($model, 'director') ?>
            <?php foreach(\common\models\Service::$listType as $id => $type) { ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?=$type['ru']?></label>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Договор</label>
                    <div class="col-sm-6">
                        <?= Html::textInput("Company[requisitesList][$id][Requisites][contract]", '', ['class' => 'form-control'])?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Текст заголовка</label>
                    <div class="col-sm-6">
                        <?= Html::textarea("Company[requisitesList][$id][Requisites][header]", '', ['class' => 'form-control'])?>
                    </div>
                </div>
                <?= Html::hiddenInput("Company[requisitesList][$id][Requisites][type]", $id)?>
            <?php } ?>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <?php ActiveForm::end() ?>
    </div>
</div>