<?php

/**
 * @var $searchModel common\models\search\CarSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Car
 */
use yii\bootstrap\Tabs;

$this->title = 'История машины ' . $model->car_number;

$request = Yii::$app->request;

echo Tabs::widget([
    'items' => [
        [
            'label' => 'Машины',
            'url' => ['car/list'],
            'active' => false,
        ],
        [
            'label' => 'История машины',
            'url' => $request->referrer,
            'active' => false,
        ],
        [
            'label' => 'Акт',
            'url' => '#',
            'active' => true,
        ],
    ],
]);

echo $this->render('/act/client/_view', [
    'model' => $model,
    'company' => 1,
]);