<?php
use kartik\datetime\DateTimePicker;

/**
 * @var $model backend\models\forms\ServiceForm
 */

?>
<div class="form-group">
    <label>Часы работы</label>
</div>
<div class="form-group row">
    <div class="col-md-6">
        <?= $form->field($model, 'work_from')->widget(DateTimePicker::classname(),
        [
            'size'          => 'lg',
            'removeButton'  => false,
            'pluginOptions' => [
                'startView'    => 1,
                'showMeridian' => false,
                'autoclose'    => true,
                'format'       => 'hh:ii'
            ],
            'options'       => [
                'id'       => 'mts_work_from',
                'class'    => 'form-control datepicker',
                'readonly' => 'true',
            ]
        ])->error(false)->label('От') ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'work_to')->widget(DateTimePicker::classname(),
        [
            'size'          => 'lg',
            'removeButton'  => false,
            'pluginOptions' => [
                'startView'    => 1,
                'showMeridian' => false,
                'autoclose'    => true,
                'format'       => 'hh:ii'
            ],
            'options'       => [
                'id'       => 'mts_work_to',
                'class'    => 'form-control datepicker',
                'readonly' => 'true',
            ]
        ])->error(false)->label('До') ?>
    </div>
</div>