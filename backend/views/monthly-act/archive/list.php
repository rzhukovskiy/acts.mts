<?php

use common\assets\CanvasJs\CanvasJsAsset;
use common\models\Company;
use yii\bootstrap\Html;

/**
 * @var $this yii\web\View
 * @var $group string
 * @var $type integer
 * @var $searchModel \frontend\models\search\ActSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $totalServe float
 * @var $totalProfit float
 * @var $totalExpense float
 * @var $title string
 */


$this->title = $title;
$filters = 'Выбор компании: ' . Html::activeDropDownList($searchModel,
        'client_id',
        Company::dataDropDownList(Company::TYPE_OWNER),
        ['prompt' => 'все', 'class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
$filters .= \frontend\widgets\datePeriod\DatePeriodWidget::widget([
    'model'        => $searchModel,
    'dateFromAttr' => 'dateFrom',
    'dateToAttr'   => 'dateTo',
]);
/**
 * Конец виджета
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $this->title ?>
    </div>
    <div class="panel-body">
        <?php
        echo \kartik\grid\GridView::widget([
            'dataProvider'       => $dataProvider,
            'emptyCell'          => '',
            'showFooter'         => true,
            'floatHeader'        => false,
            'floatHeaderOptions' => ['top' => '0'],
            'hover'              => false,
            'striped'            => false,
            'export'             => false,
            'summary'            => false,
            'filterSelector'     => '.ext-filter',
            'beforeHeader'       => [
                [
                    'columns' => [
                        [
                            'content' => $filters,
                            'options' => [
                                'colspan' => 8,
                                'style'   => 'vertical-align: middle',
                                'class'   => 'kv-grid-group-filter period-select'
                            ],
                        ],
                    ],
                    'options' => ['class' => 'filters extend-header'],
                ],
                [
                    'columns' => [
                        [
                            'content' => '&nbsp',
                            'options' => [
                                'colspan' => 8,
                            ]
                        ]
                    ],
                    'options' => ['class' => 'kv-group-header'],
                ],
            ],
            'columns'            => [
                [
                    'header'        => '№',
                    'class'         => 'yii\grid\SerialColumn',
                    'footer'        => 'Итого:',
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'client_id',
                    'label'     => 'Клиент',
                    'content'   => function ($data) {
                        return $data->client->name;
                    },
                ],
                [
                    'attribute' => 'act_date',
                    'label'     => 'Дата',
                    'content'   => function ($data) {
                        return $data->dateMonth;
                    },
                ],
                [
                    'attribute' => 'type_id',
                    'label'     => 'Услуга',
                    'content'   => function ($data) {
                        return Company::$listType[$data->type_id]['ru'];
                    },
                ],
                'profit' => [
                    'attribute'     => 'profit',
                    'value'         => function ($data) {
                        return $data->profit;
                    },
                    'format'        => 'html',
                    'footer'        => $totalProfit,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],


            ],
        ]);
        ?>
    </div>
</div>

<script>

</script>