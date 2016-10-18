<?php
use yii\bootstrap\Tabs;

/**
 * @var $this yii\web\View
 * @var $type null|integer
 * @var $model \common\models\MonthlyAct
 */

$this->title = 'Детализация акта';

$request = Yii::$app->request;

echo Tabs::widget([
    'items' => [
        [
            'label'  => 'Акты',
            'url'    => $request->referrer,
            'active' => false,
        ],
        [
            'label'  => 'Детализация',
            'url'    => '#',
            'active' => true,
        ],
    ],
]);

echo $this->render('_form_detail',
    [
        'model' => $model,
    ]);