<?php

/**
 * @var $model \common\models\Company
 * @var $type array
 */

use common\models\Service;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Обслуживание
        <div class="btn btn-xs btn-primary pull-right" data-toggle="collapse" href="#collapsePartnerExcludeForm"
             aria-expanded="false" aria-controls="collapseExample">Скрыть/Развернуть
        </div>
    </div>
    <div class="collapse" id="collapsePartnerExcludeForm">
        <div class="panel-body">
            <?php
            $form = ActiveForm::begin([
                'action'  => ['company/update-partner-exclude', 'id' => $model->id],
                'options' => ['class' => 'form-horizontal price-from'],
            ]) ?>

            <?php
            $excludedIds = $model->getExcludedIds();
            foreach (Service::$listType as $type_id => $type) {
                $companyPermission = $model->getCompanyPartner($type['id']);
                $checked = $model->getInvertIds($type['id'], $excludedIds);
                if ($companyPermission) {
                    ?>
                    <div class="form-group">
                        <label class="col-sm-2 control-label"><?= $type['ru'] ?></label>

                        <div class="col-sm-6">
                            <?= Html::checkboxList('partner[' . $type['id'] . ']', $checked, $companyPermission) ?>
                        </div>
                    </div>
                <?php
                }
            } ?>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-6">
                    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
                </div>
            </div>
            <?php ActiveForm::end() ?>
        </div>
    </div>
</div>