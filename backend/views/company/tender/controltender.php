<?php

use yii\bootstrap\Tabs;
/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */

$this->title = 'Контроль денежных средств';

$params = Yii::$app->request->get("TenderControlSearch");
$activeArchive = 0;

if(isset($params['is_archive'])) {

    if($params['is_archive'] == 1) {
        $activeArchive = 1;
    }

}

echo Tabs::widget([
    'items' => [
        ['label' => 'Активные', 'url' => ['controltender'], 'active' => $activeArchive == 0],
        ['label' => 'Архив', 'url' => ['/company/controltender?TenderControlSearch%5Bis_archive%5D=1'], 'active' => $activeArchive == 1],
    ],
]);

echo $this->render('_controltender', [
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
    'usersList' => $usersList,
]);