<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel common\models\search\ServiceSearch
 * @var $admin boolean
 */

$this->title = (Yii::$app->controller->action->id != 'replace') ? 'Услуги' : 'Замещение услуг';


if(Yii::$app->controller->action->id != 'replace') {
    // раздел услуги

    echo $this->render('_tabs', [
        'model' => $model,
    ]);

    if ($admin) {
        echo $this->render('_form', [
            'model' => $model,
            'searchModel' => $searchModel,
        ]);
    }
    echo $this->render('_list', [
        'dataProvider' => $dataProvider,
        'admin' => $admin,
    ]);
} else {
    // раздел замещения

    echo $this->render('_tabsReplace', [
        'model' => $model,
        'type' => $type,
    ]);

    echo $this->render('_formReplace', [
        'model' => $model,
        'type' => $type,
        'searchModel' => $searchModel,
    ]);

    echo $this->render('_replace', [
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'type' => $type,
        'CarTypes' => $CarTypes,
        'CarMarks' => $CarMarks,
        'CompanyList' => $CompanyList,
    ]);
}