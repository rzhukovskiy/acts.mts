<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Company;
use common\models\Service;
use common\models\Type;
use common\models\Mark;

/* @var $this yii\web\View
 * @var $model common\models\Service
 * @var $form yii\widgets\ActiveForm
 * @var $searchModel common\models\search\ServiceSearch
 */

$partner_services = [];
$client_services = [];

if(!$model->isNewRecord) {
    $select_services = \frontend\controllers\ServiceController::getSelectServices($model->id);
    $partner_services = $select_services[0];
    $client_services = $select_services[1];
}

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?php if($model->isNewRecord) {
            echo 'Добавление замещения';
            ?>
            <div class="btn btn-xs btn-primary pull-right" data-toggle="collapse" href="#collapsePartnerExcludeForm"
                 aria-expanded="false" aria-controls="collapseExample">Скрыть/Развернуть
            </div>
        <?php } else {
            echo 'Редактирование замещения';
        }  ?>
    </div>
    <?php if($model->isNewRecord) { ?><div class="collapse" id="collapsePartnerExcludeForm"><?php } ?>
    <div class="panel-body">
        <?php $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['service/createreplace', 'type' => $type] : ['service/updatereplace', 'id' => $model->id],
            'id' => 'service-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>
        <table class="table table-bordered list-data" style="font-size:13px;">
            <tr style="background: #aedaff;">
                <td class="list-label-md" width="50%"><b>Партнер</b></td>
                <td class="list-label-md" width="50%"><b>Клиент</b></td>
            </tr>
            <tr>
                <td><?= $form->field($model, 'partner_id')->dropDownList(Company::find()->where(['type' => $type])->andWhere(['OR', ['status' => 2], ['status' => 10]])->select('name')->indexBy('id')->orderBy('name')->asArray()->column(), ['class' => 'form-control input-sm', 'prompt' => 'Выберите партнера'])->label(false) ?></td>
                <td><?= $form->field($model, 'client_id')->dropDownList(Company::find()->where(['type' => Company::TYPE_OWNER])->andWhere(['OR', ['status' => 2], ['status' => 10]])->select('name')->indexBy('id')->orderBy('name')->asArray()->column(), ['class' => 'form-control input-sm', 'prompt' => 'Выберите клиента'])->label(false) ?></td>
            </tr>
            <tr style="background: #aedaff;">
                <td colspan="2" class="list-label-md" width="50%"><b>Тип ТС</b></td>
            </tr>
            <tr>
                <td><?= $form->field($model, 'type_partner')->dropDownList(Type::find()->select('name')->indexBy('id')->orderBy('name')->asArray()->column(), ['class' => 'form-control input-sm', 'prompt' => 'Выберите тип ТС'])->label(false) ?></td>
                <td><?= $form->field($model, 'type_client')->dropDownList(Type::find()->select('name')->indexBy('id')->orderBy('name')->asArray()->column(), ['class' => 'form-control input-sm', 'prompt' => 'Выберите тип ТС'])->label(false) ?></td>
            </tr>
            <tr style="background: #aedaff;">
                <td colspan="2" class="list-label-md" width="50%"><b>Марка</b></td>
            </tr>
            <tr>
                <td><?= $form->field($model, 'mark_partner')->dropDownList(Mark::find()->select('name')->indexBy('id')->orderBy('name')->asArray()->column(), ['class' => 'form-control input-sm', 'prompt' => 'Выберите марку ТС'])->label(false) ?></td>
                <td><?= $form->field($model, 'mark_client')->dropDownList(Mark::find()->select('name')->indexBy('id')->orderBy('name')->asArray()->column(), ['class' => 'form-control input-sm', 'prompt' => 'Выберите марку ТС'])->label(false) ?></td>
            </tr>
            <tr style="background: #aedaff;">
                <td colspan="2" class="list-label-md" width="50%"><b>Услуги</b></td>
            </tr>
            <tr>
                <td><?= Html::checkboxList('partner', $partner_services, Service::find()->where(['type' => $type])->select('description')->indexBy('id')->asArray()->column(), ['separator' => '<br />']) ?></td>
                <td><?= Html::checkboxList('client', $client_services, Service::find()->where(['type' => $type])->select('description')->indexBy('id')->asArray()->column(), ['separator' => '<br />']) ?></td>
            </tr>
            <tr>
                <td colspan="2"><?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Обновить', ['class' => 'btn btn-primary btn-sm']) ?></td>
            </tr>
        </table>

        <?php ActiveForm::end(); ?>

    </div>
    <?php if($model->isNewRecord) { ?></div><?php } ?>
</div>
