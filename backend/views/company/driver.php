<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

if(\Yii::$app->controller->action->id == 'driver') {
    $this->title = 'Водители ' . $model->company->name;
} else if(\Yii::$app->controller->action->id == 'undriver') {
    $this->title = 'ТС без водителей ' . $model->name;
}

echo $this->render('_update_tabs', [
    'model' => isset($model->company) ? $model->company : $model,
]);

echo $this->render('/company-driver/_list', [
    'model' => $model,
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'arrTypes' => $arrTypes,
    'arrMarks' => $arrMarks,
]);