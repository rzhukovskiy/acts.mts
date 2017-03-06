<?php
/**
 * @var $this yii\web\View
 * @var $type null|integer
 * @var $group string
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 */

$this->title = 'Анализ данных';

if ($group != 'type') {
    echo $this->render('_tabs', [
        'type' => $type,
        'group' => $group,
    ]);
}

echo $this->render('_list', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'group' => $group,
]);
