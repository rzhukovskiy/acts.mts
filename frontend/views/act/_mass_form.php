<?php

/**
 * @var $searchModel \common\models\search\CarSearch
 * @var $serviceList array[]
 * @var $companyList array[]
 */

use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Массовая дезинфекция
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => ['act/disinfect'],
            'method' => 'get',
            'id' => 'act-form',
        ]) ?>
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td>
                    <?= $form->field($searchModel, 'period')->widget(DatePicker::classname(), [
                        'type' => DatePicker::TYPE_INPUT,
                        'language' => 'ru',
                        'pluginOptions' => [
                            'autoclose' => true,
                            'changeMonth' => true,
                            'changeYear' => true,
                            'showButtonPanel' => true,
                            'format' => 'm-yyyy',
                            'maxViewMode' => 2,
                            'minViewMode' => 1,
                        ],
                        'options' => [
                            'class' => 'form-control ext-filter',
                        ]
                    ])->error(false) ?>
                </td>
                <td>
                    <?= $form->field($searchModel, 'company_id')->dropDownList($companyList)->error(false) ?>
                </td>
                <td>
                    <label class="control-label">Услуга</label>
                    <?= Html::dropDownList('serviceId', '', $serviceList, ['label' => 'Услуга', 'class' => 'form-control']) ?>
                </td>
                <td>
                    <label class="control-label">Действие</label>
                    <?= Html::submitButton('Дезинфицировать', ['class' => 'form-control btn btn-primary btn-sm']) ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php ActiveForm::end() ?>
    </div>
</div>