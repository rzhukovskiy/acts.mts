<?php

use common\models\Act;
use kartik\grid\GridView;
use yii\bootstrap\Html;
use common\assets\CanvasJs\CanvasJsAsset;
use common\models\Company;

/**
 * @var $this yii\web\View
 * @var $group string
 * @var $type integer
 * @var $searchModel \frontend\models\search\ActSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $totalServe float
 * @var $totalProfit float
 * @var $totalExpense float
 */

CanvasJsAsset::register($this);

echo $this->render('_tabs', ['action' => $group]);


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
$rangeYear = range(date('Y') - 10, date('Y'));
$currentYear = isset($searchModel->dateFrom)
    ? date('Y', strtotime($searchModel->dateFrom))
    : date('Y');

$currentMonth = isset($searchModel->dateFrom)
    ? date('n', strtotime($searchModel->dateFrom))
    : date('n');
$currentMonth--;

$filters = '';
$periodForm = '';
$periodForm .= Html::dropDownList('period', $period, Act::$periodList, [
    'class' =>'select-period form-control',
    'style' => 'margin-right: 10px;'
]);
$periodForm .= Html::dropDownList('month', $currentMonth, $months, [
    'id' => 'month',
    'class' => 'autoinput form-control',
    'style' => $diff == 1 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('half', $currentMonth < 5 ? 0 : 1, $halfs, [
    'id' => 'half',
    'class' => 'autoinput form-control',
    'style' => $diff == 6 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('quarter', floor($currentMonth / 3), $quarters, [
    'id' => 'quarter',
    'class' => 'autoinput form-control',
    'style' => $diff == 3 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('year', $currentYear, range(date('Y'), date('Y') - 10), [
    'id' => 'year',
    'class' => 'autoinput form-control',
    'style' => $diff && $diff <= 12 ? '' : 'display:none'
]);
$periodForm .= Html::activeTextInput($searchModel, 'dateFrom', ['class' => 'date-from ext-filter hidden']);
$periodForm .= Html::activeTextInput($searchModel, 'dateTo',  ['class' => 'date-to ext-filter hidden']);
$periodForm .= Html::submitButton('Показать', ['class' => 'btn btn-primary date-send', 'style' => 'margin-left: 10px;']);

$filters = '';

if ($admin) {

    if($group == 'partner') {
        $filters = 'Выбор компании: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
                ->andWhere(['type' => $type])
                ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
    } else {
        $filters = 'Выбор компании: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
                ->andWhere(['type' => Company::TYPE_OWNER])
                ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
    }

} elseif (!empty(Yii::$app->user->identity->company->children)) {

    // ищем дочерние дочерних
    $queryPar = Company::find()->where(['parent_id' => Yii::$app->user->identity->company_id])->select('id')->column();

    $arrParParIds = [];

    for ($i = 0; $i < count($queryPar); $i++) {

        $arrParParIds[] = $queryPar[$i];

        $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]])->select('id')->column();

        for ($j = 0; $j < count($queryParPar); $j++) {
            $arrParParIds[] = $queryParPar[$j];
        }

    }

    $filters = 'Выбор филиала: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->where(['id' => $arrParParIds])
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

            'emptyCell' => '',
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
                            'options' => ['colspan' => 8, 'style' => 'vertical-align: middle', 'class' => 'kv-grid-group-filter period-select'],
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
            'rowOptions' => function ($model) use ($group) {
                // Выделяем цветом для каких типов

                if ($group == 'partner') {
                if ($model->partner->car_type == 0) {
                    // грузовые оставляем как есть
                    return '';
                } else if ($model->partner->car_type == 1) {
                    return ['style' => 'background: #dff1d8;'];
                } else if ($model->partner->car_type == 2) {
                    return ['style' => 'background: #f9f5e3;'];
                } else {
                    return '';
                }
                } else {
                    if ($model->client->car_type == 0) {
                        // грузовые оставляем как есть
                        return '';
                    } else if ($model->client->car_type == 1) {
                        return ['style' => 'background: #dff1d8;'];
                    } else if ($model->client->car_type == 2) {
                        return ['style' => 'background: #f9f5e3;'];
                    } else {
                        return '';
                    }
                }

            },
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn',
                    'footer' => 'Итого:',
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

                [
                    'attribute' => 'partner_id',
                    'label' => 'Партнер',
                    'content' => function ($data) use ($group) {
                        if ($group == 'partner')
                            return !empty($data->partner->name) ? Html::a($data->partner->name, ['/stat/view', 'id' => $data->partner->id, 'type' => $data->service_type, 'group' => $group]) : '—';
                        return !empty($data->client->name) ? Html::a($data->client->name, ['/stat/view', 'id' => $data->client->id, 'type' => $data->service_type, 'group' => $group]) : '—';
                        },
                    'contentOptions' => ['class' => 'value_0'],
                ],
                [
                    'label' => 'Город',
                    'attribute' => 'company_id',
                    'content' => function ($data) {
                        return !empty($data->client->address) ? $data->client->address : '-';
                    }
                ],
                [
                    'attribute' => 'countServe',
                    'label' => 'Обслужено',
                    'footer' => $totalServe,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'ssoom',
                    'label' => 'ССООМ',
                    'content' => function ($data) {
                        return Html::tag('strong', Yii::$app->formatter->asDecimal($data->ssoom, 0));
                    },
                    'contentOptions' => ['class' => 'success'],
                ],

                ($group == 'partner') ?
                    $groupCustomColl = [
                        'attribute' => 'expense',
                        'label' => 'Расход',
                        'content' => function ($data) {
                            return Yii::$app->formatter->asDecimal($data->expense, 0);
                        },
                        'contentOptions' => ['class' => 'value_1'],
                        'footer' => $totalExpense,
                        'footerOptions' => ['style' => 'font-weight: bold'],
                    ]
                    :
                    $groupCustomColl = [
                        'attribute' => 'income',
                        'label' => 'Доход',
                        'content' => function ($data) {
                            return Yii::$app->formatter->asDecimal($data->income, 0);
                        },
                        'contentOptions' => ['class' => 'value_1'],
                        'footer' => $totalIncome,
                        'footerOptions' => ['style' => 'font-weight: bold'],
                    ],
                [
                    'attribute' => 'profit',
                    'label' => 'Прибыль',
                    'content' => function ($data) {
                        return Html::tag('strong', Yii::$app->formatter->asDecimal($data->profit, 0));
                    },
                    'contentOptions' => ['class' => 'value_2'],
                    'footer' => $totalProfit,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [

                        'view' => function ($url, $model, $key) use ($group) {

                            if ($group == 'partner') {
                                if (isset(Yii::$app->request->queryParams['ActSearch'])) {
                                    return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/view', 'ActSearch' => Yii::$app->request->queryParams['ActSearch'], 'type' => $model->service_type, 'group' => $group, 'id' => $model->partner->id]);
                                } else {
                                    return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/view', 'id' => $model->partner->id, 'type' => $model->service_type, 'group' => $group]);
                                }
                            } else {
                                if (isset(Yii::$app->request->queryParams['ActSearch'])) {
                                    return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/view', 'ActSearch' => Yii::$app->request->queryParams['ActSearch'], 'type' => $model->service_type, 'group' => $group, 'ActSearch[client_id]' => $model->client_id]);
                                } else {
                                    return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/view', 'ActSearch[client_id]' => $model->client_id, 'type' => $model->service_type, 'group' => $group]);
                                }
                            }

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
                    y: parseInt($(this).find('.value_2').text().replace(/\s+/g, '').replace(',', '')),
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

