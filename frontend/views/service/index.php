<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel common\models\search\ServiceSearch
 * @var $admin boolean
 */

$this->title = 'Услуги';

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