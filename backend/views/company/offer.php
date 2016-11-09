<?php

/**
 * @var $this yii\web\View
 * @var $modelCompany common\models\Company
 * @var $modelCompanyInfo common\models\CompanyInfo
 * @var $modelCompanyOffer common\models\CompanyOffer
 */

$this->title = 'Редактирование ' . $modelCompany->name;

echo $this->render('_update_tabs', [
    'model' => $modelCompany,
]);

echo $this->render('form/_process', [
    'modelCompany' => $modelCompany,
    'modelCompanyInfo' => $modelCompanyInfo,
    'modelCompanyOffer' => $modelCompanyOffer,
]);