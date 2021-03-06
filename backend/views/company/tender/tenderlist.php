<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Company
 * @var $searchModel \common\models\search\CompanySearch
 * @var $type integer
 * @var $admin bool
 * @var $listType array
 * @var $userData array
 */
$this->title = 'Закупки';

$action = Yii::$app->controller->action->id;

echo $this->render('_tenderlist', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'usersList'     => $usersList,
    'arrusersOwner' => json_encode($arrusersOwner),
    'admin' => isset($admin) ? $admin : false,
]);