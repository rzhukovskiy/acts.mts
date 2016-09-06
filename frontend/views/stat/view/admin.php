<?php

use yii\grid\GridView;
use yii\bootstrap\Html;
use common\components\DateHelper;
use common\assets\CanvasJs\CanvasJsAsset;

/**
 * @var $this \yii\web\View
 * @var $group string
 * @var $model \common\models\Company
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \frontend\models\search\ActSearch
 */

CanvasJsAsset::register($this);

echo $this->render('../_search', [
    'type' => null,
    'companyId' => $model->id,
    'model' => $searchModel,
]);
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
                        $date = DateHelper::getMonthName($data->dateMonth, 0) . ' ' . date('Y', strtotime($data->dateMonth));
                        if ($modelType == 'client')
                            return Html::a($date, ['/stat/month', 'id' => $data->client->id, 'date' => date('Y-m', strtotime($data->dateMonth)), 'type' => $data->service_type, 'group' => $group]);

                        return Html::a($date, ['/stat/month', 'id' => $data->partner->id, 'date' => date('Y-m', strtotime($data->dateMonth)), 'type' => $data->service_type, 'group' => $group]);
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
                        'view' => function ($url, $model, $key) use ($group, $modelType) {
                            if ($modelType == 'client')
                                return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/month', 'id' => $model->client->id, 'date' => date('Y-m', strtotime($model->dateMonth)), 'type' => $model->service_type, 'group' => $group]);

                            return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/month', 'id' => $model->partner->id, 'date' => date('Y-m', strtotime($model->dateMonth)), 'type' => $model->service_type, 'group' => $group]);
                        }
                    ]
                ],
            ]
        ]);
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
