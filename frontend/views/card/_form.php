<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View
 * @var $model common\models\Card
 * @var $form yii\widgets\ActiveForm
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $admin null|bool
 * @var $searchModel common\models\search\CardSearch
 * @var $companyDropDownData array
 */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $model->isNewRecord ? 'Добавление карты' : 'Редактирование карты ' . $model->number ?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['card/create'] : ['card/update', 'id' => $model->id],
            'id' => 'card-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ])
        ?>

        <?= $form->field($model, 'company_id')->dropDownList($companyDropDownData) ?>

        <?= $form->field($model, 'number') ?>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>

        <?php ActiveForm::end() ?>
    </div>
</div>