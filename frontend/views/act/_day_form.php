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
            'action' => ['act/create-entry', 'type' => $model->service_type],
            'method' => 'get',
            'id' => 'act-form',
        ]) ?>
        <table class="table table-bordered">
            <tbody>
            <tr>
            <tr>
                <td>
                    <div class="free-time">Текущий <?= $model->day ?></div>
                </td>
                <td>
                    <?= DatePicker::widget([
                        'name' => 'day',
                        'value' => $model->day,
                        'language' => 'ru',
                        'pluginOptions' => [
                            'autoclose' => true,
                            'changeMonth' => true,
                            'changeYear' => true,
                            'format' => 'dd-mm-yyyy',
                        ],
                        'options' => [
                            'class' => 'form-control',
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