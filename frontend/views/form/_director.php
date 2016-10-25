<?php
/**
 * @var $model frontend\models\forms\ServiceForm
 */

?>
<div class="row">
    <div class="col-md-offset-1 col-md-5">
        <div class="form-group">
            <label>Директор</label>
        </div>
        <?= $form->field($model, 'director_fio')->textInput([])->label('ФИО') ?>
        <?= $form->field($model, 'director_phone')->textInput([])->label('Телефон') ?>
        <?= $form->field($model, 'director_email')->textInput([])->label('E-mail') ?>
    </div>
    <div class="col-md-5">
        <div class="form-group">
            <label>Ответственный за договорную работу</label>
        </div>
        <?= $form->field($model, 'manager_fio')->textInput([])->label('ФИО') ?>
        <?= $form->field($model, 'manager_phone')->textInput([])->label('Телефон') ?>
        <?= $form->field($model, 'manager_email')->textInput([])->label('E-mail') ?>
    </div>
</div>