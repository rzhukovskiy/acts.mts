<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Contact
 * @var $searchModel \common\models\search\ContactSearch
 * @var $type integer
 * @var $admin boolean
 */
use common\models\Company;

$this->title = Company::$listType[$type]['ru'];

echo $this->render('_tabs',
    [
        'type' => $type,
    ]);

if ($admin) {
    echo $this->render('_form',
        [
            'model'       => $model,
            'searchModel' => $searchModel,
            'type'        => $type,
        ]);

}
echo $this->render('_list',
    [
        'dataProvider' => $dataProvider,
        'searchModel'  => $searchModel,
        'type'         => $type,
        'admin'        => $admin
    ]);