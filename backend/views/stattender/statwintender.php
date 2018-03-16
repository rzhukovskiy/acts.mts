<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use common\assets\CanvasJs\CanvasJsAsset;
use common\models\Tender;
use yii\helpers\Url;
use yii\bootstrap\Modal;

CanvasJsAsset::register($this);

$this->title = 'Статистика тендеров';

$actionLinkCompare = Url::to('@web/company/comparewintender');
$nowMonth = date('n', strtotime("-1 month"));
$nowYear = date('Y', time());

if (Yii::$app->controller->action->id == 'statwintender') {
    $script = <<< JS
// формат числа
window.onload=function(){
  var formatSum2a = $('.kv-page-summary-container td:eq(2)');
  var formatSum3a = $('.kv-page-summary-container td:eq(3)');
  var persent = 0;
  var formatSum2 = $('td[data-col-seq="2"]');
  $(formatSum2).each(function (id, value) {
       var thisId = $(this);
       
       persent = thisId.text()/parseInt(formatSum2a.text())*100;
       thisId.parent('tr').find('td[data-col-seq="3"]').text(persent.toFixed(2) + ' %');
       persent = 0;

       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});

formatSum3a.text((formatSum2a.text()/parseInt(formatSum2a.text())*100).toFixed(2) + ' %');
};


JS;
    $this->registerJs($script, \yii\web\View::POS_READY);
}

$script = <<< JS
// сравнения
var arrMonth = [];
var arrYear = [];
var arrDay = '';
var arrDayCount = [];
var arrMonthYears = [];

// открываем модальное окно сравнения по месяцу
$('.compare').on('click', function() {
    $('#showListsName').modal('show');
    // убираем галочки
    $('input[type="checkbox"]').removeAttr('checked');
    $('input[type="checkbox"][value="$nowMonth"]').prop('checked','checked');
    
    //сбрасываем селектор
    arrMonthYears = [];
    arrMonth = [];
    var now = new Date();
    var yearM = now.getFullYear();
    $('.yearMonth').val(yearM);
    
});

// Нажимаем на кнопку сравнить В месяцах

$('.addNewItem').on('click', function() {
    
    arrMonth = [];
    $('#showListsName').modal('hide');
    
        var selectMonth = 1;
    
       $('.monthList').each(function (value) {
      if ($(this).is(':checked')) {
          arrMonth.push($(this).val());
          
          if($("#yearOnMonth[data-month='" + selectMonth + "']") != "undefined" && $("#yearOnMonth[data-month='" + selectMonth + "']") !== null) {  
          arrMonthYears.push($("#yearOnMonth[data-month='" + selectMonth + "']").val());
          }
          
     }
     selectMonth++;
       
});
      sendCompare();
    $('#showSettingsList').modal('show');
});                                              

function sendCompare() {
          $.ajax({
         
                type     :'POST',
                cache    : true,
                data: 'arrMonth=' + JSON.stringify(arrMonth) + '&arrMonthYears=' + JSON.stringify(arrMonthYears),
                url  : '$actionLinkCompare',
                success  : function(data) {
                var resTables = "";
                var reswash = [];
                var resall = "";
                var arrKey = [];
                var response = $.parseJSON(data);
               
                var countServe = '';
                
                var month = [];
                month['1'] = "Январь";
                month['2'] = "Февраль";
                month['3'] = "Март";
                month['4'] = "Апрель";
                month['5'] = "Май";
                month['6'] = "Июнь";
                month['7'] = "Июль";
                month['8'] = "Август";
                month['9'] = "Сентябрь";
                month['10'] = "Октябрь";
                month['11'] = "Ноябрь";
                month['12'] = "Декабрь";
                               
                var today = new Date();
                var yr = today.getFullYear();
                var year = [];
                var i = 0;
                year[yr] = yr;
                for (i = 10; i > 0; i--) {
                year[yr-i] = yr - i;
                }
                
                var oldvalue = [];
                oldvalue[2] = [];
                oldvalue[2]['1'] = '';
                oldvalue[6] = [];
                oldvalue[6]['1'] = '';
                
                var splitFloat = "";
                var splitInt = "";
                
               var sumArr = [];
      
                if (response.success == 'true') {
                  
                // Удачно
                var arr = $.parseJSON(response.result);
                 i = 0;
                 $.each(arr,function(key) {
                     arrKey[i] = key;
                     i++;
                 });
                 
                $.each(arr,function(key,data) {
                   
                 $.each(data, function(index,value) {
                    
                        if(!sumArr[index]) {
                            sumArr[index] = [];
                        }
                     
                        if(!sumArr[index]['countServe']) {
                            sumArr[index]['countServe'] = 0;
                        }
                        
                        sumArr[index]['countServe'] += parseFloat(value['countServe']);
                        sumArr[index]['served_at'] = value['served_at'];
                     i = 0;
                     $.each(arrKey, function(nomber,id) {
                    
                     if (key == id) {
                        
                        if(oldvalue[2]['1'] != '') {
                        if (oldvalue[2]['1'] > parseFloat(value['countServe'])) {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['countServe'] - oldvalue[2]['1'])/value['countServe']*100).toFixed(1)) + '%</span>';
                        } else {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['countServe'] - oldvalue[2]['1'])/oldvalue[2]['1']*100).toFixed(1))  + '%</span>';
                        }
                        } else {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
                        }
                       
                        oldvalue[2]['1'] = parseFloat(value['countServe']);
                        
                        var dateShow = new Date(parseInt(value['served_at']) * 1000);
                        
                           reswash[id].push("<tr><td>" + month[index] + ' ' + dateShow.getFullYear() + "</td><td>" + countServe + "</td></tr>");
                       
                     }
                     i++;
                     });
                     
                            });
                 

                 
                    });
                
                console.log(reswash);
                     resTables += "<table border='1' width='100%' bordercolor='#dddddd'><tr height='25px'><td colspan='5' align='center' style='color: #000000;'>" + '123' + "</td></tr><tr height='25px' style='background:#dff0d8;'><td style='width:300px;'>Месяц</td><td>Количество</td></tr>" + reswash + "</table></br>";

                        $.each(sumArr, function(index,value) {
                       
                         if (typeof sumArr[index] !== 'undefined' && sumArr[index] !== null) {
                         
                         if(oldvalue[6]['1'] != '') {
                        if (oldvalue[6]['1'] > parseFloat(value['countServe'])) {
                           countServe = value['countServe'].toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['countServe'] - oldvalue[6]['1'])/value['countServe']*100).toFixed(1)) + '%</span>';
                        } else {
                           countServe = value['countServe'].toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' + Math.abs(((value['countServe'] - oldvalue[6]['1'])/value['countServe']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                           countServe = value['countServe'].toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
                        }
                        
                        oldvalue[6]['1'] = parseFloat(value['countServe']);
                        
                        var dateShow = new Date(parseInt(value['served_at']) * 1000);
                        
                            resall += "<tr><td>" + month[index] + ' ' + dateShow.getFullYear() + "</td><td>" + countServe + "</td></tr>";
                        
                         }
                         });
                        
                    var nameColomn = "";
                    nameColomn = 'Месяц';
                    
                   
                   
                     if (resall.length > 0) {
                     resTables += "<table border='1' width='100%' bordercolor='#dddddd'><tr height='25px'><td colspan='5' align='center' style='color: #000000;'>Общая</td></tr><tr height='25px' style='background:#dff0d8;'><td style='width:300px;'>" + nameColomn + "</td><td>Количество</td></tr>" + resall +"</table></br>";
                     }
                
                    $('.place_list').html(resTables);
                    
                } else {
                // Неудачно
                $('.place_list').html();
                }
                
                }
                });
}
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

$GLOBALS['userList'] = $userList;
$GLOBALS['type'] = $type;

if ($type == 1) {
    $name = 'Выигранные';
    $nameTitle = 'Статистика выигранных тендеров';
} else if ($type == 2) {
    $name = 'Проигранные';
    $nameTitle = 'Статистика проигранных тендеров';
} else {
    $name = 'Общее';
    $nameTitle = 'Статистика всех тендеров';
}
$GLOBALS['name'] = $name;

if (Yii::$app->controller->action->id == 'statwintender') {
    echo Tabs::widget([
        'items' => [
            ['label' => 'Выигранные', 'url' => ['/company/statwintender', 'type' => 1], 'active' => $type == 1],
            ['label' => 'Проигранные', 'url' => ['/company/statwintender', 'type' => 2], 'active' => $type == 2],
            ['label' => 'Общее', 'url' => ['/company/statwintender', 'type' => 3], 'active' => $type == 3],
        ],
    ]);
} else {
    echo Tabs::widget([
        'items' => [
            ['label' => $name, 'url' => ['/company/statwintender', 'type' => $type]],
            ['label' => 'Подробная статистика', 'active' => Yii::$app->controller->action->id == 'showstatwintender'],
        ],
    ]);
}

if (Yii::$app->controller->action->id == 'statwintender') {
    $column = [

        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn'
        ],
        [
            'attribute' => 'user_id',
            'filter' => false,
            'vAlign'=>'middle',
            'contentOptions' => ['class' => 'value_0', 'style' => 'min-width: 300px'],
            'value' => function ($data) {

                if (isset($GLOBALS['userList'][$data->user_id])) {
                    return $GLOBALS['userList'][$data->user_id];
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
                if ($data->federal_law) {
                    return $data->federal_law;
                } else {
                    return '-';
                }

            },
        ],
        [
            'header' => '%',
            'vAlign'=>'middle',
            'contentOptions' => ['style' => 'min-width: 60px'],
            'value' => function ($data) {
                if ($data->comment) {
                    return $data->comment;
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
                        ['/company/showstatwintender', 'user_id' => $data->user_id, 'type' => $GLOBALS['type'],
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
                return $GLOBALS['name'] . ' ' . $GLOBALS['userList'][$data->user_id];
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
                    if ($data->id) {
                        return Html::a('<span class="glyphicon glyphicon-search"></span>',
                            ['/company/fulltender', 'tender_id' => $data->id]);
                    } else {
                        return '-';
                    }

                },
            ],
        ],
    ];
}

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <?php echo $nameTitle; ?>
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
                            'content' => $filters . '<span class="pull-right btn btn-warning btn-sm compare" style="padding: 6px 8px; margin-top: 2px; border:1px solid #c18431;">Сравнение по месяцу</span>',
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
if (Yii::$app->controller->action->id == 'statwintender') {
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

// Модальное окно месяца
$modalListsName = Modal::begin([
    'header' => '<h5>Выбор месяца</h5>',
    'id' => 'showListsName',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
    'size'=>'modal-sm',
]);

// Вывод селектора для года
$select = [];

for ($j = 1; $j <= 12; $j++) {

    $yearOnMonth = "";

    for ($i = 10; $i > 0; $i--) {
        $yearOnMonth .= "<option value='" . date('Y', strtotime("-$i year")) . "'>" . date('Y', strtotime("-$i year")) . "</option>";
    }

    $nowYearOnMonth = "<option selected value='" . date('Y', time()) . "'>" . date('Y', time()) . "</option>";
    $select[$j] = "<select id='yearOnMonth' class='yearMonth' data-month='" . $j . "'>$yearOnMonth $nowYearOnMonth</select>";

}

echo "<table><tr><td><input type='checkbox' class='monthList' value='1'> Январь </td><td>" . $select[1] . "</td></tr>" . "</td></tr>";

echo "<tr><td><input type='checkbox' class='monthList' value='2'> Февраль </td><td>" . $select[2] . "</td></tr>";

echo "<tr><td><input type='checkbox' class='monthList' value='3'> Март </td><td>" . $select[3] . "</td></tr>";

echo "<tr><td><input type='checkbox' class='monthList' value='4'> Апрель </td><td>" . $select[4] . "</td></tr>";

echo "<tr><td><input type='checkbox' class='monthList' value='5'> Май </td><td>" . $select[5] . "</td></tr>";

echo "<tr><td><input type='checkbox' class='monthList' value='6'> Июнь </td><td>" . $select[6] . "</td></tr>";

echo "<tr><td><input type='checkbox' class='monthList' value='7'> Июль </td><td>" . $select[7] . "</td></tr>";

echo "<tr><td><input type='checkbox' class='monthList' value='8'> Август </td><td>" . $select[8] . "</td></tr>";

echo "<tr><td><input type='checkbox' class='monthList' value='9'> Сентябрь </td><td>" . $select[9] . "</td></tr>";

echo "<tr><td><input type='checkbox' class='monthList' value='10'> Октябрь </td><td>" . $select[10] . "</td></tr>";

echo "<tr><td><input type='checkbox' class='monthList' value='11'> Ноябрь </td><td>" . $select[11] . "</td></tr>";

echo "<tr><td><input type='checkbox' class='monthList' value='12'> Декабрь </td><td>" . $select[12] . "</td></tr></table>";

echo "</br><span class='btn btn-primary btn-sm addNewItem'>Сравнить</span></div>";

Modal::end();
// Модальное окно месяца


// Модальное окно сравнения
$modalListsName = Modal::begin([
    'header' => '<h5 class="settings_name">Сравнение</h5>',
    'id' => 'showSettingsList',
    'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
    'size'=>'modal-lg',
]);

echo "<div class='place_list' style='margin-left:15px; margin-right:15px;'></div>";

Modal::end();
// Модальное окно

?>
