<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */

$this->title = 'Тендеры клиента ' . $model->name;

echo $this->render('_update_tabs', [
    'model' => $model,
]);

echo $this->render('_tenders', [
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
    'model' => $model,
    'usersList' => $usersList,
]);