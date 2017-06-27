<?php

/**
 * @var $searchModel common\models\search\ActSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model common\models\Car
 */

use common\components\DateHelper;
use common\models\Company;
use common\models\Service;
use kartik\grid\GridView;
use yii\bootstrap\Html;
use yii\bootstrap\Tabs;
use common\models\Act;

$this->title = 'История машины ' . $model->number;

$request = Yii::$app->request;

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
$periodForm .= Html::dropDownList('year', array_search($currentYear, $rangeYear), range(date('Y') - 10, date('Y')), [
    'id' => 'year',
    'class' => 'autoinput form-control',
    'style' => $diff && $diff <= 12 ? '' : 'display:none'
]);
$periodForm .= Html::activeTextInput($searchModel, 'dateFrom', ['class' => 'date-from ext-filter hidden']);
$periodForm .= Html::activeTextInput($searchModel, 'dateTo',  ['class' => 'date-to ext-filter hidden']);
$periodForm .= Html::submitButton('Показать', ['class' => 'btn btn-primary date-send', 'style' => 'margin-left: 10px;']);

echo Tabs::widget([
    'items' => [
        [
            'label' => 'Машины',
            'url' => ['car/list'],
            'active' => false,
        ],
        [
            'label' => 'История машины',
            'url' => '#',
            'active' => true,
        ],
    ],
]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'summary' => false,
    'emptyText' => '',
    'panel' => [
        'type' => 'primary',
        'heading' => 'История машины ' . $model->number,
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'filterSelector' => '.ext-filter',
    'beforeHeader' => [
        [
            'columns' => [
                [
                    'content' => 'Выбор периода: ' . $periodForm,
                    'options' => ['colspan' => 7, 'class' => 'kv-grid-group-filter period-select'],
                ],
            ],
            'options' => ['class' => 'extend-header'],
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
    'hover' => false,
    'striped' => false,
    'export' => false,
    'showPageSummary' => true,
    'columns' => [
        [
            'header' => '№',
            'class' => 'kartik\grid\SerialColumn',
            'pageSummary' => 'Итого',
        ],
        [
            'attribute' => 'served_at',
            'value' => function ($data) {
                return date('d ', $data->served_at) . DateHelper::getMonthName($data->served_at)[1] . date(' Y', $data->served_at);
            },
        ],
        [
            'attribute' => 'card_id',
            'value' => function ($data) {
                return isset($data->card) ? $data->card->number : 'error';
            },
        ],
        [
            'header' => 'Услуга',
            'value' => function ($data) {
                if ($data->service_type == Service::TYPE_WASH) {
                    /** @var \common\models\ActScope $scope */
                    $services = [];
                    foreach ($data->partnerScopes as $scope) {
                        $services[] = $scope->description;
                    }
                    return implode('+', $services);
                }
                return Service::$listType[$data->service_type]['ru'];
            }
        ],
        [
            'attribute' => 'partner.address',
            'filter' => Company::find()->active()->select(['name', 'id'])->indexBy('id')->column(),
            'value' => function ($data) {
                return isset($data->partner) ? $data->partner->address : 'error';
            },
        ],
        [
            'attribute' => 'income',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
        ],
        [
            'header' => '',
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url, $data, $key) {
                    if (in_array($data->service_type, [Service::TYPE_WASH, Service::TYPE_DISINFECT])) {
                        return '';
                    }
                    return Html::a('<span class="glyphicon glyphicon-search"></span>', ['act-view', 'id' => $data->id]);
                },
            ],
        ],
    ],
]);