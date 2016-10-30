<?php

/**
 * @var $isFirst boolean
 */

?>
<div class="form-group multiple-form-group row <?= ($isFirst) ? 'example" style="display: none;' : ''; ?>">
    <div class="col-md-6">
        <div class="form-group">
            <label>Вид работ</label>
            <input class="form-control input-one" name="ServiceForm[service_type][]" type="text">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Норма часа</label>
            <input class="form-control input-one" name="ServiceForm[service_hour_norm][]" type="text">
        </div>
    </div>
</div>