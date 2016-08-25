<?php
/**
 * @var $this yii\web\View
 * @var $type null|integer
 * @var $company null|integer
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 */

use common\models\Service;

$this->title = 'Акты';

$request = Yii::$app->request;

echo $this->render('_tabs', [
    'role' => $role,
]);

echo $this->render($company ? 'client/' . Service::$listType[$type]['en'] . '/_list' : 'partner/' . Service::$listType[$type]['en'] . '/_list', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'role' => $role,
]);

