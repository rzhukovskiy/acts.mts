<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Company
 * @var $searchModel \common\models\search\CompanySearch
 * @var $type integer
 */

$action = Yii::$app->controller->action->id;
echo $this->render('_tabs', [
    'model' => $model,
    'listType' => $listType,
]);

echo $this->render($action . '/_list', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'type' => $type,
]);

echo $this->render(\common\models\Company::$listType[$type]['en'] . '/_form', [
    'model' => $model,
    'type' => $type,
]);