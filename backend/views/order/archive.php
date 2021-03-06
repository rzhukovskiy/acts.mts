<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $entrySearchModel \common\models\search\EntrySearch
 * @var $listCity array
 */

$this->title = 'Запись';

echo $this->render('_tabs');

// Старая форма поиска по дню
/*echo $this->render('_date_selector', [
    'entrySearchModel' => $searchModel,
]);*/

echo $this->render('_list', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
]);