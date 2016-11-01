<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Company
 * @var $searchModel \common\models\search\CompanySearch
 * @var $type integer
 * @var $admin null|bool
 */
$this->title = \common\models\Company::$listType[$type]['ru'];
if ($admin) {
    echo $this->render(\common\models\Company::$listType[$type]['en'] . '/_form',
    [
        'model' => $model,
        'type'  => $type,
    ]);
}


echo $this->render(\common\models\Company::$listType[$type]['en'] . '/_list',
[
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
    'type'         => $type,
    'admin'        => $admin
]);