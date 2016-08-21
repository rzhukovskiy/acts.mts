<?php
use yii\grid\GridView;
use yii\bootstrap\Html;

/**
 * @var $this \yii\web\View
 * @var $model \common\models\Company
 * @var $searchModel \frontend\models\search\ActSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 *
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
                    $date = Yii::$app->formatter->asDate($data->dateMonth, 'php:Y-m-d (l)');
                    return $date;
                }
            ],
            [
                'header' => 'Карта',
                'attribute' => 'card_id',
                'content' => function($data) {
                    return empty($data->card->number) ? '—' : $data->card->number;
                }
            ],
            [
                'header' => 'Номер ТС',
                'attribute' => 'number',
            ],
            'mark.name',
            'type.name',
            [
                'header' => 'Услуга',
                'attribute' => 'service_type',
                'content' => function($data) {
                    return \common\models\Service::$listType[$data->service_type]['ru'];
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
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', ['/statistic/view-act', 'id' => $model->id]);
                    }
                ]
            ],
        ]
    ])
    ?>
</div>
</div>