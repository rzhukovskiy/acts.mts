<?php
/**
 * @var $isFirst boolean
 */

?>
<div class="form-group multiple-form-group row <?= ($isFirst) ? 'example" style="display: none;' : ''; ?>">
    <div class="col-md-4">
        <div class="form-group">
            <label>Марка ТС</label>
            <input class="form-control input-one" name="OwnerForm[car_mark][]" type="text">
        </div>
    </div>
    <div class="col-md-5">
        <div class="form-group">
            <label>Вид ТС</label>

            <div class="">
                <div class="input-group">
                    <input class="form-control" value="" name="OwnerForm[car_type][]" type="text">
                    <span class="input-group-btn">
                        <button class="btn btn-primary btn-ts-modal btn-edit" data-toggle="modal"
                                data-target="#ts_modal">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label>Количество</label>

            <div class="">
                <div class="input-group">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary btn-number btn-first"
                                            disabled="disabled" data-type="minus" data-field="Request[amount][]">
                                        <span class="glyphicon glyphicon-minus"></span>
                                    </button>
                                </span>
                    <input name="OwnerForm[car_count][]" class="form-control input-number text-center" value="1" min="1"
                           max="10000" type="text">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary btn-number btn-last" data-type="plus"
                                            data-field="Request[amount][]">
                                        <span class="glyphicon glyphicon-plus"></span>
                                    </button>
                                </span>
                </div>
            </div>
        </div>
    </div>
</div>