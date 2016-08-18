<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\CardSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 * @var $admin null|bool
 */

$this->title = 'Карты';

if ($admin) {
    echo $this->render('_form', [
        'model' => $model,
        'companyDropDownData' => $companyDropDownData,
    ]);
}

Pjax::begin();
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $admin ? $searchModel : null,
    'floatHeader' => $admin,
    'floatHeaderOptions' => ['scrollingTop' => '0'],
    'hover' => false,
    'striped' => false,
    'export' => false,
    'summary' => false,
    'emptyText' => '',
    'panel' => [
        'type' => 'primary',
        'heading' => 'Карты',
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'columns' => [
        [
            'header' => '№',
            'class' => 'yii\grid\SerialColumn'
        ],
        [
            'attribute' => 'company_id',
            'filter' => Html::activeDropDownList($searchModel, 'company_id', $companyDropDownData, ['class' => 'form-control', 'prompt' => 'Все компании']),
            'content' => function ($data) {
                return $data->company->name;
            },
            'visible' => $admin,
        ],
        'number',
    ],
]);
Pjax::end();