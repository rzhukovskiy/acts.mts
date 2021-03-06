<?php

use common\models\Act;
use kartik\grid\GridView;
use yii\bootstrap\Html;
use common\models\Company;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\ActSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 * @var $admin null|bool
 * @var $client null|bool
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

if ($admin) {
    $filters = 'Выбор компании: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->andWhere(['type' => Company::TYPE_OWNER])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
} elseif (!empty(Yii::$app->user->identity->company->children)) {


    $filters = 'Выбор филиала: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->where(['id' => $arrParParIds])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
}

$filters .= 'Выбор периода: ' . $periodForm;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => ($admin || (Yii::$app->user->identity->role == \common\models\User::ROLE_CLIENT)) ? $searchModel : null,
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
                    'options' => ['colspan' => 7, 'style' => 'vertical-align: middle', 'class' => 'kv-grid-group-filter period-select'],
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
            'filter' => Html::activeDropDownList($searchModel, 'mark_id', \common\models\Mark::find()->innerJoin('act', '`act`.`mark_id` = `mark`.`id`')->where(['OR', ['`act`.`client_id`' => $arrParParIds], ['`act`.`client_id`' => $searchModel->client_id]])->andWhere(['between', 'served_at', strtotime($searchModel->dateFrom), strtotime($searchModel->dateTo)])->andWhere(['!=', '`act`.`service_type`', 5])->select(['name', 'mark.id'])->groupBy('`name`')->indexBy('id')->column(), ['prompt' => 'все марки','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']),
            'content' => function ($data) {
                return !empty($data->mark->name) ? Html::encode($data->mark->name) : '';
            },
        ],
        'car_number',
        [
            'attribute' => 'type_id',
            'filter' => Html::activeDropDownList($searchModel, 'type_id', \common\models\Type::find()->innerJoin('act', '`act`.`type_id` = `type`.`id`')->where(['OR', ['`act`.`client_id`' => $arrParParIds], ['`act`.`client_id`' => $searchModel->client_id]])->andWhere(['between', 'served_at', strtotime($searchModel->dateFrom), strtotime($searchModel->dateTo)])->select(['name', 'type.id'])->andWhere(['!=', '`act`.`service_type`', 5])->select(['name', 'type.id'])->groupBy('`name`')->indexBy('id')->column(), ['prompt' => 'все типы','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']),
            'content' => function ($data) {
                return !empty($data->type->name) ? Html::encode($data->type->name) : '';
            },
        ],
        [
            'attribute' => 'car.is_infected',
            'content' => function ($data) {
                return !empty($data->car->is_infected) ? 'да' : 'нет';
            },
            'visible' => $admin,
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
                    if (!is_null($data->car_id)) { // появился акт для машины призрака

                        $number = $data->car_number;

                        $resCar = \common\models\Car::find()->where(['number' => $number])->select('id')->column();

                        if(count($resCar) > 0) {
                            $car_id = $resCar[0];

                            if (isset(Yii::$app->request->queryParams['ActSearch'])) {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'id' => $car_id, 'ActSearch[dateFrom]' => Yii::$app->request->queryParams['ActSearch']['dateFrom'], 'ActSearch[dateTo]' => Yii::$app->request->queryParams['ActSearch']['dateTo']]);
                            } else {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>', ['view', 'id' => $car_id]);
                            }
                        } else {
                            return Html::tag('span', 'Нет машины', ['class' => 'label label-danger']);
                        }


                    } else {
                        return Html::tag('span', 'Нет машины', ['class' => 'label label-danger']);
                    }
                },
            ],
        ],
    ],
]);