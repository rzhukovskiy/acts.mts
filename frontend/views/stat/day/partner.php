<?php

use common\models\Service;
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
                        $unixDate = strtotime($data->dateMonth);
                        $date = date('d', $unixDate) . ' ' . DateHelper::getMonthName($data->dateMonth, 0) . ' ' . date('Y', $unixDate);

                        return $date;
                    }
                ],
                [
                    'label' => 'Карта',
                    'attribute' => 'card_id',
                    'content' => function ($data) {
                        return empty($data->card->number) ? '—' : $data->card->number;
                    }
                ],
                [
                    'label' => 'Номер ТС',
                    'attribute' => 'car_number',
                ],
                'mark.name',
                'type.name',
                [
                    'label' => 'Услуга',
                    'attribute' => 'service_type',
                    'value' => function ($data) {
                        /** @var \common\models\ActScope $scope */
                        $services = [];
                        foreach ($data->partnerScopes as $scope) {
                            $services[] = $scope->description;
                        }
                        return implode('+', $services);
                    },
                    'visible' => in_array($searchModel->service_type, [Service::TYPE_WASH, Service::TYPE_DISINFECT]),
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
                    'attribute' => 'check',
                    'value' => function ($data) {
                        $imageLink = $data->getImageLink();
                        if ($imageLink) {
                            return Html::a($data->check, $imageLink, ['class' => 'preview']);
                        }
                        return 'error';
                    },
                    'format' => 'raw',
                    'visible' => $searchModel->service_type == Service::TYPE_WASH,
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) use($searchModel) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/act', 'id' => $model->id, 'type' => $searchModel->service_type]);
                        }
                    ],
                    'visible' => in_array($searchModel->service_type, [Service::TYPE_SERVICE, Service::TYPE_TIRES]),
                ],
            ]
        ])
        ?>
    </div>
</div>
