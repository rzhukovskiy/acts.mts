<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */

$this->title = 'Статус клиента ' . $modelCompanyInfo->company->name;

echo $this->render('_update_tabs', [
    'model' => $modelCompanyInfo->company,
]);

echo $this->render('_state', [
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
    'model' => $model,
    'modelCompanyInfo' => $modelCompanyInfo,
    'modelCompanyOffer' => $modelCompanyOffer,
    'companyMembers' => $companyMembers,
    'authorMembers' => $authorMembers,
]);