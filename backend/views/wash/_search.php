<?php

/**
 * @var $searchModel \common\models\search\CompanySearch
 * @var $entrySearchModel \common\models\search\EntrySearch
 * @var $listCity array
 * @var $type integer
 */

use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Выбор города
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'id' => 'wash-form',
            'method' => 'get',
            'options' => ['class' => 'form-horizontal col-sm-12', 'style' => 'margin: 20px 0;'],
            'fieldConfig' => [
                'template' => '{input}',
                'inputOptions' => ['class' => 'form-control input-sm'],
                'options' => ['class' => 'col-sm-4'],
            ],
        ]) ?>
        <?= $form->field($searchModel, 'address')->dropdownList($listCity, ['prompt' => 'выберите город']); ?>
        <?= $form->field($entrySearchModel, 'day')->widget(DatePicker::classname(), [
            'size' => 'lg',
            'removeButton' => false,
            'type' => DatePicker::TYPE_INPUT,
            'language' => 'ru',
            'pluginOptions' => [
                'autoclose' => true,
                'format' => 'dd-mm-yyyy'
            ],
            'options' => [
                'class' => 'form-control input-sm datepicker',
                'readonly' =>'true',
            ]
        ])->error(false); ?>
        <?= Html::submitButton('Показать', ['class' => 'btn btn-primary btn-sm']) ?>

        <?php ActiveForm::end() ?>
    </div>
</div>