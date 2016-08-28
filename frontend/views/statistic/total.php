<?php

use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Service;
use yii\bootstrap\Html;
use common\assets\CanvasJs\CanvasJsAsset;

/**
 * @var $this yii\web\View
 * @var $type int
 * @var $searchModel \frontend\models\search\ActSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $chartData array
 * @var $totalProfit int
 * @var $totalServe int
 * @var $totalExpense int
 */

CanvasJsAsset::register($this);

$this->title = 'Общая статистика';
echo $this->render('_tabs');

echo $this->render('_search', [
    'type' => 'total',
    'model' => $searchModel,
]) ?>
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
                        if (empty($data->service_type))
                            $title = '—';
                        else
                            $title = Html::a(Service::$listType[$data->service_type]['ru'], ['/statistic/list', 'type' => $data->service_type]);

                        return $title;
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

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/statistic/list', 'type' => $model->service_type]);
                        },
                    ]
                ],
            ],
        ]);
        Pjax::end();
        ?>
        <hr>

        <div class="col-sm-12">
            <div id="chart_div" style="width:100%;height:500px;"></div>
            <?php
            $js = "CanvasJS.addColorSet('blue', ['#428bca']);
                var dataTable = " . $chartData . ";
                var max = 0;
                dataTable.forEach(function (value) {
                    if (value.y > max) max = value.y;
                });
                var options = {
                    colorSet: 'blue',
                    dataPointMaxWidth: 40,
                    title: {
                        text: 'По месяцам',
                        fontColor: '#069',
                        fontSize: 22
                    },
                    subtitles: [
                        {
                            text: 'Прибыль',
                            horizontalAlign: 'left',
                            fontSize: 14,
                            fontColor: '#069',
                            margin: 20
                        }
                    ],
                    data: [
                        {
                            type: 'column', //change it to line, area, bar, pie, etc
                            dataPoints: dataTable
                        }
                    ],
                    axisX: {
                        title: 'Месяц',
                        titleFontSize: 14,
                        titleFontColor: '#069',
                        titleFontWeight: 'bol',
                        labelFontColor: '#069',
                        labelFontWeight: 'bold',
                        interval: 1,
                        lineThickness: 1,
                        labelFontSize: 14,
                        lineColor: 'black'
                    },

                    axisY: {
                        labelFontColor: '#069',
                        labelFontWeight: 'bold',
                        tickThickness: 1,
                        gridThickness: 1,
                        lineThickness: 1,
                        labelFontSize: 14,
                        lineColor: 'black',
                        valueFormatString: '### ### ###',
                        maximum: max + 0.1 * max
                    }
                };

                $('#chart_div').CanvasJSChart(options);
                ";
            $this->registerJs($js);
            ?>
        </div>

    </div>
</div>
