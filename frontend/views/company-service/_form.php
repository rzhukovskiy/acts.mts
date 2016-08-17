<?php

/**
 * @var $model \common\models\Company
 * @var $type \common\models\Type
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Service;
use common\models\Type;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Редактирование прайса на ' . Service::$listType[$type]['ru'] ?>
    </div>
    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'action' => ['company/add-price', 'id' => $model->id],
            'options' => ['class' => 'form-horizontal price-from'],
        ]) ?>
        <table class="table table-bordered">
            <tbody>
            <tr>
                <td>
                    <?= Html::checkboxList('Price[type]', [], Type::find()->select(['name', 'id'])->indexBy('id')->column()) ?>
                </td>
                <td style="vertical-align: middle">
                    <?php foreach (Service::findAll(['type' => $type, 'is_fixed' => 1]) as $service) { ?>
                        <div class="form-group">
                            <label class="control-label col-sm-4"><?= $service->description ?></label>

                            <div class="col-sm-4">
                                <?= Html::textInput("Price[service][$service->id]", '', ['class' => 'form-control input-sm']) ?>
                            </div>
                        </div>
                    <?php } ?>
                </td>
                <td>
                    <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary btn-sm']) ?>
                </td>
            </tr>
            </tbody>
        </table>
        <?php ActiveForm::end() ?>

        <?= $this->render('/company-service/_list', [
            'dataProvider' => $model->getPriceDataProvider($type),
        ]); ?>
    </div>
</div>