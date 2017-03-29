<?php

use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\bootstrap\Html;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\ActSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 * @var $admin null|bool
 */

$this->title = 'Немытые';

if (Yii::$app->user->identity->role == \common\models\User::ROLE_ADMIN) {
    echo $this->render('_tabs');
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'floatHeader' => $admin,
    'floatHeaderOptions' => ['top' => '0'],
    'hover' => false,
    'striped' => false,
    'export' => false,
    'summary' => false,
    'emptyText' => '',
    'beforeHeader' => [
        [
            'columns' => [
                [
                    'content' => 'Выбор даты:',
                    'options' => ['style' => 'vertical-align: middle'],
                ],
                [
                    'content' => DatePicker::widget([
                        'model' => $searchModel,
                        'attribute' => 'dateFrom',
                        'attribute2' => 'dateTo',
                        'separator' => '-',
                        'type' => DatePicker::TYPE_RANGE,
                        'language' => 'ru',
                        'pluginOptions' => [
                            'autoclose' => true,
                            'changeMonth' => true,
                            'changeYear' => true,
                            'showButtonPanel' => true,
                            'format' => 'dd-mm-yyyy',
                        ],
                        'options' => [
                            'class' => 'form-control',
                            'value' => date('d-m-Y'),
                        ]
                    ]),
                    'options' => ['colspan' => 2, 'class' => 'kv-grid-group-filter'],
                ],
                [
                    'content' => Html::submitButton('Показать', ['class' => 'btn btn-primary']),
                ],
                '',
            ],
            'options' => ['class' => 'filters extend-header', 'id' => 'w2-filters'],
        ],
        [
            'columns' => [
                [
                    'content' => '&nbsp',
                    'options' => [
                        'colspan' => 6,
                    ]
                ]
            ],
            'options' => ['class' => 'kv-group-header'],
        ],
    ],
    'panel' => [
        'type' => 'primary',
        'heading' => 'Не мытые',
        'before' => false,
//        'footer' => false,
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
            'groupOddCssClass' => 'kv-group-header',
            'groupEvenCssClass' => 'kv-group-header',
            'visible' => $admin,
        ],
        [
            'attribute' => 'mark_id',
            'content' => function ($data) {
                return !empty($data->mark->name) ? Html::encode($data->mark->name) : '';
            },
        ],
        'car_number',
        [
            'attribute' => 'type_id',
            'content' => function ($data) {
                return !empty($data->type->name) ? Html::encode($data->type->name) : '';
            },
            'contentOptions' => ['style' => 'width: 60%'],
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'id' => $data->id]);
                },
            ],
        ],
    ],
]);