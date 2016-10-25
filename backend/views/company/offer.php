<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\CompanyInfo
 */

$this->title = 'Редактирование ' . $model->company->name;

echo $this->render('_update_tabs', [
    'model' => $model->company,
]);

echo $this->render('/company-offer/_form', [
    'model' => $model,
]);