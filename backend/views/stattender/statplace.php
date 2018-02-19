<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use common\assets\CanvasJs\CanvasJsAsset;
use common\models\Tender;

CanvasJsAsset::register($this);

$this->title = 'Статистика электронных площадок';

$GLOBALS['namePlace'] = $namePlace;
$GLOBALS['type'] = $type;
if ($type == 1) {
    $name = 'Победные';
} else if ($type == 2) {
    $name = 'Проигранные';
} else if ($type == 3) {
    $name = 'Отклоненные';
} else {
    $name = 'Общее';
}
$GLOBALS['name'] = $name;

if (Yii::$app->controller->action->id == 'statplace') {
    echo Tabs::widget([
        'items' => [
            ['label' => 'Победные', 'url' => ['/company/statplace', 'type' => 1], 'active' => $type == 1],
            ['label' => 'Проигранные', 'url' => ['/company/statplace', 'type' => 2], 'active' => $type == 2],
            ['label' => 'Отклоненные', 'url' => ['/company/statplace', 'type' => 3], 'active' => $type == 3],
            ['label' => 'Общее', 'url' => ['/company/statplace', 'type' => 4], 'active' => $type == 4],
        ],
    ]);
} else {
    echo Tabs::widget([
        'items' => [
            ['label' => $name, 'url' => ['/company/statplace', 'type' => $type]],
            ['label' => 'Подробная статистика', 'active' => Yii::$app->controller->action->id == 'showstatplace'],
        ],
    ]);
}

if (Yii::$app->controller->action->id == 'statplace') {
$column = [

    [
        'header' => '№',
        'vAlign'=>'middle',
        'class' => 'kartik\grid\SerialColumn'
    ],
    [
        'attribute' => 'site_address',
        'filter' => false,
        'vAlign'=>'middle',
        'contentOptions' => ['class' => 'value_0', 'style' => 'min-width: 300px'],
        'value' => function ($data) {

            if (isset($GLOBALS['namePlace'][$data->site_address])) {
                return $GLOBALS['namePlace'][$data->site_address];
            } else {
                return '-';
            }

        },
    ],
    [
        'header' => 'Количество',
        'vAlign'=>'middle',
        'pageSummary' => true,
        'pageSummaryFunc' => GridView::F_SUM,
        'contentOptions' => ['class' => 'value_1', 'style' => 'width: 300px'],
        'value' => function ($data) {
            if ($data->link) {
                return $data->link;
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
            'update' => function ($url, $data, $key) {
                return Html::a('<span class="glyphicon glyphicon-search"></span>',
                    ['/company/showstatplace', 'site_address' => $data->site_address, 'type' => $GLOBALS['type'],
                        'TenderSearch[dateFrom]' => $GLOBALS['dateFrom'],
                        'TenderSearch[dateTo]' => $GLOBALS['dateTo']]);
            },
        ],
    ],
];
} else {
    $column = [
        [
            'header' => $name,
            'group' => true,
            'groupedRow' => true,
            'groupOddCssClass' => 'kv-group-header',
            'groupEvenCssClass' => 'kv-group-header',
            'value' => function ($data) {
                return $GLOBALS['name'];
            },
        ],
        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'attribute' => 'customer',
            'filter' => false,
            'vAlign'=>'middle',
            'value' => function ($data) {

                if ($data->customer) {
                    return $data->customer;
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
                'update' => function ($url, $data, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/company/fulltender', 'tender_id' => $data->id]);
                },
            ],
        ],
    ];
}

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Статистика электронных площадок
    </div>
    <div class="panel-body">
        <?php

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
        $periodForm .= Html::dropDownList('period', $period, Tender::$periodList, [
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

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'showPageSummary' => true,
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
                                'colspan' => count($column),
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
                                'colspan' => count($column),
                            ]
                        ]
                    ],
                    'options' => ['class' => 'kv-group-header'],
                ],
            ],
            'columns' => $column,
        ]);
        ?>
    </div>
</div>

<?php
if (Yii::$app->controller->action->id == 'statplace') {
    echo "<div class=\"grid-view hide-resize\"><div class=\"panel panel-primary\" style='padding: 10px;'><div id=\"chart_div\" style=\"width:100%;height:500px;\"></div></div></div>";


    $js = "
            var dataTable = [];
            
            var idArr = [];
            var valArr = [];
            var idComp = [];
            var summComp = [];
            var index = 0;
            
            var labelArr = [];
            var vallabelArr = [];
            
            $('.table tbody tr').each(function (id, value) {
            if($(this).find('.value_0').text() != '') {

                    valArr[index] = parseInt($(this).find('.value_1').text().replace(/\s+/g, '').replace(',', ''));
                    idArr[index] = $(this).find('.value_2');
                    idComp[index] = $(this).find('.value_2').text();
                    
                    var indexIdComp = parseInt($(this).find('.value_2').text().replace(/\s+/g, '').replace(',', ''));
                    
                    if(summComp[indexIdComp] > 0) {
                    summComp[indexIdComp] += parseInt($(this).find('.value_1').text().replace(/\s+/g, '').replace(',', ''));
                    } else {
                    summComp[indexIdComp] = parseInt($(this).find('.value_1').text().replace(/\s+/g, '').replace(',', ''));
                    }
                    
                    var checkHave = false;
                    for(var i = 0; i < labelArr.length; i++) {
                    if(labelArr[i] == $(this).find('.value_0').text()) {
                    vallabelArr[i] += parseInt($(this).find('.value_1').text().replace(/\s+/g, '').replace(',', ''));
                    checkHave = true;
                    }
                    }
                    
                    if(checkHave == false) {
                    labelArr[index] = $(this).find('.value_0').text();
                    vallabelArr[index] = parseInt($(this).find('.value_1').text().replace(/\s+/g, '').replace(',', ''));
                    }
                    
                    indexIdComp = 0;
                    index++;
                    
                }
            });
            
            for(var i = 0; i < labelArr.length; i++) {
            if((labelArr[i] != undefined) && (vallabelArr[i] != undefined)) {
                dataTable.push({
                    label: labelArr[i],
                    y: vallabelArr[i],
                });
                }
            }
            
            console.log(dataTable);
            var options = {
                title: {
                    text: 'График',
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
            }; $('#chart_div').CanvasJSChart(options);
            
            var itemVal = 0;
            var lastVal = 0;
            for (var i = 0; i < index; i++) {
            
            var indexIdComp = idComp[i];
            
            itemVal = valArr[i] / (summComp[indexIdComp] / 100);
            
            if(itemVal == 100) {
            idArr[i].text(100);
            } else if(itemVal == 0) {
            idArr[i].text(0);
            } else {
            idArr[i].text(itemVal.toFixed(2));
            }
            
            itemVal = 0;
            indexIdComp = 0;
            
            }";
    $this->registerJs($js);
}


?>
