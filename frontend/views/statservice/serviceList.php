<?php
use yii\bootstrap\Tabs;
use common\models\Service;
use yii\helpers\ArrayHelper;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\UserSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 * @var $newUser \common\models\User
 * @var $admin boolean
 */

if($company == 0) {
    $this->title = 'Статистика компаний по партнерам';
} else {
    $this->title = 'Статистика партнеров по компаниям';
}

// Вкладки
$items = [];
foreach (Service::$listType as $type_id => $typeData) {
    if(($type_id != 5) && ($company != 1)) {
        $items[] = [
            'label' => $typeData['ru'],
            'url' => ['service', 'type' => $type_id],
            'active' => Yii::$app->request->get('type') == $type_id,
        ];
    } else if($company == 1) {
        $items[] = [
            'label' => $typeData['ru'],
            'url' => ['service', 'type' => $type_id, 'company' => $company],
            'active' => Yii::$app->request->get('type') == $type_id,
        ];
    }
}

echo Tabs::widget([
    'items' => $items,
]);
// Вкладки

?>

<?php
echo $this->render('_serviceList', [
'dataProvider' => $dataProvider,
'searchModel'  => $searchModel,
'type'         => $type,
'company'         => $company,
]);
?>