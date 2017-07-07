<?php

use common\assets\CanvasJs\CanvasJsAsset;
use common\models\Company;
use yii\bootstrap\Html;
use kartik\grid\GridView;

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

$filters = \frontend\widgets\datePeriod\DatePeriodWidget::widget([
    'model'        => $searchModel,
    'dateFromAttr' => 'dateFrom',
    'dateToAttr'   => 'dateTo',
]);
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
}

if(!$searchModel->client_id) {
    $columns[] = [
        'label'     => '',
        'contentOptions' => ['style' => 'width: 70px', 'align' => 'center'],
        'content'   => function ($data) use ($type) {
            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                \yii\helpers\Url::to([
                    '/monthly-act/archive',
                    'type'                        => $type,
                    'MonthlyActSearch[client_id]' => $data->client_id
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
