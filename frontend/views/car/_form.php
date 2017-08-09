<?php

/**
 * @var $companyModel \common\models\Company
 * @var $model \common\models\Car
 * @var $expanded bool
 */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\Mark;
use common\models\Type;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $model->isNewRecord ? 'Добавление машины' : 'Редактирование машины' ?>
        <div class="btn btn-xs btn-primary pull-right" data-toggle="collapse" href="#collapseCarAddForm"
             aria-expanded="false" aria-controls="collapseExample">Скрыть/Развернуть
        </div>
    </div>
    <div class="<?= !empty($expanded) ? 'collapse in' : 'collapse' ?>" id="collapseCarAddForm">
        <div class="panel-body">
            <?php
            $form = ActiveForm::begin([
                'action' => $model->isNewRecord ? ['car/create'] : ['car/update', 'id' => $model->id],
                'id' => 'company-form',
                'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
                'fieldConfig' => [
                    'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                    'labelOptions' => ['class' => 'col-sm-2 control-label'],
                    'inputOptions' => ['class' => 'form-control input-sm'],
                ],
            ]) ?>
            <?= $form->field($model, 'number') ?>
            <?= $form->field($model, 'mark_id')->dropDownList(
                Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
                ['prompt' => 'выберите марку ТС']
            ) ?>
            <?= $form->field($model, 'type_id')->dropDownList(
                Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
                ['prompt' => 'выберите тип ТС']
            ) ?>
            <?= $form->field($model, 'is_infected')->checkbox([], false) ?>
            <?= Html::hiddenInput('Car[company_id]', $companyModel->id) ?>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-6">
                    <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
                </div>
            </div>
            <?php ActiveForm::end() ?>

            <?php

            // Кнопки дезинфекции
            echo '<table class="table table-striped table-bordered"><tbody><tr><td align="right">';
            echo Html::a('Включить дезинфекцию для всех ТС', ['car/desinfect', 'id' => $companyModel->id, 'doDesinfect' => 1], ['data-confirm' => "Вы уверены?",
                'data-method' => "post",
                'data-pjax' => "0", 'class' => 'btn btn-warning btn-sm', 'style' => 'margin-right:10px;']);
            echo Html::a('Отключить дезинфекцию для всех ТС', ['car/desinfect', 'id' => $companyModel->id, 'doDesinfect' => 2], ['data-confirm' => "Вы уверены?",
                'data-method' => "post",
                'data-pjax' => "0", 'class' => 'btn btn-danger btn-sm']);
            echo '</td></tr></tbody></table>';
            // Кнопки дезинфекции

            echo $model->isNewRecord ? $this->render('/car/_list',
            [
                'dataProvider' => $companyModel->getCarDataProvider(),
                'searchModel'  => $companyModel->getCarSearchModel(),
                'companyModel'  => $companyModel,
            ]) : ''; ?>
        </div>
    </div>
</div>