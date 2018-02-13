<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Company;
use common\models\Service;

/* @var $this yii\web\View
 * @var $model common\models\Service
 * @var $form yii\widgets\ActiveForm
 */

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?php if($model->isNewRecord) {
            echo 'Добавление привязки';
            ?>
            <div class="btn btn-xs btn-primary pull-right" data-toggle="collapse" href="#collapsePartnerExcludeForm"
                 aria-expanded="false" aria-controls="collapseExample">Скрыть/Развернуть
            </div>
        <?php } else {
            echo 'Редактирование привязки';
        }  ?>
    </div>
    <?php if($model->isNewRecord) { ?><div class="collapse" id="collapsePartnerExcludeForm"><?php } ?>
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['user/createlink', 'type' => $type] : ['user/updatelink', 'id' => $model->id],
            'id' => 'user-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>
        <table class="table table-bordered list-data" style="font-size:13px;">
            <tr style="background: #aedaff;">
                <td class="list-label-md" width="50%"><b><?= $model->getAttributeLabel('user_id') ?></b></td>
                <td class="list-label-md" width="50%"><b><?= $model->getAttributeLabel('company_id') ?></b></td>
            </tr>
            <tr>
                <td><?= $form->field($model, 'user_id')->dropDownList($authorMembers, ['class' => 'form-control input-sm', 'prompt' => 'Выберите сотрудника'])->label(false) ?></td>
                <td><?= $form->field($model, 'company_id')->dropDownList(Company::find()->where(['type' => $type])->andWhere(['OR', ['status' => 2], ['status' => 10]])->select('name')->indexBy('id')->orderBy('name')->asArray()->column(), ['class' => 'form-control input-sm', 'prompt' => 'Выберите компанию'])->label(false) ?></td>
            </tr>
            <tr>
                <td colspan="2"><?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Обновить', ['class' => 'btn btn-primary btn-sm']) ?></td>
            </tr>
        </table>

        <?php ActiveForm::end(); ?>

    </div>
    <?php if($model->isNewRecord) { ?></div><?php } ?>
</div>
