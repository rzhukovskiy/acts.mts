<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 * @var $admin boolean
 */

use kartik\grid\GridView;
use common\models\Changes;
use common\models\Company;
use yii\helpers\Html;

$GLOBALS['authorMembers'] = $authorMembers;
$GLOBALS['arrTypes'] = $arrTypes;
$GLOBALS['serviceList'] = $serviceList;

// Фильтр по дате
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
$periodForm .= Html::dropDownList('period', $period, \common\models\Act::$periodList, [
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
// Фильтр по дате

$columns = [];

if(Yii::$app->controller->action->id == 'card') {
    $columns = [
        [
            'header' => '№',
            'class' => 'kartik\grid\SerialColumn',
            'contentOptions' => ['style' => 'max-width: 40px'],
        ],
        [
            'attribute' => 'old_value',
            'header' => 'Номер карты',
        ],
        [
            'attribute' => 'company_id',
            'header' => 'Старая компания',
            'value' => function ($data) {
                if (isset($data->company_id)) {
                    if ($data->company_id) {

                        if (isset($GLOBALS['arrTypes'][$data->company_id])) {
                            return $GLOBALS['arrTypes'][$data->company_id];
                        } else {
                            return '-';
                        }

                    }
                }
                return '-';
            },
        ],
        [
            'attribute' => 'new_value',
            'header' => 'Новая компания',
            'value' => function ($data) {
                if (isset($data->new_value)) {
                    if ($data->new_value) {

                        if (isset($GLOBALS['arrTypes'][$data->new_value])) {
                            return $GLOBALS['arrTypes'][$data->new_value];
                        } else {
                            return '-';
                        }

                    }
                }
                return '-';
            },
        ],
        [
            'attribute' => 'user_id',
            'value' => function ($data) {
                return isset($GLOBALS['authorMembers'][$data->user_id]) ? $GLOBALS['authorMembers'][$data->user_id] : '';
            },
        ],
        [
            'attribute' => 'status',
            'value' => function ($data) {
                if ($data->status == Changes::NEW_CARD) {
                    return 'Добавление';
                } else if ($data->status == Changes::MOVE_CARD) {
                    return 'Перенос';
                }
            },
        ],
        [
            'attribute' => 'date',
            'value' => function ($data) {
                return date('d-m-Y', $data->date);
            },
        ],
    ];
} else {
    $columns = [
        [
            'header' => '№',
            'class' => 'kartik\grid\SerialColumn',
            'contentOptions' => ['style' => 'max-width: 40px'],
        ],
        [
            'attribute' => 'company_id',
            'filter' => Html::activeDropDownList($searchModel, 'user_id', Changes::find()->innerJoin('company', '`company`.`id` = `changes`.`company_id`')->where(['AND', ['changes.type' => Changes::TYPE_PRICE], ['changes.sub_type' => Yii::$app->request->get('type')], ['between', "DATE(FROM_UNIXTIME(`date`))", $searchModel->dateFrom, $searchModel->dateTo]])->select('company.name')->indexBy('company_id')->column(), ['class' => 'form-control', 'prompt' => 'Все компании']),
            'value' => function ($data) {
                if (isset($data->company_id)) {
                    if ($data->company_id) {
                        $company = Company::find()->where(['id' => $data->company_id])->select('name')->column();

                        if (isset($company[0])) {
                            return $company[0];
                        } else {
                            return '-';
                        }

                    }
                }
                return '';
            },
        ],
        [
            'attribute' => 'service_id',
            'filter' => Html::activeDropDownList($searchModel, 'service_id', Changes::find()->innerJoin('service', '`service`.`id` = `changes`.`service_id`')->where(['AND', ['changes.type' => Changes::TYPE_PRICE], ['changes.sub_type' => Yii::$app->request->get('type')], ['between', "DATE(FROM_UNIXTIME(`date`))", $searchModel->dateFrom, $searchModel->dateTo]])->select('description')->indexBy('service_id')->column(), ['class' => 'form-control', 'prompt' => 'Все услуги']),
            'value' => function ($data) {
                return isset($GLOBALS['serviceList'][$data->service_id]) ? $GLOBALS['serviceList'][$data->service_id] : '-';
            },
        ],
        [
            'attribute' => 'type_id',
            'filter' => Html::activeDropDownList($searchModel, 'type_id', Changes::find()->innerJoin('type', '`type`.`id` = `changes`.`type_id`')->where(['AND', ['changes.type' => Changes::TYPE_PRICE], ['changes.sub_type' => Yii::$app->request->get('type')], ['between', "DATE(FROM_UNIXTIME(`date`))", $searchModel->dateFrom, $searchModel->dateTo]])->select('name')->indexBy('type_id')->column(), ['class' => 'form-control', 'prompt' => 'Все типы']),
            'value' => function ($data) {
                return isset($GLOBALS['arrTypes'][$data->type_id]) ? $GLOBALS['arrTypes'][$data->type_id] : '';
            },
        ],
        [
            'attribute' => 'old_value',
            'value' => function ($data) {
                if ($data->status == Changes::NEW_PRICE) {
                    return '-';
                } else {
                    return $data->old_value;
                }
            },
        ],
        [
            'attribute' => 'new_value',
            'value' => function ($data) {
                return $data->new_value;
            },
        ],
        [
            'attribute' => 'user_id',
            'filter' => Html::activeDropDownList($searchModel, 'user_id', Changes::find()->innerJoin('user', '`user`.`id` = `changes`.`user_id`')->where(['AND', ['changes.type' => Changes::TYPE_PRICE], ['changes.sub_type' => Yii::$app->request->get('type')], ['between', "DATE(FROM_UNIXTIME(`date`))", $searchModel->dateFrom, $searchModel->dateTo]])->select('user.username')->indexBy('user_id')->column(), ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
            'value' => function ($data) {
                return isset($GLOBALS['authorMembers'][$data->user_id]) ? $GLOBALS['authorMembers'][$data->user_id] : '';
            },
        ],
        [
            'attribute' => 'status',
            'filter' => Html::activeDropDownList($searchModel, 'status', [1 => 'Добавление', 2 => 'Изменение'], ['class' => 'form-control', 'prompt' => 'Все действия']),
            'value' => function ($data) {
                if ($data->status == Changes::NEW_PRICE) {
                    return 'Добавление';
                } else {
                    return 'Изменение';
                }
            },
        ],
        [
            'attribute' => 'date',
            'value' => function ($data) {
                return date('d-m-Y', $data->date);
            },
        ],
    ];
}

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $this->title ?>
    </div>
    <div class="panel-body">
        <?php

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'resizableColumns' => false,
            'showPageSummary' => false,
            'emptyText' => '',
            'layout' => '{items}',
            'filterSelector' => '.ext-filter',
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
            'columns' => $columns,
        ]);
        ?>
    </div>
</div>
