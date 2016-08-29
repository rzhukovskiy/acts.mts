<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;
use common\assets\CanvasJs\CanvasJsAsset;

/**
 * @var $this yii\web\View
 * @var $type integer
 * @var $searchModel \frontend\models\search\ActSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $totalServe float
 * @var $totalProfit float
 * @var $totalExpense float
 * @var $totalIncome float
 */

CanvasJsAsset::register($this);

echo $this->render('_tabs');

echo $this->render('_search', [
    'type' => $type,
    'model' => $searchModel,
])
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $this->title ?>
    </div>
    <div class="panel-body">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => false,
            'emptyCell' => '',
            'showFooter' => true,
            'export' => false,
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn',
                    'footer' => 'Итого:',
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'client_id',
                    'header' => 'Компания',
                    'content' => function ($data) {
                        return !empty($data->client->name) ? Html::a($data->client->name, ['/company-statistic/view', 'id' => $data->client->id, 'type' => $data->service_type]) : '—';
                    },
                    'contentOptions' => ['class' => 'value_0'],
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
                    'attribute' => 'income',
                    'header' => 'Доход',
                    'content' => function ($data) {
                        return Yii::$app->formatter->asDecimal($data->income, 0);
                    },
                    'contentOptions' => ['class' => 'value_1'],
                    'footer' => $totalIncome,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'profit',
                    'header' => 'Прибыль',
                    'content' => function ($data) {
                        return Yii::$app->formatter->asDecimal($data->profit, 0);
                    },
                    'contentOptions' => ['class' => 'value_2'],
                    'footer' => $totalProfit,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/company-statistic/view', 'id' => $model->client->id, 'type' => $model->service_type]);
                        }
                    ]
                ],
            ],
        ]);
        ?>
        <hr>
        <div id="chart_div" style="width:100%;height:500px;"></div>
        <?php
        // TODO: refactor it, plz, move collecting data into controller
        $js = "
            var dataTable = [];
            console.log('Hello');
            $('.table tbody tr').each(function (id, value) {
                dataTable.push({
                    label: $(this).find('.value_0').text(),
                    y: parseInt($(this).find('.value_2').text().replace(' ', '')),
                });
            });
            console.log(dataTable);
            var options = {
                title: {
                    text: 'По компаниям',
                    fontColor: '#069',
                    fontSize: 22,
                },
                data: [
                    {
                        type: 'pie', //change it to line, area, bar, pie, etc
                        dataPoints: dataTable,
                        yValueFormatString: '### ### ###',
                        toolTipContent: '{label}: <strong>{y}</strong>',
                        indexLabel: '{label} - {y}',
                        indexLabelFontSize: 14,
                        indexLabelFontColor: '#069',
                        indexLabelFontWeight: 'bold'
                    }
                ]
            };

            $('#chart_div').CanvasJSChart(options);";
        $this->registerJs($js);
        ?>

    </div>
</div>