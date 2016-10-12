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
            <tr>
                <td colspan="3">
                    <label class="control-label">Свободное время:</label>
                    <div class="free-time" style="column-count: 3">
                        <?php
                        $step = 0;
                        $listEntry = $model->company->getFreeTimeArray($model->day);
                        $timeStart = gmdate('H:i', $model->company->info->start_at);
                        $timeEnd = gmdate('H:i', $model->company->info->end_at);
                        foreach ($listEntry as $entry) {
                            if (!$step) {
                                if (date('H:i', $entry->start_at) != $timeStart) {
                                    echo $timeStart . '&nbsp;-&nbsp;' . date('H:i', $entry->start_at) . '<br />';
                                }
                            } else {
                                echo date('H:i', $entry->start_at) . '<br />';
                            }
                            $step++;
                            if ($step == count($listEntry)) {
                                if (date('H:i', $entry->end_at) != $timeEnd) {
                                    echo date('H:i', $entry->end_at) . '&nbsp;-&nbsp' . $timeEnd . '<br />';
                                } else {
                                    echo '<br />';
                                }
                            } else {
                                echo date('H:i', $entry->end_at) . ' - ';
                            }
                        } ?>
                    </div>
                </td>
            </tr>
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
                    <label class="control-label">Действие</label>
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