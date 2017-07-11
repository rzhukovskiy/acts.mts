<?php

use common\assets\CanvasJs\CanvasJsAsset;
use common\models\Company;
use yii\bootstrap\Html;
use kartik\grid\GridView;
use common\models\DepartmentCompany;
use common\models\MonthlyAct;

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


$this->title = "Архив актов";

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
$periodForm .= Html::dropDownList('period', $period, DepartmentCompany::$periodList, [
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

$filters = 'Выбор периода: ' . $periodForm;
/**
 * Конец виджета
 */

?>
<?php
echo $this->render('_tabs',
    [
        'type'        => $type,
        'listType'    => $listType,
        'searchModel' => $searchModel
    ]);
?>
<?php

$columns = [];
$columns[] = [
    'header'        => '№',
    'class' => '\kartik\grid\SerialColumn',
    'footer'        => 'Итого:',
    'footerOptions' => ['style' => 'font-weight: bold'],
];
$columns[] = [
    'attribute' => 'client_name',
    'label'     => 'Клиент',
    'content'   => function ($data) use ($type) {
        return $data->client->name;
    },
    'format'    => 'raw',
    'filter'    => ($searchModel->client_id ? false : true),
    'pageSummary' => 'Итого',
];

if($searchModel->client_id) {
    $columns[] = [
        'attribute' => 'act_date',
        'label'     => 'Дата',
        'filter'    => false,
        'content'   => function ($data) {

            // Фикс ошибки вывода даты на англ языке
            $dataArr = explode('-', $data->dateFix());
            if(count($dataArr) == 3) {

                $monthName = [
                    1 => ['Январь', 'Января', 'Январе'],
                    2 => ['Февраль', 'Февраля', 'Феврале'],
                    3 => ['Март', 'Марта', 'Марте'],
                    4 => ['Апрель', 'Апреля', 'Апреле'],
                    5 => ['Май', 'Мая', 'Мае'],
                    6 => ['Июнь', 'Июня', 'Июне'],
                    7 => ['Июль', 'Июля', 'Июле'],
                    8 => ['Август', 'Августа', 'Августе'],
                    9 => ['Сентябрь', 'Сентября', 'Сентябре'],
                    10 => ['Октябрь', 'Октября', 'Октябре'],
                    11 => ['Ноябрь', 'Ноября', 'Ноябре'],
                    12 => ['Декабрь', 'Декабря', 'Декабре']
                ];

                $mountID = (int) $dataArr[1];
                return $monthName[$mountID][0] . ' ' . $dataArr[0];
            } else {
                return Yii::$app->formatter->asDate($data->dateFix(), 'LLLL yyyy');
            }

        },
    ];
}

if($searchModel->client_id && $searchModel->type_id == Company::TYPE_OWNER) {
    $columns[] = [
        'attribute'         => 'type_id',
        'label'             => 'Услуга',
        'filter'    => false,
        'group'             => true,  // enable grouping
        'options'           => ['class' => 'kv-grouped-header'],
        'groupedRow'        => true,  // enable grouping
        'groupOddCssClass'  => 'kv-group-header',  // configure odd group cell css class
        'groupEvenCssClass' => 'kv-group-header', // configure even group cell css class
        'content'           => function ($data) {
            return Company::$listType[$data->type_id]['ru'];
        },
    ];
}

if($searchModel->client_id && $searchModel->type_id == Company::TYPE_DISINFECT) {
    $columns[] = [
        'attribute' => 'service_id',
        'filter'    => false,
        'label'     => 'Услуга',
        'content'   => function ($data) {
            return $data->service->description;
        },
    ];
}

if($searchModel->client_id && $searchModel->type_id == Company::TYPE_SERVICE) {
  $columns[] = [
      'attribute' => 'number',
      'label'     => 'Номер',
      'filter'    => false,
      'content'   => function ($data) {
          return $data->number;
      },
  ];
}

if($searchModel->client_id) {
    $columns[] = [
        'attribute'     => 'profit',
        'value'         => function ($data) {
            return $data->profit;
        },
        'format'        => 'html',
        'filter'    => false,
        'pageSummary' => true,
        'pageSummaryFunc' => GridView::F_SUM,
    ];

    $columns[] = [
        'attribute' => 'payment_status',
        'value' => function ($model, $key, $index, $column) {
            return Html::activeDropDownList($model,
                'payment_status',
                MonthlyAct::$paymentStatus,
                [
                    'class'              => 'form-control change-payment_status',
                    'data-id'            => $model->id,
                    'data-paymentStatus' => $model->payment_status,
                    'disabled'           => MonthlyAct::payDis($model->payment_status) ? 'disabled' : false,
                ]

            );
        },
        'filter' => false,
        'format' => 'raw',
        'contentOptions' => function ($model) {
            return [
                'class' => MonthlyAct::colorForPaymentStatus($model->payment_status),
                'style' => 'min-width: 130px'
            ];
        },
    ];

    $columns[] = [
        'attribute' => 'act_status',
        'value' => function ($model, $key, $index, $column) {
            return Html::activeDropDownList($model,
                'act_status',
                MonthlyAct::passActStatus($model->act_status),
                [
                    'class'          => 'form-control change-act_status',
                    'data-id'        => $model->id,
                    'data-actStatus' => $model->act_status,
                    'disabled'       => MonthlyAct::actDis($model->act_status) ? 'disabled' : false,
                ]);
        },
        'contentOptions' => function ($model) {
            return ['class' => MonthlyAct::colorForStatus($model->act_status), 'style' => 'min-width: 190px'];
        },
        'filter' => false,
        'format' => 'raw',

    ];

}

if(!$searchModel->client_id) {

    $GLOBALS['dateFrom'] = $searchModel->dateFrom;
    $GLOBALS['dateTo'] = $searchModel->dateTo;
    $GLOBALS['comopany'] = $company;

    $columns[] = [
        'label'     => '',
        'contentOptions' => ['style' => 'width: 70px', 'align' => 'center'],
        'content'   => function ($data) use ($type) {
            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                \yii\helpers\Url::to([
                    '/monthly-act/archive',
                    'type'                        => $type,
                    'company'                        => $GLOBALS['comopany'],
                    'MonthlyActSearch[client_id]' => $data->client_id,
                    'MonthlyActSearch[dateFrom]' => $GLOBALS['dateFrom'],
                    'MonthlyActSearch[dateTo]' => $GLOBALS['dateTo']
                ]));
        },
        'format'    => 'raw',
        'filter'    => false,
    ];
}

if($searchModel->client_id) {
    echo GridView::widget([
        'id'               => 'monthly-act-grid',
        'dataProvider'     => $dataProvider,
        'showPageSummary' => ($searchModel->client_id),
        'summary'          => false,
        'emptyText'        => '',
        'panel'            => [
            'type'    => 'primary',
            'heading' => 'Архив актов по ' . \common\models\Company::$listType[$type]['ru'],
            'before'  => false,
            'footer'  => false,
            'after'   => false,
        ],
        'resizableColumns' => false,
        'hover'            => false,
        'striped'          => false,
        'export'           => false,
        'filterSelector'   => '.ext-filter',
        'beforeHeader'     => [
            [
                'columns' => [
                    [
                        'content' => $filters,
                        'options' => [
                            'colspan' => count($columns),
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
                            'colspan' => count($columns),
                        ]
                    ]
                ],
                'options' => ['class' => 'kv-group-header'],
            ],
        ],
        'columns'          => $columns,
    ]);
} else {
    echo GridView::widget([
        'id'               => 'monthly-act-grid',
        'dataProvider'     => $dataProvider,
        'filterModel' => $searchModel,
        'showPageSummary' => ($searchModel->client_id),
        'summary'          => false,
        'emptyText'        => '',
        'panel'            => [
            'type'    => 'primary',
            'heading' => 'Архив актов по ' . \common\models\Company::$listType[$type]['ru'],
            'before'  => false,
            'footer'  => false,
            'after'   => false,
        ],
        'resizableColumns' => false,
        'hover'            => false,
        'striped'          => false,
        'export'           => false,
        'filterSelector'   => '.ext-filter',
        'beforeHeader'     => [
            [
                'columns' => [
                    [
                        'content' => $filters,
                        'options' => [
                            'colspan' => count($columns),
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
                            'colspan' => count($columns),
                        ]
                    ]
                ],
                'options' => ['class' => 'kv-group-header'],
            ],
        ],
        'columns'          => $columns,
    ]);
}

?>
