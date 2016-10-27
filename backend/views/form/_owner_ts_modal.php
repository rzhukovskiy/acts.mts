<?php
use common\models\Type;

$carType = Type::find()->all();
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
    <div class="modal-dialog" style="min-width: 1100px">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">x</span></button>
                <h4 class="modal-title">Выберите вид транспортного средства</h4>
            </div>
            <div class="modal-body">
                <?php
                foreach ($carType as $type) {
                    $img = $type->getImage();
                    if ($img) {
                        $img = \yii\bootstrap\Html::img($img);
                        echo '
                    <button class="btn text-center btn-ts-select">
                        ' . $img . '
                        <h6 data-id="' . $type->id . '" style="width:150px">' . $type->name . '</h6>
                    </button>
                    ';
                    }
                }
                ?>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>