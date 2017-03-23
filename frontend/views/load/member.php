<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = 'Редактирование ' . $model->company->name;

echo $this->render('_update_tabs', [
    'model' => $model->company,
]);

echo $this->render('/company-member/_list', [
    'model' => $model,
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
]);