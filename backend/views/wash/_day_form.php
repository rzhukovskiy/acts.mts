<?php

/**
 * @var $model \common\models\Entry
 */

use kartik\date\DatePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Выбрать день
    </div>
    <div class="panel-body">

        <?php
        $form = ActiveForm::begin([
            'action' => ['wash/view', 'id' => $model->company_id],
            'method' => 'get',
            'id' => 'act-form',
        ]) ?>
        <table class="table table-bordered">
            <tbody>
            <tr>
            <tr>
                <td>
                    <div class="current-time" style="min-width: 180px"><?= $model->day ?></div>
                </td>
                <td>
                    <?= DatePicker::widget([
                        'size' => 'lg',
                        'removeButton' => false,
                        'name' => 'Entry[day]',
                        'value' => $model->day,
                        'language' => 'ru',
                        'pluginOptions' => [
                            'autoclose' => true,
                            'changeMonth' => true,
                            'changeYear' => true,
                            'format' => 'dd-mm-yyyy',
                        ],
                        'options' => [
                            'class' => 'form-control datepicker',
                            'readonly' =>'true',
                        ]
                    ]) ?>
                </td>
                <td style="width: 150px">
                    <?= Html::submitButton('Выбрать', ['class' => 'btn btn-primary']) ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php ActiveForm::end() ?>
    </div>
</div>