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
                    'header' => 'Дата',
                    'attribute' => 'dateMonth',
                    'content' => function ($data) {
                        $unixDate = strtotime($data->dateMonth);
                        $date = date('d', $unixDate) . ' ' . DateHelper::getMonthName($data->dateMonth, 0) . ' ' . date('Y', $unixDate);

                        return $date;
                    }
                ],
                [
                    'header' => 'Карта',
                    'attribute' => 'card_id',
                    'content' => function ($data) {
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
                    'content' => function ($data) {
                        return \common\models\Service::$listType[$data->service_type]['ru'];
                    }
                ],
                [
                    'attribute' => 'expense',
                    'header' => 'Доход',
                    'content' => function ($data) {
                        return Yii::$app->formatter->asDecimal($data->expense, 0);
                    },
                    'footer' => $totalExpense,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

//                [
//                    'class' => 'yii\grid\ActionColumn',
//                    'template' => '{view}',
//                    'buttons' => [
//                        'view' => function ($url, $model, $key) {
//                            return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/act', 'id' => $model->id]);
//                        }
//                    ]
//                ],
            ]
        ])
        ?>
    </div>
</div>
