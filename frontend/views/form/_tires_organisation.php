<?php

/**
 * @var $isFirst boolean
 */

?>
<div class="form-group multiple-form-group row <?= ($isFirst) ? 'example" style="display: none;' : ''; ?>">
    <div class="col-md-5">
        <div class="form-group">
            <label>Название</label>
            <input class="form-control input-one" name="TiresForm[organisation_name][]" type="text">
        </div>
    </div>
    <div class="col-md-5">
        <div class="form-group">
            <label>Телефон</label>
            <input class="form-control input-one" name="TiresForm[organisation_phone][]" type="text">
        </div>
    </div>
</div>