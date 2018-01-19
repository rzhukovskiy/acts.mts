<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $entrySearchModel \common\models\search\EntrySearch
 * @var $entryModel \common\models\Entry
 * @var $listCity array
 */
use yii\widgets\ListView;

$this->title = 'Запись' . $companyName;

echo $this->render('_tabs');

echo $this->render('_search', [
    'searchModel' => $searchModel,
    'entrySearchModel' => $entrySearchModel,
    'listCity' => $listCity,
    'companyName' => $companyName,
]);
?>
<div class="row">
<?php
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'viewParams' => [
        'searchModel' => $searchModel,
        'entrySearchModel' => $entrySearchModel,
        'entryModel' => $entryModel,
    ],
    'itemView' => '_short_view',
    'layout' => '{items}',
]);
?>
</div>