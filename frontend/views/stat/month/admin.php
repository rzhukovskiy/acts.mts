<?php

use yii\bootstrap\Html;
use yii\grid\GridView;
use common\components\DateHelper;
use common\assets\CanvasJs\CanvasJsAsset;

/**
 * @var $this \yii\web\View
 * @var $group string
 * @var $model \common\models\Company
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \frontend\models\search\ActSearch
 * @var $chartData array
 * @var $chartTitle string
 */

CanvasJsAsset::register($this);
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $this->title ?>
    </div>
    <div class="panel-body">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => '{items}',
            'emptyText' => '',
            'showFooter' => true,
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn',
                    'footer' => 'Итого:',
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

                [
                    'header' => 'Дата',
                    'attribute' => 'dateMonth',
                    'content' => function ($data) use ($modelType, $group) {
                        $date = date('d', strtotime($data->dateMonth)) . ' ' . DateHelper::getMonthName($data->dateMonth, 1) . ' ' . date('Y', strtotime($data->dateMonth));
                        return $date;
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
                        return Yii::$app->formatter->asDecimal($data->expense, 0);
                    },
                    'footer' => $totalExpense,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'income',
                    'header' => 'Доход',
                    'content' => function ($data) {
                        return Yii::$app->formatter->asDecimal($data->income, 0);
                    },
                    'footer' => $totalIncome,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'profit',
                    'header' => 'Прибыль',
                    'content' => function ($data) {
                        return Html::tag('strong', Yii::$app->formatter->asDecimal($data->profit, 0));
                    },
                    'footer' => $totalProfit,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) use ($modelType, $group) {
                            if ($modelType == 'client')
                                return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/day', 'id' => $model->client->id, 'date' => $model->dateMonth, 'type' => $model->service_type, 'group' => $group]);

                            return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/day', 'id' => $model->partner->id, 'date' => $model->dateMonth, 'type' => $model->service_type, 'group' => $group]);
                        }
                    ]
                ],
            ]
        ])
        ?>
        <hr>
        <div class="col-sm-12">
            <div id="chart_div" style="width:100%;height:500px;"></div>
            <?php
            $js = '
                var it = 1;
                var dataTable = ' . $chartData . ';
                CanvasJS.addColorSet("blue",["#428bca"]);

                var options = {
                    colorSet: "blue",
                    title: {
                        text: "' . $chartTitle . '",
                        fontColor: "#069",
                        fontSize: 22
                    },
                    dataPointMaxWidth: 30,
                    subtitles:[
                        {
                            text: "Прибыль",
                            horizontalAlign: "left",
                            fontSize: 14,
                            fontColor: "#069",
                            margin: 20
                        }
                    ],
                    data: [
                        {
                            type: "column",
                            dataPoints: dataTable
                        }
                    ],
                    axisX:{
                        title: "Дни месяца",
                        titleFontSize: 14,
                        titleFontColor: "#069",
                        titleFontWeight: "bold",
                        labelFontColor: "#069",
                        labelFontWeight: "bold",
                        interval: 1,
                        lineThickness: 1,
                        labelFontSize: 14,
                        lineColor: "black",
                        margin: 20
                    },

                    axisY:{
                        labelFontColor: "#069",
                        labelFontWeight: "bold",
                        tickThickness: 1,
                        gridThickness: 1,
                        lineThickness: 1,
                        labelFontSize: 14,
                        lineColor: "black",
                        valueFormatString: "### ### ###",
                        stripLines:[
                            {
                                thickness: 1,
                                value:0,
                                color:"#000"
                            }
                        ]
                    }
                };

                $("#chart_div").CanvasJSChart(options);';

            $this->registerJs($js);
            ?>
        </div>
    </div>
</div>
