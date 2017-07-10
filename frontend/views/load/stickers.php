<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 * @var $columns array
 * @var $is_locked bool
 */

use common\models\Company;
use common\models\User;
use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\date\DatePicker;
use yii\web\View;

echo GridView::widget([
    'id' => 'act-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => ($hideFilter || $role != User::ROLE_ADMIN) ? null : $searchModel,
    'summary' => false,
    'emptyText' => '',
    'panel' => [
        'type' => 'primary',
        'heading' => 'Услуги',
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'resizableColumns' => false,
    'hover' => false,
    'striped' => false,
    'export' => false,
    'showPageSummary' => true,
    'filterSelector' => '.ext-filter',
    'beforeHeader' => [
        [
            'columns' => [
                [
                    'content' => $filters,
                    'options' => [
                        'style' => 'vertical-align: middle',
                        'colspan' => count($columns),
                        'class' => 'kv-grid-group-filter',
                    ],
                ]
            ],
            'options' => ['class' => 'extend-header'],
        ],
        [
            'columns' => [
                [
                    //'content' => '<button class="btn btn-primary show-search">Поиск</button>',
                    'options' => [
                        'colspan' => count($columns),
                    ]
                ]
            ],
            'options' => ['class' => 'kv-group-header'],
        ],
    ],
    'columns' => $columns,
]);