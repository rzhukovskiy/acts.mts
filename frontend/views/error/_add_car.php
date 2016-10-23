<?php

use common\models\Company;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
$model = new \common\models\Car();
$script = <<< JS
        $(document).on('submit', '#add-car-model-form', function (event, messages, deferreds) {
        var action=$(this).attr('action');
        var car_data=$(this).serialize();
        $.ajax(action, {
            type: 'POST',
            data: car_data
        }).done(function(data) {
            window.location.reload();
        });
        $('#add-car-modal').modal('hide');
        return false;
    })
JS;
$this->registerJs($script, yii\web\View::POS_END);
?>

<div class="modal fade" id="add-car-modal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">Добавить машину</h4>
            </div>
            <div class="modal-body">

                <?php $form = ActiveForm::begin([
                    'action' => ['/car/create'],
                    'id'     => 'add-car-model-form'
                ]); ?>

                <?= $form->field($model, 'company_id')->dropdownList(Company::dataDropDownList(Company::TYPE_OWNER)) ?>
                <?= Html::activeHiddenInput($model, 'number'); ?>
                <?= Html::activeHiddenInput($model, 'mark_id'); ?>
                <?= Html::activeHiddenInput($model, 'type_id'); ?>

                <div class="form-group">
                    <?= Html::submitButton('Добавить',
                        ['class' => 'btn btn-success']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

