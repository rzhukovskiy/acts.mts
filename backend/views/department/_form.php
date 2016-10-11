<?php

use common\models\Company;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Department;

/* @var $this yii\web\View */
/* @var $model common\models\Department */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $model->isNewRecord ? 'Добавление отдела' : 'Редактирование отдела ' . $model->name ?>
    </div>
    <div class="panel-body">

        <?php $form = ActiveForm::begin([
            'action' => $model->isNewRecord ? ['department/create'] : ['department/update', 'id' => $model->id],
            'id' => 'service-form',
            'options' => ['class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
            'fieldConfig' => [
                'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
                'labelOptions' => ['class' => 'col-sm-2 control-label'],
                'inputOptions' => ['class' => 'form-control input-sm'],
            ],
        ]); ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'role')->dropDownList(Department::$listRole) ?>

        <div class="form-group field-department-name required">
            <label class="col-sm-2 control-label" for="department-name">Заявки</label>
            <div class="col-sm-10">
                <?php foreach (Company::$listType as $companyTypeId => $companyTypeData) {
                    echo Html::checkbox('CompanyType[' . Company::STATUS_NEW . '][' . $companyTypeId . ']', $model->isNewRecord ? false : $model->can($companyTypeId, Company::STATUS_NEW), [
                        'label' => $companyTypeData['ru'],
                        'labelOptions' => [
                            'class' => 'checkbox-inline',
                            'style' => 'margin-right: 10px;'
                        ]
                    ]);
                } ?>
            </div>
        </div>

        <div class="form-group field-department-name required">
            <label class="col-sm-2 control-label" for="department-name">Архив</label>
            <div class="col-sm-10">
                <?php foreach (Company::$listType as $companyTypeId => $companyTypeData) {
                    echo Html::checkbox('CompanyType[' . Company::STATUS_ACTIVE . '][' . $companyTypeId . ']', $model->isNewRecord ? false : $model->can($companyTypeId, Company::STATUS_ACTIVE), [
                        'label' => $companyTypeData['ru'],
                        'labelOptions' => [
                            'class' => 'checkbox-inline',
                            'style' => 'margin-right: 10px;'
                        ]
                    ]);
                } ?>
            </div>
        </div>

        <div class="form-group field-department-name required">
            <label class="col-sm-2 control-label" for="department-name">Отклоненные</label>
            <div class="col-sm-10">
                <?php foreach (Company::$listType as $companyTypeId => $companyTypeData) {
                    echo Html::checkbox('CompanyType[' . Company::STATUS_REFUSE . '][' . $companyTypeId . ']', $model->isNewRecord ? false : $model->can($companyTypeId, Company::STATUS_REFUSE), [
                        'label' => $companyTypeData['ru'],
                        'labelOptions' => [
                            'class' => 'checkbox-inline',
                            'style' => 'margin-right: 10px;'
                        ]
                    ]);
                } ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-6">
                <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Обновить', ['class' => 'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>

    </div>
</div>