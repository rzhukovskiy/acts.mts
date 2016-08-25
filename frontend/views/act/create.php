<?php
use common\models\Service;
use common\models\User;

/**
 * @var $this yii\web\View
 * @var $type null|integer
 * @var $model \common\models\Act
 * @var $serviceList array
 * @var $role string
 */

$this->title = 'Добавить машину';

$request = Yii::$app->request;

echo $this->render('_tabs', [
    'role' => $role,
]);

if ($role == User::ROLE_PARTNER) {
    echo $this->render('partner/' . Service::$listType[$type]['en'] . '/_short_form', [
        'serviceList' => $serviceList,
        'model' => $model,
        'role' => $role,
    ]);
}

