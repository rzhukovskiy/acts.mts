<?php
use yii\bootstrap\Tabs;

/**
 * @var $this yii\web\View
 * @var $type null|integer
 * @var $model \common\models\Act
 * @var $serviceList array
 * @var $clientScopes \common\models\ActScope[]
 * @var $partnerScopes \common\models\ActScope[]
 * @var $role string
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
            'label' => 'Редактирование',
            'url' => '#',
            'active' => true,
        ],
    ],
]);

echo $this->render('_form', [
    'model' => $model,
    'serviceList' => $serviceList,
    'clientScopes' => $clientScopes,
    'partnerScopes' => $partnerScopes,
]);