<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $group string
 * @var $admin boolean
 * @var $subQuery yii\db\Query
 */

use common\models\Act;
use common\models\Car;
use common\models\Company;
use common\models\Service;
use kartik\grid\DataColumn;
use kartik\grid\GridView;
use yii\helpers\Html;
use common\assets\CanvasJs\CanvasJsAsset;

CanvasJsAsset::register($this);

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
    'class' => 'select-period form-control',
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
$periodForm .= Html::dropDownList('year', array_search($currentYear, $rangeYear), range(date('Y') - 10, date('Y')), [
    'id' => 'year',
    'class' => 'autoinput form-control',
    'style' => $diff && $diff <= 12 ? '' : 'display:none'
]);
$periodForm .= Html::activeTextInput($searchModel, 'dateFrom', ['class' => 'date-from ext-filter hidden']);
$periodForm .= Html::activeTextInput($searchModel, 'dateTo', ['class' => 'date-to ext-filter hidden']);
$periodForm .= Html::submitButton('Показать', ['class' => 'btn btn-primary date-send', 'style' => 'margin-left: 10px;']);

if ($admin) {
    $filters = 'Выбор компании: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->andWhere(['type' => Company::TYPE_OWNER])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все', 'class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
} elseif (!empty(Yii::$app->user->identity->company->children)) {
    $filters = 'Выбор филиала: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->andWhere(['parent_id' => Yii::$app->user->identity->company_id])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все', 'class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
} else {
    $filters = '';
}

$filters .= 'Выбор периода: ' . $periodForm;

$columns = [
    [
        'header' => '№',
        'class' => 'kartik\grid\SerialColumn',
        'contentOptions' => ['style' => 'max-width: 40px'],
        'visible' => $group != 'count' AND $group != 'type' AND $group != 'average',
    ],
    [
        'attribute' => 'client_id',
        'content' => function ($data) {
            return $data->client->name;
        },
        'group' => true,
        'groupedRow' => true,
        'groupOddCssClass' => 'kv-group-header',
        'groupEvenCssClass' => 'kv-group-header',
        //уродская конструкция для получения 0 обслуживаний
        'groupFooter' => $group != 'count' ? null : function ($data) use ($searchModel, $subQuery) {
            $subQuery->addSelect('car_id');
            $notServed = Car::find()
                ->where(['not in', 'id', $subQuery->column()])
                ->andWhere(['company_id' => $searchModel->client_id])
                ->andWhere('type_id != 7')
                ->andWhere('type_id != 8')->count();

            return !$notServed ? null : [
                'content' => [
                    2 => '0 обслуживаний',
                    3 => $notServed,
                    4 => Html::a('<span class="glyphicon glyphicon-search"></span>', [
                        'view',
                        'group' => 'count',
                        'count' => 0,
                        'ActSearch[dateFrom]' => $searchModel->dateFrom,
                        'ActSearch[dateTo]' => $searchModel->dateTo,
                        'ActSearch[client_id]' => $data->client_id,
                        'ActSearch[service_type]' => $searchModel->service_type,
                    ]),
                ],
                'contentOptions' => [
                    4 => ['style' => 'text-align:center'],
                ],
            ];
        }
    ],
    'partner.address',
    [
        'header' => 'Количество машин',
        'value' => function ($data) {
            return $data->actsCount;
        },
    ],
    [
        'header' => '',
        'mergeHeader' => false,
        'class' => 'kartik\grid\ActionColumn',
        'template' => '{view}',
        'width' => '40px',
        'buttons' => [
            'view' => function ($url, $data, $key) use ($group, $searchModel) {
                if ($group == 'city') {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>', [
                        'view',
                        'group' => $group,
                        'ActSearch[address]' => $data->partner->address,
                        'ActSearch[dateFrom]' => $searchModel->dateFrom,
                        'ActSearch[dateTo]' => $searchModel->dateTo,
                        'ActSearch[client_id]' => $data->client_id,
                        'ActSearch[service_type]' => $searchModel->service_type,
                    ]);
                }
                if ($group == 'average') {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>', [
                        'view',
                        'group' => $group,
                        'ActSearch[dateFrom]' => $searchModel->dateFrom,
                        'ActSearch[dateTo]' => $searchModel->dateTo,
                        'ActSearch[client_id]' => $data->client_id,
                        'ActSearch[service_type]' => $searchModel->service_type,
                    ]);
                }
                if ($group == 'type') {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>', [
                        'view',
                        'group' => $group,
                        'ActSearch[dateFrom]' => $searchModel->dateFrom,
                        'ActSearch[dateTo]' => $searchModel->dateTo,
                        'ActSearch[client_id]' => $data->client_id,
                        'ActSearch[service_type]' => $searchModel->service_type,
                    ]);
                }
                if ($group == 'count') {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>', [
                        'view',
                        'group' => $group,
                        'count' => $data->actsCount,
                        'ActSearch[dateFrom]' => $searchModel->dateFrom,
                        'ActSearch[dateTo]' => $searchModel->dateTo,
                        'ActSearch[client_id]' => $data->client_id,
                        'ActSearch[service_type]' => $searchModel->service_type,
                    ]);
                }
            },
        ],
    ],
];

if ($group == 'type') {
    $columns[2] = [
        'header' => 'Тип услуги',
        'value' => function ($data) {
            return Service::$listType[$data->service_type]['ru'];
        }
    ];
}
if ($group == 'average') {
    $columns[2] = [
        'header' => 'Кол-во ТС в парке',
        'value' => function ($data) {
            return count($data->client->getCars()->where('company_id = ' . $data->client->id .  ' AND type_id != 7 AND type_id !=8')->all());
        },
    ];
    $columns[3] = [
        'header' => 'Кол-во обслужившихся<br />машин',
        'value' => function ($data) {
            return \frontend\controllers\AnalyticsController::getWorkCars($data->client->id, $data->service_type, true);
        }
    ];
    $columns[4] = [
        'header' => 'Кол-во операций',
        'value' => function ($data) {
            return $data->actsCount;
        }
    ];
    $columns[5] = [
        'header' => 'Среднее кол-во<br />операций',
        'value' => function ($data) {
            return \frontend\controllers\AnalyticsController::getWorkCars($data->client->id, $data->service_type, false, $data->actsCount);
        }
    ];
}
if ($group == 'count') {
    $columns[2] = [
        'header' => 'Количество обслуживаний',
        'value' => function ($data) {
            return $data->actsCount . ' обслуживаний';
        }
    ];
    $columns[3] = [
        'header' => 'Количество машин',
        'value' => function ($data) {
            return $data->carsCount;
        }
    ];
}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => false,
    'emptyText' => '',
    'filterSelector' => '.ext-filter',
    'panel' => [
        'type' => 'primary',
        'heading' => 'Статистика данных',
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'hover' => false,
    'striped' => false,
    'export' => false,
    'beforeHeader' => [
        [
            'columns' => [
                [
                    'content' => $filters,
                    'options' => [
                        'style' => 'vertical-align: middle',
                        'colspan' => count($columns),
                        'class' => 'kv-grid-group-filter',
                    ],
                ]
            ],
            'options' => ['class' => 'extend-header'],
        ],
    ],
    'columns' => $columns,
]);

if($group == 'count') {
    // TODO: refactor it, plz, move collecting data into controller
    echo "<div class=\"grid-view hide-resize\"><div class=\"panel panel-primary\" style='padding: 10px;'><div id=\"chart_div\" style=\"width:100%;height:500px;\"></div></div></div>";
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
                    text: 'По филиалам',
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
}