<?php
use common\models\Type;

$carType = Type::getTypeList();
?>
<style>
    .btn-ts-select img {
        height: 60px;
        width: auto;
    }

    .modal-body .btn {
        margin-bottom: 10px;
    }

    .btn-ts-select {
        background-color: #fff;
    }
</style>
<div class="modal fade-in" id="ts_modal" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">x</span></button>
                <h4 class="modal-title">Выберите вид транспортного средства</h4>
            </div>
            <div class="modal-body">
                <?php
                foreach ($carType as $key => $type) {
                    echo '
                    <button class="btn text-center btn-ts-select">
                        <img src="/images/cars/' . $key . '.jpg">
                        <h6 data-id="' . $key . '">' . $type . '</h6>
                    </button>
                    ';
                }
                ?>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>