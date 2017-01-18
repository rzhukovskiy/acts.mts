<?php
/**
 * @var $this yii\web\View
 * @var $type null|integer
 * @var $company null|integer
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 * @var $columns array
 * @var $is_locked bool
 */

$this->title = 'Акты';

echo $this->render('_tabs', [
    'role' => $role,
]);

echo $this->render('_list', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'role' => $role,
    'hideFilter' => false,
    'columns' => $columns,
    'is_locked' => $is_locked,
]);

