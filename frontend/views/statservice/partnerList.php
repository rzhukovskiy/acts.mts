<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\UserSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 * @var $newUser \common\models\User
 * @var $admin boolean
 */

$this->title = 'Статистика услуг партнеров';

?>

<?php
echo $this->render('_partnerList', [
'dataProvider' => $dataProvider,
'searchModel'  => $searchModel,
'type'         => $type,
]);
?>