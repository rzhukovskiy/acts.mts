<?php
use yii\bootstrap\Html;

/**
 * @var $isFirst boolean
 */

?>
<div class="form-group multiple-form-group row <?= ($isFirst) ? 'example" style="display: none;' : ''; ?>">
    <div class="col-md-6">
        <div class="form-group">
            <label>Название</label>
            <?= Html::input(false, 'WashForm[organisation_name][]', false, ['class' => 'form-control input-one']) ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Телефон</label>
            <?= Html::input(false, 'WashForm[organisation_phone][]', false, ['class' => 'form-control input-one']) ?>
        </div>
    </div>
</div>