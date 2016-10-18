<?php

use common\models\Company;
use kartik\grid\GridView;
use yii\bootstrap\Html;
use common\assets\CanvasJs\CanvasJsAsset;
use common\models\Service;
/**
 * @var $this \yii\web\View
 * @var $group string
 * @var $model \common\models\Company
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $searchModel \frontend\models\search\ActSearch
 */

CanvasJsAsset::register($this);
echo $this->render('../view/_client_tabs', ['action' => 'total']);
$this->title = 'Общая статистика';
/**
 * Виджет выбора диапазона дат
 */
$halfs = [
    '1е полугодие',
    '2е полугодие'
];
$quarters = [
    '1й квартал',
    '2й квартал',
    '3й квартал',
    '4й квартал',
];
$months = [
    'январь',
    'февраль',
    'март',
    'апрель',
    'май',
    'июнь',
    'июль',
    'август',
    'сентябрь',
    'октябрь',
    'ноябрь',
    'декабрь',
];

$ts1 = strtotime($searchModel->dateFrom);
$ts2 = strtotime($searchModel->dateTo);

$year1 = date('Y', $ts1);
$year2 = date('Y', $ts2);

$month1 = date('m', $ts1);
$month2 = date('m', $ts2);

$diff = (($year2 - $year1) * 12) + ($month2 - $month1);
switch ($diff) {
    case 1:
        $period = 1;
        break;
    case 3:
        $period = 2;
        break;
    case 6:
        $period = 3;
        break;
    case 12:
        $period = 4;
        break;
    default:
        $period = 0;
}

$periodForm = '';
$periodForm .= Html::dropDownList('period', $period, \common\models\Act::$periodList, ['class' =>'select-period form-control', 'style' => 'margin-right: 10px;']);
$periodForm .= Html::dropDownList('month', '', $months, ['id' => 'month', 'class' => 'autoinput form-control', 'style' => $diff == 1 ? '' : 'display:none']);
$periodForm .= Html::dropDownList('half', '', $halfs, ['id' => 'half', 'class' => 'autoinput form-control', 'style' => $diff == 6 ? '' : 'display:none']);
$periodForm .= Html::dropDownList('quarter', '', $quarters, ['id' => 'quarter', 'class' => 'autoinput form-control', 'style' => $diff == 3 ? '' : 'display:none']);
$periodForm .= Html::dropDownList('year', 10, range(date('Y') - 10, date('Y')), ['id' => 'year', 'class' => 'autoinput form-control', 'style' => $diff && $diff <= 12 ? '' : 'display:none']);
$periodForm .= Html::activeTextInput($searchModel, 'dateFrom', ['class' => 'date-from ext-filter hidden']);
$periodForm .= Html::activeTextInput($searchModel, 'dateTo',  ['class' => 'date-to ext-filter hidden']);
$periodForm .= Html::submitButton('Показать', ['class' => 'btn btn-primary date-send', 'style' => 'margin-left: 10px;']);

if ($admin) {
    $filters = 'Выбор компании: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->andWhere(['type' => Company::TYPE_OWNER])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
} elseif (!empty(Yii::$app->user->identity->company->children)) {
    $filters = 'Выбор филиала: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->andWhere(['parent_id' => Yii::$app->user->identity->company_id])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
}

$filters .= 'Выбор периода: ' . $periodForm;

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

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => '{items}',
            'emptyText' => '',

            'showFooter' => true,
            'floatHeader' => $admin,
            'floatHeaderOptions' => ['top' => '0'],
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'filterSelector' => '.ext-filter',
            'beforeHeader' => [
                [
                    'columns' => [
                        [
                            'content' => $filters,
                            'options' => ['colspan' => 6, 'style' => 'vertical-align: middle', 'class' => 'kv-grid-group-filter period-select'],
                        ],
                    ],
                    'options' => ['class' => 'filters extend-header'],
                ],
                [
                    'columns' => [
                        [
                            'content' => '&nbsp',
                            'options' => [
                                'colspan' => 7,
                            ]
                        ]
                    ],
                    'options' => ['class' => 'kv-group-header'],
                ],
            ],

            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn',
                    'footer' => 'Итого:',
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

                [
                    'attribute' => 'service_type',
                    'header' => 'Услуга',
                    'content' => function ($data) {
                        if (empty($data->service_type))
                            $title = '—';
                        else
                            $title = Html::a(Service::$listType[$data->service_type]['ru'], ['/stat/view', 'type' => $data->service_type]);

                        return $title;
                    },
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
