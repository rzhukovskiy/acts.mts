<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Company
 * @var $type integer common\models\Company
 */
$this->title = \common\models\Company::$listType[$type]['ru'];

echo $this->render('_form', [
    'model' => $model,
    'type' => $type,
]);