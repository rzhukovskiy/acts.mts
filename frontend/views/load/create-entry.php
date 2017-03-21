<?php
use common\models\Service;
use common\models\User;

/**
 * @var $this yii\web\View
 * @var $model \common\models\Entry
 * @var $serviceList array
 * @var $role string
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $entrySearchModel \common\models\search\EntrySearch
 * @var $columns array
 */

$this->title = 'Добавить машину';

echo $this->render('_create_tabs');

echo $this->render('_day_form', [
    'model' => $model,
]);

echo $this->render('_empty_entry_form', [
    'model' => $model,
    'searchModel' => $entrySearchModel,
]);

echo $this->render(Service::$listType[$type]['en'] . '/_entry_form', [
    'model' => $model,
    'serviceList' => $serviceList,
]);

echo $this->render(Service::$listType[$type]['en'] . '/_entry_list', [
    'searchModel' => $entrySearchModel,
    'serviceList' => $serviceList,
]);

echo $this->render('_list', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'role' => $role,
    'hideFilter' => true,
    'columns' => $columns,
]);

