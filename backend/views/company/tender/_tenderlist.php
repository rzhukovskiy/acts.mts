<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use common\models\Company;
use kartik\date\DatePicker;
use common\models\TenderLists;

$script = <<< JS
// формат числа
window.onload=function(){
  var formatSum = $('td[data-col-seq="10"]');
  $(formatSum).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
};
JS;
$this->registerJs($script, \yii\web\View::POS_READY);
?>

<?php
// массив списков
$arrayTenderList = TenderLists::find()->select('id, description, type')->orderBy('type, id')->asArray()->all();

$arrLists = [];
$oldType = -1;
$tmpArray = [];

for ($i = 0; $i < count($arrayTenderList); $i++) {

    if($arrayTenderList[$i]['type'] == $oldType) {

        $index = $arrayTenderList[$i]['id'];
        $tmpArray[$index] = $arrayTenderList[$i]['description'];

    } else {

        if($i > 0) {

            $arrLists[$oldType] = $tmpArray;
            $tmpArray = [];

            $oldType = $arrayTenderList[$i]['type'];

            $index = $arrayTenderList[$i]['id'];
            $tmpArray[$index] = $arrayTenderList[$i]['description'];

        } else {
            $oldType = $arrayTenderList[$i]['type'];
            $tmpArray = [];

            $index = $arrayTenderList[$i]['id'];
            $tmpArray[$index] = $arrayTenderList[$i]['description'];
        }
    }

    if(($i + 1) == count($arrayTenderList)) {
        $arrLists[$oldType] = $tmpArray;
    }

}
//

$GLOBALS['arrLists'] = $arrLists;

//Выбор периода
$GLOBALS['dateFrom'] = $searchModel->dateFrom;
$GLOBALS['dateTo'] = $searchModel->dateTo;

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
$periodForm .= Html::dropDownList('period', $period, \common\models\Tender::$periodList, [
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

$columns = [
[
'attribute' => 'companyname',
'content' => function ($data) {

    if ($data->companyname) {
        return $data->companyname;
    } else {
        return '-';
    }
},
'group' => true,
'groupedRow' => true,
'groupOddCssClass' => 'kv-group-header',
'groupEvenCssClass' => 'kv-group-header',
],
    [
                    'header' => '№',
                    'vAlign'=>'middle',
                    'class' => 'kartik\grid\SerialColumn'
                ],
                [
                    'attribute' => 'id',
                    'format' => 'raw',
                    'vAlign'=>'middle',
                ],
                [
                    'attribute' => 'purchase_status',
                    'header' => 'Статус<br />закупки',
                    'filter' => Html::activeDropDownList($searchModel, 'purchase_status', isset($GLOBALS['arrLists'][0]) ? $GLOBALS['arrLists'][0] : [], ['class' => 'form-control', 'prompt' => 'Все статусы']),
                    'format' => 'raw',
                    'pageSummary' => 'Всего',
                    'vAlign'=>'middle',
                    'value' => function ($data) {

                        if ($data->purchase_status) {
                            return isset($GLOBALS['arrLists'][0][$data->purchase_status]) ? $GLOBALS['arrLists'][0][$data->purchase_status] : '-';
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'attribute' => 'user_id',
                    'header' => 'Сотрудник',
                    'filter' => Html::activeDropDownList($searchModel, 'user_id', isset($GLOBALS['arrLists'][1]) ? $GLOBALS['arrLists'][1] : [], ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'value' => function ($data) {

                        $arrUserTend = explode(', ', $data->user_id);
                        $UserTendText = '';

                        if (count($arrUserTend) > 1) {

                            for ($i = 0; $i < count($arrUserTend); $i++) {

                                $index = $arrUserTend[$i];

                                if(isset($GLOBALS['arrLists'][1][$index]) ? $GLOBALS['arrLists'][1][$index] : '-') {
                                    $UserTendText .= (isset($GLOBALS['arrLists'][1][$index]) ? $GLOBALS['arrLists'][1][$index] : '-') . '<br />';
                                }
                            }

                        } else {

                            try {
                                if(isset($GLOBALS['arrLists'][1]) ? $GLOBALS['arrLists'][1][$data->user_id] : '-') {
                                    $UserTendText = isset($GLOBALS['arrLists'][1]) ? $GLOBALS['arrLists'][1][$data->user_id] : '-';
                                }
                            } catch (\Exception $e) {
                                $UserTendText = '-';
                            }

                        }

                        return $UserTendText;
                    },
                ],
                [
                    'attribute' => 'date_request_end',
                    'vAlign'=>'middle',
                    'header' => 'Окончание подачи<br /> заявки',
                    'filter' => false,
                    'value' => function ($data) {

                        if ($data->date_request_end) {
                            return date('d.m.Y', $data->date_request_end);
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'time_bidding_end',
                    'vAlign'=>'middle',
                    'header' => 'Дата и время<br /> подведения итогов',
                    'filter' => false,
                    'value' => function ($data) {

                        if ($data->time_bidding_end) {
                            return date('d.m.Y', $data->time_bidding_end);
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'method_purchase',
                    'header' => 'Способ закупки',
                    'filter' => Html::activeDropDownList($searchModel, 'method_purchase', isset($GLOBALS['arrLists'][2]) ? $GLOBALS['arrLists'][2] : [], ['class' => 'form-control', 'prompt' => 'Все способы']),
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'value' => function ($data) {

                        $arrMethodsTend = explode(', ', $data->method_purchase);
                        $methodsText = '';

                        if (count($arrMethodsTend) > 1) {

                            for ($i = 0; $i < count($arrMethodsTend); $i++) {

                                $index = $arrMethodsTend[$i];

                                if(isset($GLOBALS['arrLists'][2][$index]) ? $GLOBALS['arrLists'][2][$index] : '-') {
                                    $methodsText .= (isset($GLOBALS['arrLists'][2][$index]) ? $GLOBALS['arrLists'][2][$index] : '-') . '<br />';
                            }
                            }
                        } else {

                            try {
                                if(isset($GLOBALS['arrLists'][2]) ? $GLOBALS['arrLists'][2][$data->method_purchase] : '-') {
                                    $methodsText = isset($GLOBALS['arrLists'][2]) ? $GLOBALS['arrLists'][2][$data->method_purchase] : '-';
                                }
                            } catch (\Exception $e) {
                                $methodsText = '-';
                            }

                        }
                        return $methodsText;

                    },
                ],
                [
                    'attribute' => 'city',
                    'vAlign'=>'middle',
                    'header' => 'Город, <br />Область поставки',
                    'value' => function ($data) {

                        if ($data->city) {
                            return $data->city;
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'service_type',
                    'header' => 'Закупаемые<br />услуги',
                    'filter' => Html::activeDropDownList($searchModel, 'service_type', isset($GLOBALS['arrLists'][3]) ? $GLOBALS['arrLists'][3] : [], ['class' => 'form-control', 'prompt' => 'Все услуги']),
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'value' => function ($data) {

                        $arrServices = explode(', ', $data->service_type);
                        $serviceText = '';

                        if (count($arrServices) > 1) {

                            for ($i = 0; $i < count($arrServices); $i++) {

                                $index = $arrServices[$i];

                                if(isset($GLOBALS['arrLists'][3][$index]) ? $GLOBALS['arrLists'][3][$index] : '-') {
                                    $serviceText .= (isset($GLOBALS['arrLists'][3][$index]) ? $GLOBALS['arrLists'][3][$index] : '-') . '<br />';
                                }
                            }

                        } else {

                            try {
                                if(isset($GLOBALS['arrLists'][3]) ? $GLOBALS['arrLists'][3][$data->service_type] : '-') {
                                    $serviceText = isset($GLOBALS['arrLists'][3]) ? $GLOBALS['arrLists'][3][$data->service_type] : '-';
                                }
                            } catch (\Exception $e) {
                                $serviceText = '-';
                            }

                        }

                        return $serviceText;

                    },
                ],
                [
                    'attribute' => 'price_nds',
                    'vAlign'=>'middle',
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_SUM,
                    'header' => 'Максимальная<br /> стоимость закупки',
                    'value' => function ($data) {

                        if ($data->price_nds) {
                            return $data->price_nds;
                        } else {
                            return '-';
                        }

                    },
                ],
    [
                    'class' => 'kartik\grid\ActionColumn',
                    'header' => 'Действие',
                    'vAlign'=>'middle',
                    'template' => '{update}',
                    'contentOptions' => ['style' => 'min-width: 60px'],
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                ['/company/fulltender', 'tender_id' => $model->id]);
                        },
                    ],
],
];
echo GridView::widget([
'dataProvider' => $dataProvider,
'filterModel' => $searchModel,
'summary' => false,
'showPageSummary' => true,
'emptyText' => '',
'panel' => [
'type' => 'primary',
'heading' => 'Все закупки',
'before' => false,
'footer' => false,
'after' => false,
],
 'rowOptions' => function ($model) {

    // Выделяем цветом для каких типов
     if(isset($model->purchase_status)) {
         if ($model->purchase_status == 0) {
             return '';
         } else if (($model->purchase_status) == 16 || ($model->purchase_status == 20)) {
             return ['style' => 'background: #e6e6e6;'];
         } else if (($model->purchase_status) == 17 || ($model->purchase_status == 23)) {
             return ['style' => 'background: #ffd5d5;'];
         } else if ($model->purchase_status == 19) {
             return ['style' => 'background: #add9ff;'];
         } else if ($model->purchase_status == 21) {
             return ['style' => 'background: #fffc98;'];
         } else if ($model->purchase_status == 22) {
             return ['style' => 'background: #d9ffd8;'];
         } else if ($model->purchase_status == 57) {
             return ['style' => 'background: #f3dcf3;'];
         } else if ($model->purchase_status == 58) {
             return ['style' => 'background: #ffe7c6;'];
         } else {
             return '';
         }
     } else {
         return '';
     }
    },
'resizableColumns' => false,
'hover' => false,
'striped' => false,
'export' => false,

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

