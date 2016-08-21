<?php

use yii\bootstrap\Html;
use yii\grid\GridView;
use dosamigos\chartjs\ChartJs;

/**
 * @var $this \yii\web\View
 * @var $model \common\models\Company
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \frontend\models\search\ActSearch
 */

$this->title = $model->name;

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
                        $date = Yii::$app->formatter->asDate($data->dateMonth, 'php:d (l)');
                        return Html::a($date, ['/statistic/by-hours', 'id' => $data->partner->id, 'date' => $data->dateMonth]);
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
            ]
        ])
        ?>
    </div>
</div>
