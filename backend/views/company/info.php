<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */

$this->title = 'Редактирование ' . $modelCompanyInfo->company->name;

echo $this->render('_update_tabs', [
    'model' => $modelCompanyInfo->company,
]);

echo $this->render('form/_info', [
    'model' => $model,
    'modelCompanyInfo' => $modelCompanyInfo,
]);