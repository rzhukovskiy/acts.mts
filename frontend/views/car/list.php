<?php

use kartik\grid\GridView;
use yii\bootstrap\Html;
use common\models\Company;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\ActSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 * @var $admin null|bool
 */

$this->title = 'Машины';

if ($admin) {
    echo $this->render('_tabs');
}

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
$periodForm .= Html::activeHiddenInput($searchModel, 'dateFrom');
$periodForm .= Html::activeHiddenInput($searchModel, 'dateTo');
$periodForm .= Html::submitButton('Показать', ['class' => 'btn btn-primary', 'style' => 'margin-left: 10px;']);

$filters = $admin || empty(Yii::$app->user->identity->company->children) ? '' :
    'Выбор филиала: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
        ->andWhere(['parent_id' => Yii::$app->user->identity->company_id])
        ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
$filters .= 'Выбор периода: ' . $periodForm;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $admin ? $searchModel : null,
    'floatHeader' => $admin,
    'floatHeaderOptions' => ['top' => '0'],
    'hover' => false,
    'striped' => false,
    'export' => false,
    'summary' => false,
    'emptyText' => '',
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
    'panel' => [
        'type' => 'primary',
        'heading' => 'Машины',
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'columns' => [
        [
            'header' => '№',
            'class' => 'yii\grid\SerialColumn'
        ],
        [
            'attribute' => 'company_id',
            'content' => function ($data) {
                return $data->client->name;
            },
            'group' => true,
            'groupedRow' => true,
            'groupOddCssClass' => 'kv-group-header',
            'groupEvenCssClass' => 'kv-group-header',
            'visible' => $admin || !empty(Yii::$app->user->identity->company->children),
        ],
        [
            'attribute' => 'mark_id',
            'content' => function ($data) {
                return !empty($data->mark->name) ? Html::encode($data->mark->name) : '';
            },
        ],
        'number',
        [
            'attribute' => 'type_id',
            'content' => function ($data) {
                return !empty($data->type->name) ? Html::encode($data->type->name) : '';
            },
        ],
        [
            'attribute' => 'actsCount',
            'content' => function ($data) {
                return $data->actsCount;
            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url, $data, $key) {
                    if (!is_null($data->car)) // появился акт для машины призрака
                        return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'id' => $data->car->id]);
                    return Html::tag('span', 'Нет машины', ['class' => 'label label-danger']);
                },
            ],
        ],
    ],
]);