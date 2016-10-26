<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\CompanyInfoSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Company Infos';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="company-info-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Company Info', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'company_id',
            'phone',
            'address',
            'address_mail',
            // 'email:email',
            // 'start_at',
            // 'end_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
