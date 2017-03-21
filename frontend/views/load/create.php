<?php
use common\models\Service;
use common\models\User;

/**
 * @var $this yii\web\View
 * @var $type null|integer
 * @var $model \common\models\Act
 * @var $serviceList array
 * @var $role string
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $columns array
 */

$this->title = 'Добавить машину';

echo $this->render('_create_tabs');

if ($role == User::ROLE_PARTNER) {
    echo $this->render(Service::$listType[$type]['en'] . '/_short_form', [
        'serviceList' => $serviceList,
        'model' => $model,
        'role' => $role,
    ]);
}

echo $this->render('_list', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'role' => $role,
    'hideFilter' => true,
    'columns' => $columns,
    'is_locked' => false,
]);

