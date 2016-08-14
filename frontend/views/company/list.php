<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Company
 * @var $type integer
 */
$this->title = \common\models\Company::$listType[$type]['ru'];

echo $this->render(\common\models\Company::$listType[$type]['en'] . '/_form', [
    'model' => $model,
]);

echo $this->render(\common\models\Company::$listType[$type]['en'] . '/_list', [
    'dataProvider' => $dataProvider,
    'type' => $type,
]);