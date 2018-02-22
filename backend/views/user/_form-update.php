<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use common\models\Company;

/**
 * @var $this yii\web\View
 * @var $model common\models\User
 * @var $form yii\widgets\ActiveForm
 * @var $userModel common\models\User
 */

?>
<div class="user-update-form">
    <?php
    $form = ActiveForm::begin([
        'options' => [
            'class' => 'form-horizontal col-sm-12',
            'style' => 'margin-top: 20px;',
        ],
        'fieldConfig' => [
            'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
            'labelOptions' => ['class' => 'col-sm-2 control-label'],
            'inputOptions' => ['class' => 'form-control input-sm'],
        ],
    ]);
    echo $form->field($model, 'username')->textInput();
    echo $form->field($model, 'email')->textInput();
    echo $form->field($model, 'code')->textInput();
    echo $form->field($model, 'code_pass')->textInput();
    echo $form->field($model, 'newPassword')->passwordInput();
    echo $form->field($model, 'is_account')->checkbox([], false);
    ?>

    <div class="form-group field-department-name required">
        <label class="col-sm-2 control-label" for="department-name">Тендеры</label>
        <div class="col-sm-10">
            <?php foreach (Company::$listType as $companyTypeId => $companyTypeData) {
                    echo Html::checkbox('CompanyType[' . Company::STATUS_TENDER . '][' . $companyTypeId . ']', $userModel->can($companyTypeId, Company::STATUS_TENDER), [
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
        <label class="col-sm-2 control-label" for="department-name">Активные</label>
        <div class="col-sm-10">
            <?php foreach (Company::$listType as $companyTypeId => $companyTypeData) {
                echo Html::checkbox('CompanyType[' . Company::STATUS_ACTIVE . '][' . $companyTypeId . ']', $userModel->can($companyTypeId, Company::STATUS_ACTIVE), [
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
        <label class="col-sm-2 control-label" for="department-name">Заявки</label>
        <div class="col-sm-10">
            <?php foreach (Company::$listType as $companyTypeId => $companyTypeData) {
                echo Html::checkbox('CompanyType[' . Company::STATUS_NEW . '][' . $companyTypeId . ']', $userModel->can($companyTypeId, Company::STATUS_NEW), [
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
        <label class="col-sm-2 control-label" for="department-name">Заявки 2</label>
        <div class="col-sm-10">
            <?php foreach (Company::$listType as $companyTypeId => $companyTypeData) {
                echo Html::checkbox('CompanyType[' . Company::STATUS_NEW2 . '][' . $companyTypeId . ']', $userModel->can($companyTypeId, Company::STATUS_NEW2), [
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
                echo Html::checkbox('CompanyType[' . Company::STATUS_ARCHIVE . '][' . $companyTypeId . ']', $userModel->can($companyTypeId, Company::STATUS_ARCHIVE), [
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
                echo Html::checkbox('CompanyType[' . Company::STATUS_REFUSE . '][' . $companyTypeId . ']', $userModel->can($companyTypeId, Company::STATUS_REFUSE), [
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
        <label class="col-sm-2 control-label" for="department-name">Архив 3</label>
        <div class="col-sm-10">
            <?php foreach (Company::$listType as $companyTypeId => $companyTypeData) {
                echo Html::checkbox('CompanyType[' . Company::STATUS_ARCHIVE3 . '][' . $companyTypeId . ']', $userModel->can($companyTypeId, Company::STATUS_ARCHIVE3), [
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
        <div
            class="col-sm-6 col-sm-offset-2"><?= Html::submitButton('Изменить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>