<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel  common\models\search\MonthlyActSearch
 * @var $type integer
 * @var $admin boolean
 */
$this->title = \common\models\Company::$listType[$type]['ru'];

echo $this->render('_tabs',
    [
        'type'        => $type,
        'listType'    => $listType,
        'searchModel' => $searchModel
    ]);


echo $this->render('_list',
    [
        'dataProvider' => $dataProvider,
        'searchModel'  => $searchModel,
        'type'         => $type,
        'admin'        => $admin
    ]);