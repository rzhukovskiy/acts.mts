<?php

use yii\helpers\Html;
use common\models\DepartmentCompany;
use kartik\grid\GridView;
use common\assets\CanvasJs\CanvasJsAsset;

CanvasJsAsset::register($this);

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $this->title ?>
    </div>
    <div class="panel-body">

        <?php

        $GLOBALS['authorMembers'] = $authorMembers;
        $GLOBALS['type'] = $type;
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

        if((Yii::$app->controller->action->id == 'new') || (Yii::$app->controller->action->id == 'archive')) {

            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'hover' => false,
                'striped' => false,
                'export' => false,
                'summary' => false,
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
                                    'colspan' => 3,
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
                                    'colspan' => 3,
                                ]
                            ]
                        ],
                        'options' => ['class' => 'kv-group-header'],
                    ],
                ],
                'columns' => [
                    [
                        'attribute' => (Yii::$app->controller->action->id == 'new' ? 'user_id' : 'remove_id'),
                        'contentOptions' => ['class' => 'value_0', 'style' => 'min-width: 300px'],
                        'value' => function ($data) {
                            if(Yii::$app->controller->action->id == 'new') {
                                return $GLOBALS['authorMembers'][$data->user_id];
                            } else {
                                return $GLOBALS['authorMembers'][$data->remove_id];
                            }
                        },
                    ],
                    [
                        'attribute' => 'companyNum',
                        'header' => ((Yii::$app->controller->action->id == 'new') ? 'Количество' : 'Перенесено в архив'),
                        'contentOptions' => ['class' => 'value_1', 'style' => 'width: 300px'],
                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'template' => '{view}',
                        'contentOptions' => ['style' => 'width: 120px'],
                        'buttons' => [
                            'view' => function ($url, $model, $key) {

                                $toId = 0;

                                if(Yii::$app->controller->action->id == 'new') {
                                    $toId = $model->user_id;
                                } else {
                                    $toId = $model->remove_id;
                                }

                                return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                    ['/activity/show' . Yii::$app->controller->action->id, 'user_id' => $toId, 'type' => $GLOBALS['type'],
                                        'DepartmentCompanySearch[dateFrom]' => $GLOBALS['dateFrom'],
                                        'DepartmentCompanySearch[dateTo]' => $GLOBALS['dateTo']]);
                            },
                        ],
                    ],
                ],
            ]);
        } elseif(Yii::$app->controller->action->id == 'shownew') {

            $GLOBALS['name'] = '';

            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'hover' => false,
                'striped' => false,
                'export' => false,
                'summary' => false,
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
                                    'colspan' => 4,
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
                                    'colspan' => 4,
                                ]
                            ]
                        ],
                        'options' => ['class' => 'kv-group-header'],
                    ],
                ],
                'columns' => [
                    [
                        'attribute' => 'user_id',
                        'group' => true,
                        'groupedRow' => true,
                        'groupOddCssClass' => 'kv-group-header',
                        'groupEvenCssClass' => 'kv-group-header',
                        'value' => function ($data) {
                            $GLOBALS['name'] = $GLOBALS['authorMembers'][$data->user_id];
                            return $GLOBALS['authorMembers'][$data->user_id];
                        },
                    ],
                    [
                        'header' => '№',
                        'class' => 'kartik\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'company_id',
                        'value' => function ($data) {
                            return $data->company->name;
                        },
                    ],
                    [
                        'header' => 'Дата создания',
                        'contentOptions' => ['class' => 'value_0'],
                        'value' => function ($data) {
                            return date('d.m.Y', $data->company->created_at);
                        },
                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'template' => '{view}',
                        'contentOptions' => ['style' => 'min-width: 80px'],
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                    ['/company/state', 'id' => $model->company_id]);
                            },
                        ],
                    ],
                ],
            ]);

        } elseif(Yii::$app->controller->action->id == 'showarchive') {

            $GLOBALS['name'] = '';

            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'hover' => false,
                'striped' => false,
                'export' => false,
                'summary' => false,
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
                                    'colspan' => 6,
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
                                    'colspan' => 6,
                                ]
                            ]
                        ],
                        'options' => ['class' => 'kv-group-header'],
                    ],
                ],
                'columns' => [
                    [
                        'attribute' => 'remove_id',
                        'group' => true,
                        'groupedRow' => true,
                        'groupOddCssClass' => 'kv-group-header',
                        'groupEvenCssClass' => 'kv-group-header',
                        'value' => function ($data) {
                            return $GLOBALS['authorMembers'][$data->remove_id];
                        },
                    ],
                    [
                        'header' => '№',
                        'class' => 'kartik\grid\SerialColumn'
                    ],
                    [
                        'attribute' => 'company_id',
                        'value' => function ($data) {
                            return $data->company->name;
                        },
                    ],
                    [
                        'header' => 'Дата создания',
                        'value' => function ($data) {
                            return date('d.m.Y', $data->company->created_at);
                        },
                    ],
                    [
                        'header' => 'Дата переноса',
                        'contentOptions' => ['class' => 'value_0'],
                        'value' => function ($data) {
                            return date('d.m.Y', $data->remove_date);
                        },
                    ],
                    [
                        'header' => 'Потрачено времени',
                        'format' => 'raw',
                        'value' => function ($data) {

                            $lostDateText = '';
                            $lostDate = $data->remove_date - $data->company->created_at;

                            $days = (round($lostDate / 86400));
                            $lostDate -= (round($lostDate / 86400) * 86400);

                            if($days < 0) {
                                $days = 0;
                            }

                            $hours = (round($lostDate / 3600));
                            $lostDate -= (round($lostDate / 3600) * 3600);

                            if($hours < 0) {
                                $hours = 0;
                            }

                            $minutes = (round($lostDate / 60));

                            if($minutes < 0) {
                                $minutes = 0;
                            }

                            $lostDateText .= 'Дней: ' . $days;
                            $lostDateText .= ', часов: ' . $hours;
                            $lostDateText .= ', минут: ' . $minutes;

                            return $lostDateText;
                        },
                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'template' => '{view}',
                        'contentOptions' => ['style' => 'min-width: 80px'],
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                    ['/company/state', 'id' => $model->company_id]);
                            },
                        ],
                    ],
                ],
            ]);

        }

        ?>

    </div>

</div>

<?php

echo "<div class=\"grid-view hide-resize\"><div class=\"panel panel-primary\" style='padding: 10px;'><div id=\"chart_div\" style=\"width:100%;height:500px;\"></div></div></div>";

$js = "";

if((Yii::$app->controller->action->id == 'new') || (Yii::$app->controller->action->id == 'archive')) {
    $js = "
            var dataTable = [];
            console.log('Hello');
            
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
} else {
    $js = "CanvasJS.addColorSet('blue', ['#428bca']);
                var dataTable = [];
                var labelArr = [];
                var vallabelArr = [];
                var max = 0;
                var index = 0;
                $('.table tbody tr').each(function (id, value) {
                if($(this).find('.value_0').text() != '') {
                
                /*var arrLable = $(this).find('.value_0').text().split('.');
                arrLable = arrLable[1] + '.' + arrLable[2];*/
                var arrLable = $(this).find('.value_0').text();
                
                var checkHave = false;
                for(var i = 0; i < labelArr.length; i++) {
                if(labelArr[i] == arrLable) {
                vallabelArr[i] += 1;
                checkHave = true;
                }
                }
                    
                if(checkHave == false) {
                labelArr[index] = arrLable;
                vallabelArr[index] = 1;
                }
                
                index++;
                    
                }
            });
                
                for(var i = 0; i < labelArr.length; i++) {
                if((labelArr[i] != undefined) && (vallabelArr[i] != undefined)) {
                
                if (vallabelArr[i] > max) {
                    max = vallabelArr[i];
                }
                
                dataTable.push({
                    label: labelArr[i],
                    y: vallabelArr[i],
                });
                
                }
                }
                
                var options = {
                    colorSet: 'blue',
                    dataPointMaxWidth: 40,
                    title: {
                        text: 'График - ' + '" . $GLOBALS['name'] . "',
                        fontColor: '#069',
                        fontSize: 22
                    },
                    subtitles: [
                        {
                            text: 'По дням',
                            horizontalAlign: 'left',
                            fontSize: 14,
                            fontColor: '#069',
                            margin: 20
                        }
                    ],
                    data: [
                        {
                            type: 'column', //change it to line, area, bar, pie, etc
                            dataPoints: dataTable
                        }
                    ],
                    axisX: {
                        title: 'Дни',
                        titleFontSize: 14,
                        titleFontColor: '#069',
                        titleFontWeight: 'bol',
                        labelFontColor: '#069',
                        labelFontWeight: 'bold',
                        interval: 1,
                        lineThickness: 1,
                        labelFontSize: 14,
                        lineColor: 'black'
                    },

                    axisY: {
                        labelFontColor: '#069',
                        labelFontWeight: 'bold',
                        tickThickness: 1,
                        gridThickness: 1,
                        lineThickness: 1,
                        labelFontSize: 14,
                        lineColor: 'black',
                        valueFormatString: '### ### ###.#',
                        maximum: max + 0.1 * max
                    }
                };

                $('#chart_div').CanvasJSChart(options);
                ";
}

$this->registerJs($js);

?>