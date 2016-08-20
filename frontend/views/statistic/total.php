<?php

use yii\grid\GridView;
use yii\widgets\Pjax;
use dosamigos\chartjs\ChartJs;
use common\models\Service;

/**
 * @var $this yii\web\View
 * @var $type int
 * @var $searchModel \frontend\models\search\ActSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $chartData array
 * @var $totalProfit int
 * @var $totalServe int
 * @var $totalExpense int
 * @var $monthChart array
 */

$this->title = 'Общая статистика';
echo $this->render('_tabs');
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Фильтр данных по времени
    </div>
    <div class="panel-body">
        <?=$this->render('_search', [
            'type' => 'total',
            'model' => $searchModel,
        ])?>
    </div>
</div>
<div class="panel panel-primary">
    <div class="panel-heading">
        Общая статистика
    </div>
    <div class="panel-body">
        <?php
        Pjax::begin();
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => false,
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
                    'attribute' => 'service_type',
                    'header' => 'Услуга',
                    'content' => function ($data) {
                        return !empty($data->service_type) ? Service::$listType[$data->service_type]['ru'] : '—';
                    },
                ],
                [
                    'attribute' => 'countServe',
                    'header' => 'Обслужено',
                    'footer' => number_format($totalServe, 0, '', ' '),
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'expense',
                    'header' => 'Расход',
                    'content' => function ($data) {
                        return number_format($data->expense, 2, ',', ' ');
                    },
                    'footer' => number_format($totalExpense, 2, ',', ' '),
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'profit',
                    'header' => 'Прибыль',
                    'content' => function ($data) {
                        return number_format($data->profit, 2, ',', ' ');
                    },
                    'footer' => number_format($totalProfit, 2, ',', ' '),
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]);
        Pjax::end();
        ?>
        <hr>
        <div class="row">
            <div class="col-sm-9">
                <div class="well" style="margin-left: 20px">
                    <h4>Прибыль по месяцам</h4>
                    <?php
                    echo ChartJs::widget([
                        'type' => 'line',
                        'options' => [
                            'id' => 'stat_by_months',
                            'height' => 110,
                            'width' => 150,
                            'scales' => [
                                'xAxes' => [
                                    'type' => 'linear',
                                    'position' => 'bottom',
                                ]
                            ]
                        ],
                        'data' => $monthChart,

                    ]);
                    ?>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="well">
                    <h4>Общая статистика услуг</h4>
                    <?php
                    echo ChartJs::widget([
                        'type' => 'pie',
                        'options' => [
                            'id' => 'stat_by_service',
                            'height' => 110,
                            'width' => 150
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
        </div>

    </div>
</div>
