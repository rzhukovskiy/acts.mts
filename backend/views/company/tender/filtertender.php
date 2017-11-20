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
$this->title = 'Список договоров по дате окончания заключенного договора';

$action = Yii::$app->controller->action->id;

echo $this->render('_filtertender', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'admin' => isset($admin) ? $admin : false,
]);