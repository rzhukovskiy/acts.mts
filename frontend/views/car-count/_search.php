<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model common\models\search\CarSearch
 * @var $form yii\widgets\ActiveForm
 * @var $companyDropDownData array
 * @var $type integer | null
 */

?>

<div class="car-search">

    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'action' => ['car-count/list'],
        'id' => 'search-cars',
        'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
        'fieldConfig' => [
            'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
            'labelOptions' => ['class' => 'col-sm-2 control-label'],
            'inputOptions' => ['class' => 'form-control input-sm'],
        ],
    ]); ?>

    <?= $form->field($model, 'company_id')->dropDownList($companyDropDownData, ['prompt' => 'Все компании']) ?>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-6">
            <?= Html::submitButton('Показать машины', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>