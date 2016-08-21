<?php

use yii\grid\GridView;
use yii\widgets\Pjax;
use dosamigos\chartjs\ChartJs;
use yii\bootstrap\Html;

/**
 * @var $this yii\web\View
 * @var $type integer
 * @var $searchModel \frontend\models\search\ActSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $chartData array
 * @var $totalServe float
 * @var $totalProfit float
 * @var $totalExpense float
 */

echo $this->render('_tabs');
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Фильтр данных по времени
    </div>
    <div class="panel-body">
        <?= $this->render('_search', [
            'type' => $type,
            'model' => $searchModel,
        ]) ?>
    </div>
</div>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $this->title ?>
    </div>
    <div class="panel-body">
        <?php
        // TODO: Change formatting to Yii2 style
        Pjax::begin();
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => false,
            'emptyCell' => '',
            'showFooter' => true,
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn',
                    'footer' => 'Итого:',
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'partner_id',
                    'header' => 'Партнер',
                    'content' => function ($data) {
                        return !empty($data->partner->name) ? Html::a($data->partner->name, ['/statistic/view', 'id' => $data->partner->id]) : '—';
                    },
                ],
                [
                    'header' => 'Город',
                    'attribute' => 'company_id',
                    'content' => function ($data) {
                        return !empty($data->partner->address) ? $data->partner->address : '-';
                    }
                ],
                [
                    'attribute' => 'countServe',
                    'header' => 'Обслужено',
                    'footer' => $totalServe,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'expense',
                    'header' => 'Расход',
                    'content' => function ($data) {
                        return number_format($data->expense, 2, ',', ' ');
                    },
                    'footer' => $totalExpense,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'profit',
                    'header' => 'Прибыль',
                    'content' => function ($data) {
                        return number_format($data->profit, 2, ',', ' ');
                    },
                    'footer' => $totalProfit,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['/statistic/view', 'id' => $model->partner->id]);
                        }
                    ]
                ],
            ],
        ]);
        Pjax::end(); ?>
        <hr>
        <?php
        echo ChartJs::widget([
            'type' => 'pie',
            'options' => [
                'height' => 100,
                'width' => 400
            ],
            'clientOptions' => [
                'legend' => [
                    'position' => 'bottom'
                ]
            ],
            'data' => $chartData
        ]);
        ?>
    </div>
</div>
