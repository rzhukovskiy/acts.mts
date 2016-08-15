<?php

/**
 * @var $model \common\models\Act
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Mark;
use common\models\Type;
use common\models\Card;

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавить машину
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => ['act/create'],
            'id' => 'act-form',
        ]) ?>
        <table class="table table-striped table-bordered">
            <tbody>
            <tr>
                <td>
                    <?= $form->field($model, 'served_at')->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'card_id')->dropdownList(Card::find()->select(['number', 'id'])->indexBy('id')->column())->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'number')->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'mark_id')->dropdownList(Mark::find()->select(['name', 'id'])->indexBy('id')->column())->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'type_id')->dropdownList(Type::find()->select(['name', 'id'])->indexBy('id')->column(), ['max-width'])->error(false) ?>
                </td>
                <td>
                    <?= $form->field($model, 'check')->error(false) ?>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <div class="form-group">
                        <div class="col-xs-4">
                            <input type="text" class="form-control" name="Act[serviceList][0][description]" placeholder="Услуга" />
                        </div>
                        <div class="col-xs-4">
                            <input type="text" class="form-control" name="Act[serviceList][0][amount]" placeholder="Количество" />
                        </div>
                        <div class="col-xs-2">
                            <input type="text" class="form-control" name="Act[serviceList][0][price]" placeholder="Цена" />
                        </div>
                        <div class="col-xs-1">
                            <button type="button" class="btn btn-primary addButton"><i class="glyphicon glyphicon-plus"></i></button>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-sm']) ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php ActiveForm::end() ?>
    </div>
</div>