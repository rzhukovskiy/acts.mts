<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $entrySearchModel \common\models\search\EntrySearch
 * @var $listCity array
 */
use yii\widgets\ListView;

$this->title = 'Мойки';

echo $this->render('_search', [
    'searchModel' => $searchModel,
    'entrySearchModel' => $entrySearchModel,
    'listCity' => $listCity,
]);

echo ListView::widget([
    'dataProvider' => $dataProvider,
    'viewParams' => [
        'entrySearchModel' => $entrySearchModel,
    ],
    'itemView' => '_short_view',
    'layout' => '{items}',
]);