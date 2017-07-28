<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = 'Водители';

echo $this->render('_drivers', [
    'model' => $model,
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'arrTypes' => $arrTypes,
    'arrMarks' => $arrMarks,
]);