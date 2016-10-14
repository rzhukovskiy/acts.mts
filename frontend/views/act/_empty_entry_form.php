<?php

/**
 * @var $model \common\models\Entry
 * @var $searchModel \common\models\search\EntrySearch
 * @var $serviceList array
 */

use kartik\datetime\DateTimePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Забронировать время
    </div>
    <div class="panel-body">

        <?php
        $form = ActiveForm::begin([
            'action' => ['act/create-entry', 'type' => $model->service_type],
            'id' => 'act-form',
        ]) ?>
        <table class="table table-bordered">
            <tbody>
            <?php
            $arrayFreeTime = $model->company->getFreeTimeArray($model->day);
            $i = 0;
            foreach ($arrayFreeTime as $freeTime) {
                if (!$i || !($i % 3)) {
                    echo '<tr class="free-time">';
                }
                echo '<td style="width:33%">' . $freeTime['start'] . ' - ' . $freeTime['end'] . '</td>';
                if ($i + 1 == count($arrayFreeTime) && count($arrayFreeTime) % 3) {
                    for ($j = 0; $j < 3 - count($arrayFreeTime) % 3; $j++) {
                        echo '<td style="width:25%"></td>';
                    }
                }
                if ($i + 1 == count($arrayFreeTime) || !(($i + 1) % 3)) {
                    echo '</tr>';
                }
                $i++;
            } ?>
            <tr>
                <td>
                    <?= $form->field($model, 'start_str')->widget(DateTimePicker::classname(), [
                        'size' => 'lg',
                        'removeButton' => false,
                        'pluginOptions' => [
                            'startView' => 1,
                            'showMeridian' => false,
                            'autoclose' => true,
                            'format' => 'hh:ii'
                        ],
                        'options' => [
                            'class' => 'form-control datepicker',
                            'readonly' =>'true',
                        ]
                    ])->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'end_str')->widget(DateTimePicker::classname(), [
                        'size' => 'lg',
                        'removeButton' => false,
                        'pluginOptions' => [
                            'startView' => 1,
                            'showMeridian' => false,
                            'autoclose' => true,
                            'format' => 'hh:ii'
                        ],
                        'options' => [
                            'class' => 'form-control datepicker',
                            'readonly' =>'true',
                        ]
                    ])->error(false) ?>
                </td>
                <td style="width: 150px">
                    <label class="control-label">Действие</label><br />
                    <?= Html::submitButton('Записать', ['class' => 'btn btn-primary']) ?>
                    <?= Html::activeHiddenInput($model, 'day') ?>
                    <?= Html::activeHiddenInput($model, 'company_id') ?>
                    <?= Html::activeHiddenInput($model, 'service_type') ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php ActiveForm::end() ?>
    </div>
</div>