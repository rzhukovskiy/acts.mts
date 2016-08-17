<?php

use yii\grid\GridView;
use yii\widgets\Pjax;
use dosamigos\chartjs\ChartJs;

/**
 * @var $this yii\web\View
 * @var $type null|string
 * @var $dataProvider
 * @var $chartData
 * @var $totalServe
 * @var $totalProfit
 * @var $totalExpense
 */

echo $this->render('_tabs');
?>
<div class="panel panel-primary">
    <div class="panel-body">
        <?php Pjax::begin(); ?>
        <?= GridView::widget([
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
                    'attribute' => 'partner_id',
                    'header' => 'Партнер',
                    'content' => function ($data) {
                        return !empty($data->partner->name) ? $data->partner->name : '—';
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
        ]); ?>
        <?php Pjax::end(); ?>
        <hr>
        <?php
        echo ChartJs::widget([
            'type' => 'pie',
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
