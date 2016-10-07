<?php

/**
 * @var $model \common\models\Entry
 * @var $searchModel \common\models\search\EntrySearch
 * @var $serviceList array
 */

use kartik\time\TimePicker;
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
                    <div class="free-time">
                        <?php
                        $step = 0;
                        $listEntry = $searchModel->search([])->getModels();
                        foreach ($listEntry as $entry) {
                            if (!$step) {
                                if (date('H:i', $entry->start_at) != '08:00') {
                                    echo '<div class="col-sm-4">08:00 - ' . date('H:i', $entry->start_at) . '</div><div class="col-sm-4">';
                                } else {
                                    echo '<div class="col-sm-4">';
                                }
                            } else {
                                echo date('H:i', $entry->start_at) . '</div><div class="col-sm-4">';
                            }
                            $step++;
                            if ($step == count($listEntry)) {
                                if (date('H:i', $entry->end_at) != '20:00') {
                                    echo date('H:i', $entry->end_at) . ' - 20:00</div>';
                                } else {
                                    echo '</div>';
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
                    <?= $form->field($model, 'start_str')->widget(TimePicker::classname(), [
                        'addonOptions' => [
                            'style' => 'width: 100px',
                        ],
                        'pluginOptions' => [
                            'defaultTime' => '8:00',
                            'showMeridian' => false,
                        ],
                        'options' => [
                            'class' => 'form-control',
                        ]
                    ])->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'end_str')->widget(TimePicker::classname(), [
                        'addonOptions' => [
                            'style' => 'width: 100px',
                        ],
                        'pluginOptions' => [
                            'defaultTime' => '9:00',
                            'showMeridian' => false,
                        ],
                        'options' => [
                            'class' => 'form-control',
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