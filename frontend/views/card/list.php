<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;
use common\models\User;

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
    'floatHeaderOptions' => ['top' => '0'],
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
            'visible' => $admin || !empty($searchModel->company->children),
        ],
        [
            'attribute' => 'number',
            'content' => function ($data) {
                if (!Yii::$app->user->can(User::ROLE_ADMIN) && !Yii::$app->user->can(User::ROLE_WATCHER)) {
                    return count($data->company->cards) . ' (' . $data->company->cardsAsString . ')';
                } else {
                    return $data->number;
                }
            }
        ],
        [
            'attribute' => 'car_number',
            'header'    => 'Номер авто',
            'visible'   => $admin,
        ],
        [
            'attribute' => 'car_mark',
            'header'    => 'Марка',
            'visible'   => $admin,
        ],
        [
            'attribute' => 'car_type',
            'header'    => 'Тип',
            'visible'   => $admin,
        ],
    ],
]);
Pjax::end();