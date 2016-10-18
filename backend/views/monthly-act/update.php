<?php
use yii\bootstrap\Tabs;

/**
 * @var $this yii\web\View
 * @var $model \common\models\MonthlyAct
 */

$this->title = 'Редактирование акта';

$request = Yii::$app->request;

echo Tabs::widget([
    'items' => [
        [
            'label'  => 'Акты',
            'url'    => $request->referrer,
            'active' => false,
        ],
        [
            'label'  => 'Редактирование',
            'url'    => '#',
            'active' => true,
        ],
    ],
]);

echo $this->render('_form',
    [
        'model' => $model,

    ]);