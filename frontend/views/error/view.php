<?php
use yii\bootstrap\Tabs;

/**
 * @var $this yii\web\View
 * @var $model \common\models\Act
 * @var $company bool
 */

$this->title = 'Редактирование акта';

$request = Yii::$app->request;

echo Tabs::widget([
    'items' => [
        [
            'label' => 'Акты',
            'url' => $request->referrer,
            'active' => false,
        ],
        [
            'label' => 'Предварительный акт',
            'url' => '#',
            'active' => true,
        ],
    ],
]);

echo $this->render($company ? 'client/_view' : 'partner/_view', [
    'model' => $model,
]);