<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = 'Новое коммерческое предложение';

$action = Yii::$app->controller->action->id;
echo $this->render('/company/offer/_update_tabs', [
    'model' => $model,
    'listType' => $listType,
]);

echo $this->render($action . '/_list', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'type' => $type,
//    'userData' => $userData,
    'admin' => isset($admin) ? $admin : false,
]);