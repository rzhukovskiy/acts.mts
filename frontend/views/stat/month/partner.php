<?php

use yii\bootstrap\Html;
use yii\grid\GridView;
use common\components\DateHelper;
use common\assets\CanvasJs\CanvasJsAsset;

/**
 * @var $this \yii\web\View
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
                    'label' => 'Дата',
                    'attribute' => 'dateMonth',
                    'content' => function ($data) {
                        $date = date('d', strtotime($data->dateMonth)) . ' ' . DateHelper::getMonthName($data->dateMonth, 1) . ' ' . date('Y', strtotime($data->dateMonth));
                        return $date;
                    }
                ],
                [
                    'attribute' => 'countServe',
                    'label' => 'Обслужено',
                    'footer' => $totalServe,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'expense',
                    'label' => 'Доход',
                    'content' => function ($data) {
                        return Yii::$app->formatter->asDecimal($data->expense, 0);
                    },
                    'footer' => $totalExpense,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {

                            if (isset(Yii::$app->request->queryParams['ActSearch']['client_id'])) {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/day', 'date' => $model->dateMonth, 'type' => $model->service_type, 'ActSearch[client_id]' => Yii::$app->request->queryParams['ActSearch']['client_id']]);
                            } else {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/day', 'date' => $model->dateMonth, 'type' => $model->service_type]);
                            }

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
                var dataTable = '.$chartData.';
                CanvasJS.addColorSet("blue",["#428bca"]);

                var options = {
                    colorSet: "blue",
                    title: {
                        text: "'.$chartTitle.'",
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
