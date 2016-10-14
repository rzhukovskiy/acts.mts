<?php

/**
 * @var $model \common\models\Company
 * @var $type int
 */

use common\models\Service;
use common\models\Type;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= 'Редактирование времени на ' . Service::$listType[$type]['ru'] ?>
        <div class="btn btn-xs btn-primary pull-right" data-toggle="collapse" href="#collapseDurationForm_<?= $type ?>"
             aria-expanded="false" aria-controls="collapseExample">Скрыть/Развернуть
        </div>
    </div>
    <div class="collapse" id="collapseDurationForm_<?= $type ?>">
        <div class="panel-body">
            <?= $this->render('_list',
            [
                'dataProvider' => $model->getDurationDataProvider(),
                'type'         => $type,
            ]) ?>

            <?php
            $form = ActiveForm::begin([
                'action' => ['company/add-duration', 'id' => $model->id],
                'options' => ['class' => 'form-horizontal price-from'],
            ]) ?>
            <table class="table table-bordered">
                <tbody>
                <tr>
                    <td>
                        <?= Html::checkboxList('Duration[type]',
                            [],
                            Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column()) ?>
                    </td>
                    <td style="vertical-align: middle">
                        <div class="form-group">
                            <label class="control-label col-sm-4">Длительность мойки</label>

                            <div class="col-sm-4">
                                <?= Html::textInput("Duration[duration]", '', ['class' => 'form-control input-sm']) ?>
                            </div>
                        </div>
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