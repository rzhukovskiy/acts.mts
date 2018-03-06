<?php
use yii\bootstrap\Tabs;

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Company
 * @var $searchModel \common\models\search\CompanySearch
 * @var $type integer
 * @var $admin bool
 * @var $listType array
 * @var $userData array
 */
$this->title = 'Архив тендеров';

$action = Yii::$app->controller->action->id;

echo Tabs::widget([
    'items' => [
        ['label' => 'Победили', 'url' => ['archivetender?win=1'], 'active' => $win == 1],
        ['label' => 'Проиграли', 'url' => ['archivetender?win=0'], 'active' => $win == 0],
    ],
]);

echo $this->render('_archivetender', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'usersList' => $usersList,
    'win' => $win,
]);