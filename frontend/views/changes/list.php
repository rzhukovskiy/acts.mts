<?php
/**
 * @var $this yii\web\View
 * @var $type null|integer
 * @var $company null|integer
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 * @var $admin boolean
 */

$request = Yii::$app->request;

if(Yii::$app->controller->action->id == 'card') {
    $this->title = 'История изменения карт';
} else {
    $this->title = 'История изменения цен';
}

if(Yii::$app->controller->action->id == 'price') {
    echo $this->render('_tabs', []);
}

echo $this->render('_list',
[
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
    'authorMembers' => $authorMembers,
    'arrTypes' => $arrTypes,
]);


