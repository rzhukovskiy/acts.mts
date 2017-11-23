<?php

/**
 * @var $model \common\models\Company
 * @var $type int
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Service;
use common\models\Type;
use common\models\Company;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Редактирование прайса на ' . Service::$listType[$type]['ru'] ?>
        <div class="btn btn-xs btn-primary pull-right" data-toggle="collapse" href="#collapsePriceForm_<?=$type?>"
             aria-expanded="false" aria-controls="collapseExample">Скрыть/Развернуть</div>
    </div>
    <div class="collapse<?= Yii::$app->session->hasFlash('saved') ? ' in' : '' ?>" id="collapsePriceForm_<?= $type ?>">
        <div class="panel-body">
            <?= in_array($type, [Company::TYPE_WASH, Company::TYPE_DISINFECT, Company::TYPE_PARKING, Company::TYPE_PENALTY]) ? $this->render('/company-service/merged/_list', [
                'dataProvider' => $model->getMergedPriceDataProvider($type),
                'type' => $type,
            ]) : $this->render('/company-service/split/_list', [
                'dataProvider' => $model->getPriceDataProvider($type),
                'type' => $type,
            ]); ?>

            <?php
            $form = ActiveForm::begin([
                'action' => ['company/add-price', 'id' => $model->id],
                'options' => ['class' => 'form-horizontal price-from'],
            ]) ?>
            <table class="table table-bordered">
                <tbody>
                <tr>
                    <td>
                        <?= Html::checkboxList('Price[type]', [], Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column()) ?>
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
        </div>
    </div>
</div>