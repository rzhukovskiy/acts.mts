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

$this->title = 'Статистика услуг партнеров';

// Вкладки
$items = [];
foreach (Service::$listType as $type_id => $typeData) {
    if($type_id != 5) {
        $items[] = [
            'label' => $typeData['ru'],
            'url' => ['list', 'type' => $type_id],
            'active' => ArrayHelper::getValue(Yii::$app->request->get(), 'type') == $type_id,
        ];
    }
}

echo Tabs::widget([
    'items' => $items,
]);
// Вкладки

?>

<?php
echo $this->render('_partnerList', [
'dataProvider' => $dataProvider,
'searchModel'  => $searchModel,
'type'         => $type,
]);
?>