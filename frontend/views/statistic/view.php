<?php

use yii\grid\GridView;
use yii\bootstrap\Html;
use dosamigos\chartjs\ChartJs;

/**
 * @var $this \yii\web\View
 * @var $model \common\models\Company
 * @var $dataProvider \yii\data\ActiveDataProvider
 */

$this->title = $model->name;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Фильтр данных по времени
    </div>
    <div class="panel-body">
        <?php
                echo $this->render('_search', [
                    'type' => null,
                    'companyId' => $model->id,
                    'model' => $searchModel,
                ]);
        ?>
    </div>
</div>

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

                // TODO: group by year maybe?
                [
                    'header' => 'Дата',
                    'attribute' => 'dateMonth',
                    'content' => function ($data) {
                        $date = Yii::$app->formatter->asDate($data->dateMonth, 'php:F Y');
                        return Html::a($date, ['/statistic/by-day', 'id' => $data->partner->id]);
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
                    'attribute' => 'income',
                    'header' => 'Доход',
                    'content' => function ($data) {
                        return number_format($data->income, 2, ',', ' ');
                    },
                    'footer' => $totalIncome,
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
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['/statistic/by-day', 'id' => $model->partner->id]);
                        }
                    ]
                ],
            ]
        ]);
        ?>
        <hr>
        <?php
        //var_dump($chartData);
        echo ChartJs::widget([
            'type' => 'line',
            'options' => [
                'height' => 200,
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
