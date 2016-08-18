<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model common\models\search\CarSearch
 * @var $form yii\widgets\ActiveForm
 * @var $companyDropDownData array
 * @var $type integer
 */
?>

<div class="car-search">

    <?php $form = ActiveForm::begin([
        'action' => ['view', 'type' => $type],
        'method' => 'get',
        'id' => 'search-cars',
        'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
        'fieldConfig' => [
            'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
            'labelOptions' => ['class' => 'col-sm-2 control-label'],
            'inputOptions' => ['class' => 'form-control input-sm'],
        ],
    ]); ?>

    <?= $form->field($model, 'company_id')->dropDownList($companyDropDownData, ['prompt' => 'Все компании']) ?>

    <?php
    // TODO: Точно пригодится искать по какому-то из этих параметров
    //echo $form->field($model, 'number');
    //echo $form->field($model, 'mark_id');
    //echo $form->field($model, 'type_id');
    // echo $form->field($model, 'is_infected');
    ?>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-6">
            <?= Html::submitButton('Показать машины', ['class' => 'btn btn-primary btn-sm']) ?>
            <?= Html::resetButton('Сбросить', ['class' => 'btn btn-default btn-sm']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>