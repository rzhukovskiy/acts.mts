<?php

use yii\grid\GridView;
use yii\bootstrap\Html;
use dosamigos\chartjs\ChartJs;
use common\components\DateHelper;

/**
 * @var $this \yii\web\View
 * @var $model \common\models\Company
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \frontend\models\search\ActSearch
 */

echo $this->render('_search', [
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

                // TODO: group by year maybe?
                [
                    'header' => 'Дата',
                    'attribute' => 'dateMonth',
                    'content' => function ($data) {
                        $date = DateHelper::getMonthName($data->dateMonth, 0) . ' ' . date('Y', strtotime($data->dateMonth));
                        return Html::a($date, ['/statistic/by-day', 'id' => $data->partner->id, 'date' => $data->dateMonth]);
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
                        return Yii::$app->formatter->asCurrency($data->expense);
                    },
                    'footer' => $totalExpense,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'income',
                    'header' => 'Доход',
                    'content' => function ($data) {
                        return Yii::$app->formatter->asCurrency($data->income);
                    },
                    'footer' => $totalIncome,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'profit',
                    'header' => 'Прибыль',
                    'content' => function ($data) {
                        return Yii::$app->formatter->asCurrency($data->profit);
                    },
                    'footer' => $totalProfit,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['/statistic/by-day', 'id' => $model->partner->id, 'date' => $model->dateMonth]);
                        }
                    ]
                ],
            ]
        ]);
        ?>
        <hr>
        <div class="col-sm-12">
            <div class="well">
                <h4 class="text-center">Статистика за все время</h4>
                <?php
                echo ChartJs::widget([
                    'type' => 'bar',
                    'options' => [
                        'height' => 100,
                        'width' => 400
                    ],
                    'clientOptions' => [
                        'legend' => [
                            'position' => 'bottom',
                        ]
                    ],
                    'data' => $chartData
                ]);
                ?>
            </div>
        </div>
    </div>
</div>
