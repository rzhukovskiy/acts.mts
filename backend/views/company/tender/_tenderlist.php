<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use common\models\Company;
use kartik\date\DatePicker;
use common\models\TenderLists;
use common\models\TenderOwner;

$script = <<< JS
// формат числа
window.onload=function(){
  var formatSum = $('td[data-col-seq="11"]');
  $(formatSum).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
var thisuser = '';
var user4 = $('td[data-col-seq="4"]');
  $(user4).each(function (id, value) {
       thisuser += $(this).text();
       });
   $('.userDen').text((thisuser.match(/Денис Митрофанов/g) || []).length);
   $('.userAlyna').text((thisuser.match(/Алена Попова/g) || []).length);
   $('.userMasha').text((thisuser.match(/Мария Губарева/g) || []).length);
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

// подсчет количества тендеров в работе
$userDen = TenderOwner::find()->Where(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['is', 'reason_not_take', null]])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['is', 'reason_not_take', null]])->andWhere(['tender_user' => 256])->count();
$userAlyona = TenderOwner::find()->Where(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['is', 'reason_not_take', null]])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['is', 'reason_not_take', null]])->andWhere(['tender_user' => 654])->count();
$userMasha = TenderOwner::find()->Where(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['is', 'reason_not_take', null]])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['is', 'reason_not_take', null]])->andWhere(['tender_user' => 756])->count();
// конец подсчет количества тендеров в работе

$GLOBALS['arrLists'] = $arrLists;
$GLOBALS['usersList'] = $usersList;

//Выбор периода
$GLOBALS['dateFrom'] = $searchModel->dateFrom;
$GLOBALS['dateTo'] = $searchModel->dateTo;

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
                    'filter' => Html::activeDropDownList($searchModel, 'user_id', $usersList, ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'value' => function ($data) {


                        if (isset($GLOBALS['usersList'][$data->user_id])) {
                            return $GLOBALS['usersList'][$data->user_id];
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'date_request_end',
                    'vAlign'=>'middle',
                    'header' => 'Окончание подачи<br /> заявки',
                    'filter' => false,
                    'value' => function ($data) {

                        if ($data->date_request_end) {
                            return date('d.m.Y H:i', $data->date_request_end);
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'time_bidding_start',
                    'vAlign'=>'middle',
                    'header' => 'Начало торгов',
                    'filter' => false,
                    'value' => function ($data) {

                        if ($data->time_bidding_start) {
                            return date('d.m.Y', $data->time_bidding_start);
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

$statTable = '';
$statTable .= '<table width="100%" border="1" bordercolor="#dddddd" style="margin: 15px 0px 15px 0px;">
                <tr style="background: #428bca; color: #fff;">
                    <td colspan="3" style="padding: 3px 5px 3px 5px; font-weight: normal;" align="center">Статистика сотрудников</td>
                </tr>';
$statTable .=  '</td>
                </tr>
                <tr style="background: #fff; font-weight: normal;">
                    <td style="padding: 3px 5px 3px 5px">Денис Митрофанов</td>
                    <td class="userDen" width="300px;" style="padding: 3px 5px 3px 5px"></td>
                    <td width="50px" align="center" style="background:#fff; padding:7px 6px 5px 0px;">';

$statTable .= Html::a("<span class=\"glyphicon glyphicon-search\"></span>", "/company/tenderlist?TenderSearch[user_id]=" . 256);

$statTable .=  '<tr style="background: #fff; font-weight: normal;">
                    <td style="padding: 3px 5px 3px 5px">Алена Попова</td>
                    <td class="userAlyna" width="300px;" style="padding: 3px 5px 3px 5px"></td>
                    <td width="50px" align="center" style="background:#fff; padding:7px 6px 5px 0px;">';

$statTable .= Html::a("<span class=\"glyphicon glyphicon-search\"></span>", "/company/tenderlist?TenderSearch[user_id]=" . 654);

$statTable .= '</td>
                </tr>
                <tr style="background: #fff; font-weight: normal;">
                    <td style="padding: 3px 5px 3px 5px">Мария Губарева</td>
                    <td class="userMasha" width="300px;" style="padding: 3px 5px 3px 5px"></td>
                    <td width="50px" align="center" style="background:#fff; padding:7px 6px 5px 0px;">';

$statTable .= Html::a("<span class=\"glyphicon glyphicon-search\"></span>",  "/company/tenderlist?TenderSearch[user_id]=" . 756);

$statTable .= '</td>
                </tr>
            </table>';
$statTable .= '<table width="100%" border="1" bordercolor="#dddddd" style="margin: 15px 0px 15px 0px;">
                <tr style="background: #428bca; color: #fff;">
                    <td colspan="3" style="padding: 3px 5px 3px 5px; font-weight: normal;" align="center">В работе</td>
                </tr>';
$statTable .=  '</td>
                </tr>
                <tr style="background: #fff; font-weight: normal;">
                    <td style="padding: 3px 5px 3px 5px">Денис Митрофанов</td>
                    <td width="350px;" style="padding: 3px 5px 3px 5px">' . $userDen .'</td>
                    </tr>';

$statTable .=  '<tr style="background: #fff; font-weight: normal;">
                    <td style="padding: 3px 5px 3px 5px">Алена Попова</td>
                    <td width="350px;" style="padding: 3px 5px 3px 5px">' . $userAlyona .'</td>
                    </tr>';

$statTable .= '<tr style="background: #fff; font-weight: normal;">
                    <td style="padding: 3px 5px 3px 5px">Мария Губарева</td>
                    <td width="350px;" style="padding: 3px 5px 3px 5px">' . $userMasha .'</td>
                    </tr></table>';


echo GridView::widget([
'dataProvider' => $dataProvider,
'filterModel' => $searchModel,
'summary' => false,
'showPageSummary' => true,
'emptyText' => '',
'panel' => [
'type' => 'primary',
'heading' => 'Закупки',
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
         } else if ($model->purchase_status == 85) {
             return ['style' => 'background: #b3e6d7;'];
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
                'content' => $statTable,
                'options' => [
                    'colspan' => count($columns),
                ]
            ]
        ],
        'options' => ['class' => 'kv-grid-group-filter'],
    ],
],
'columns' => $columns,
]);
?>

