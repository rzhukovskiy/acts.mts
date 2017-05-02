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

$this->title = 'Статистика услуг компаний';

?>
<div class="user-index">
    <?= $this->render('_tabs') ?>
</div>

<?php
echo $this->render('_list', [
'dataProvider' => $dataProvider,
'searchModel'  => $searchModel,
'type'         => $type,
]);
?>