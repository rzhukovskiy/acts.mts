<?php

/**
 * @var $this yii\web\View
 * @var $modelCompany common\models\Company
 */
use common\components\DateHelper;
use kartik\datetime\DateTimePicker;
use yii\helpers\ArrayHelper;

$workTime = $modelCompany->getWorkTimeArray();
?>

<div class="modaltime" style="display: none;">
    <div>
        <div class='radio'>
            <label><input id="radio1" type='radio' name='Company[workTime][type]' value="0">Круглосуточно</label>
        </div>
        <div class='radio'>
            <label><input id="radio2" type='radio' name='Company[workTime][type]' value="1">Ежедневно</label>
        </div>
        <div class='radio'>
            <label><input id="radio3" type='radio' name='Company[workTime][type]' value="2" checked="checked">Другой</label>
        </div>
    </div>

    <div id="everyday" style="display: none;">
        <div style="float: left;padding-top: 10px;width: 5%">C  </div>
        <div style="float: left;width: 25%"><?= DateTimePicker::widget([
            'name' => "Company[workTime][start_time]",
            'value' => isset($workTime[1]['start_time']) ? $workTime[1]['start_time'] : '',
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
            ],
        ]);
        ?>
        </div>
        <div style="float: left;width: 5%;padding-top: 10px;margin-left: 10px;margin-right: 10px">
         По
        </div>
        <div style="float: left;width: 25%">
        <?= DateTimePicker::widget([
            'name' => "Company[workTime][end_time]",
            'value' => isset($workTime[1]['end_time']) ? $workTime[1]['end_time'] : '',
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
            ],
        ]);
        ?>
        </div>
    </div>

    <div id="anyday" style="display: none;">
        <?php for ($day = 1; $day <= 7; $day++) { ?>
            <div style="float: left;padding-top: 10px;width: 13%">
                <?= DateHelper::getWeekDayName($day)?> с
            </div>
            <div style="float: left;width: 25%">
                <?= DateTimePicker::widget([
                    'name' => "Company[workTime][$day][start_time]",
                    'value' => ArrayHelper::getValue($workTime[$day], 'start_time', ''),
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
                    ],
                ]);
                ?>
            </div>
            <div style="float: left;width: 5%;padding-top: 10px;margin-left: 10px;margin-right: 10px">
                По
            </div>
            <div style="float: left;width: 25%">
                <?= DateTimePicker::widget([
                    'name' => "Company[workTime][$day][end_time]",
                    'value' => ArrayHelper::getValue($workTime[$day], 'end_time', ''),
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
                    ],
                ]);
                ?>
            </div>
            <div style="clear: both;"></div>
        <?php } ?>
    </div>
    <div style="clear: both;"></div>
</div>