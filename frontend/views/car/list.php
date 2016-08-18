<?php

use yii\bootstrap\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\CarSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 * @var $admin null|bool
 */

$this->title = 'Машины';

if ($admin) {
    echo $this->render('_tabs');
}

Pjax::begin();
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'floatHeader' => $admin,
    'floatHeaderOptions' => ['scrollingTop' => '0'],
    'hover' => false,
    'striped' => false,
    'export' => false,
    'summary' => false,
    'emptyText' => '',
    'panel' => [
        'type' => 'primary',
        'heading' => 'Машины',
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
            'content' => function ($data) {
                return $data->company->name;
            },
            'group' => $admin,
            'groupedRow' => true,
            'groupOddCssClass' => '',
            'groupEvenCssClass' => '',
            'visible' => $admin,
        ],
        [
            'attribute' => 'company_id',
            'filter' => Html::activeDropDownList($searchModel, 'company_id', $companyDropDownData, ['class' => 'form-control', 'prompt' => 'Все компании']),
            'content' => function ($data) {
                return $data->company->name;
            },
            'visible' => $admin,
        ],
        [
            'attribute' => 'mark_id',
            'content' => function ($data) {
                return !empty($data->mark->name) ? Html::encode($data->mark->name) : '';
            },
            'filter' => false,
        ],
        'number',
        [
            'attribute' => 'type_id',
            'content' => function ($data) {
                return !empty($data->type->name) ? Html::encode($data->type->name) : '';
            },
            'filter' => false,
        ],
        [
            'attribute' => 'listService',
            'content' => function ($data) {
                return count($data->acts);
            },
            'filter' => false,
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
        ],
    ],
]);
Pjax::end();