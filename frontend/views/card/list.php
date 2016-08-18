<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\CardSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 */

$this->title = 'Карты';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card-index">
    <div class="panel panel-primary">
        <div class="panel-heading">Карты в обращении</div>
        <div class="panel-body">
            <?php Pjax::begin(); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'floatHeader' => true,
                'export' => false,
                'summary' => false,
                'emptyText' => '',
                'columns' => [
                    [
                        'header' => '№',
                        'class' => 'yii\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'company_id',
                        'content' => function ($data) {
                            return $data->company->name;
                        },
                        'filter' => Html::activeDropDownList($searchModel, 'company_id', $companyDropDownData, ['class' => 'form-control', 'prompt' => 'Все компании']),
                    ],
                    'number',
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>