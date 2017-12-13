<?php

use kartik\grid\GridView;
use yii\widgets\Pjax;
use common\models\Service;
use yii\bootstrap\Html;
use common\assets\CanvasJs\CanvasJsAsset;
use common\models\Company;
use \yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this yii\web\View
 * @var $type int
 * @var $searchModel \frontend\models\search\ActSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $chartData array
 * @var $totalProfit int
 * @var $totalServe int
 * @var $totalExpense int
 * @var $totalIncome int
 * @var $group string
 */
$actionLinkCompare = Url::to('@web/stat/compare');

$script = <<< JS



// открываем модальное окно сравнения
$('.compare').on('click', function() {
    $('#showListsName').modal('show');
});

var arrMonth = [];
$('.addNewItem').on('click', function() {
    
    arrMonth = [];
    
    $('#showListsName').modal('hide');
    
       $('.monthList').each(function (value) {
      if ($(this).is(':checked')) {
          arrMonth.push($(this).val());
     }
       
});
      sendCompare();
    $('#showSettingsList').modal('show');
});

function sendCompare() {
          $.ajax({
                type     :'POST',
                cache    : true,
                data: 'arrMonth=' + JSON.stringify(arrMonth),
                url  : '$actionLinkCompare',
                success  : function(data) {
                var resTables = "";
                var reswash = "";
                var restires = "";
                var resdesinf = "";
                var resservise = "";
                var response = $.parseJSON(data);
               
                var countServe = '';
                var ssoom = '';
                var income = '';
                var profit = '';
                
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
                
                var oldvalue = [];
                oldvalue[2] = [];
                oldvalue[2]['1'] = '';
                oldvalue[2]['2'] = '';
                oldvalue[2]['3'] = '';
                oldvalue[2]['4'] = '';
                oldvalue[4] = [];
                oldvalue[4]['1'] = '';
                oldvalue[4]['2'] = '';
                oldvalue[4]['3'] = '';
                oldvalue[4]['4'] = '';
                oldvalue[3] = [];
                oldvalue[3]['1'] = '';
                oldvalue[3]['2'] = '';
                oldvalue[3]['3'] = '';
                oldvalue[3]['4'] = '';
                oldvalue[5] = [];
                oldvalue[5]['1'] = '';
                oldvalue[5]['2'] = '';
                oldvalue[5]['3'] = '';
                oldvalue[5]['4'] = '';
                
                
                if (response.success == 'true') {
                  
                // Удачно
                var arr = $.parseJSON(response.result);
                $.each(arr,function(key,data) {

                 $.each(data, function(index,value) {
                     
                     if (key == 2) {
                         
                        if(oldvalue[2]['1'] != '') {
                        if (oldvalue[2]['1'] > parseInt(value['countServe'])) {
                           countServe = value['countServe'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           countServe = value['countServe'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           countServe = value['countServe'];
                        }
                        
                        if(oldvalue[2]['2'] != '') {
                        if (oldvalue[2]['2'] > parseInt(value['ssoom'])) {
                           ssoom = value['ssoom'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           ssoom = value['ssoom'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           ssoom = value['ssoom'];
                        }
                        
                        if(oldvalue[2]['3'] != '') {
                        if (oldvalue[2]['3'] > parseInt(value['income'])) {
                           income = value['income'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           income = value['income'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           income = value['income'];
                        }
                        
                        if(oldvalue[2]['4'] != '') {
                        if (oldvalue[2]['4'] > parseInt(value['profit'])) {
                           profit = value['profit'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           profit = value['profit'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           profit = value['profit'];
                        }
                        
                        oldvalue[2]['1'] = parseInt(value['countServe']);
                        oldvalue[2]['2'] = parseInt(value['ssoom']);
                        oldvalue[2]['3'] = parseInt(value['income']);
                        oldvalue[2]['4'] = parseInt(value['profit']);
                        
                        reswash += "<tr><td>" + month[index] + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                     }
                     
                     
                     if (key == 4) {
                         
                         if (oldvalue[4]['1'] != '') {
                         if (oldvalue[4]['1'] > parseInt(value['countServe'])) {
                           countServe = value['countServe'] + ' <span style="color:red;">&#8595</span>';
                         } else {
                           countServe = value['countServe'] + ' <span style="color:green;">&#8593</span>';
                         }
                         } else {
                           countServe = value['countServe'];
                         }
                       
                        if (oldvalue[4]['2'] != '') {
                        if (oldvalue[4]['2'] > parseInt(value['ssoom'])) {
                           ssoom = value['ssoom'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           ssoom = value['ssoom'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           ssoom = value['ssoom'];
                        }
                       
                        if (oldvalue[4]['3'] != '') {
                        if (oldvalue[4]['3'] > parseInt(value['income'])) {
                           income = value['income'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           income = value['income'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           income = value['income'];
                        }
                        
                        if (oldvalue[4]['4'] != '') {
                        if (oldvalue[4]['4'] > parseInt(value['profit'])) {
                           profit = value['profit'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           profit = value['profit'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           profit = value['profit'];
                        }
                        
                        oldvalue[4]['1'] = parseInt(value['countServe']);
                        oldvalue[4]['2'] = parseInt(value['ssoom']);
                        oldvalue[4]['3'] = parseInt(value['income']);
                        oldvalue[4]['4'] = parseInt(value['profit']);
                         
                         restires += "<tr><td>" + month[index] + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                     }
                     
                    
                     
                     if (key == 3) {
                         
                         if (oldvalue[3]['1'] != '') {
                         if (oldvalue[3]['1'] > parseInt(value['countServe'])) {
                           countServe = value['countServe'] + ' <span style="color:red;">&#8595</span>';
                         } else {
                           countServe = value['countServe'] + ' <span style="color:green;">&#8593</span>';
                         }
                         } else {
                           countServe = value['countServe'];
                         }
                       
                        if (oldvalue[3]['2'] != '') {
                        if (oldvalue[3]['2'] > parseInt(value['ssoom'])) {
                           ssoom = value['ssoom'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           ssoom = value['ssoom'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           ssoom = value['ssoom'];
                        }
                       
                        if (oldvalue[3]['3'] != '') {
                        if (oldvalue[3]['3'] > parseInt(value['income'])) {
                           income = value['income'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           income = value['income'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           income = value['income'];
                        }
                        
                        if (oldvalue[3]['4'] != '') {
                        if (oldvalue[3]['4'] > parseInt(value['profit'])) {
                           profit = value['profit'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           profit = value['profit'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           profit = value['profit'];
                        }
                        
                        oldvalue[3]['1'] = parseInt(value['countServe']);
                        oldvalue[3]['2'] = parseInt(value['ssoom']);
                        oldvalue[3]['3'] = parseInt(value['income']);
                        oldvalue[3]['4'] = parseInt(value['profit']);
                         
                         resservise += "<tr><td>" + month[index] + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                     }
                     
                     
                     
                     if (key == 5) {
                         
                        if(oldvalue[5]['1'] != '') {
                        if (oldvalue[5]['1'] > parseInt(value['countServe'])) {
                           countServe = value['countServe'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           countServe = value['countServe'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           countServe = value['countServe'];
                        }
                        
                        if(oldvalue[5]['2'] != '') {
                        if (oldvalue[5]['2'] > parseInt(value['ssoom'])) {
                           ssoom = value['ssoom'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           ssoom = value['ssoom'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           ssoom = value['ssoom'];
                        }
                        
                        if(oldvalue[5]['3'] != '') {
                        if (oldvalue[5]['3'] > parseInt(value['income'])) {
                           income = value['income'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           income = value['income'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           income = value['income'];
                        }
                        
                        if(oldvalue[5]['4'] != '') {
                        if (oldvalue[5]['4'] > parseInt(value['profit'])) {
                           profit = value['profit'] + ' <span style="color:red;">&#8595</span>';
                        } else {
                           profit = value['profit'] + ' <span style="color:green;">&#8593</span>';
                        }
                        } else {
                           profit = value['profit'];
                        }
                        
                        oldvalue[5]['1'] = parseInt(value['countServe']);
                        oldvalue[5]['2'] = parseInt(value['ssoom']);
                        oldvalue[5]['3'] = parseInt(value['income']);
                        oldvalue[5]['4'] = parseInt(value['profit']);
                        
                         resdesinf += "<tr><td>" + month[index] + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                     }

                            });
                    });
                
                     if (reswash.length > 0) {
                      resTables += "<table border='1' width='100%' bordercolor='#dddddd'><tr height='25px'><td colspan='5' align='center' style='color: #000000;'>Мойка</td></tr><tr height='25px'><td>Месяц</td><td>Обслужено</td><td>ССООМ</td><td>Доход</td><td>Прибыль</td></tr>" + reswash +"</table></br>";
                     }
                     if (restires.length > 0) {
                      resTables += "<table border='1' width='100%' bordercolor='#dddddd'><tr height='25px'><td colspan='5' align='center' style='color: #000000;'>Шиномонтаж</td></tr><tr height='25px'><td>Месяц</td><td>Обслужено</td><td>ССООМ</td><td>Доход</td><td>Прибыль</td></tr>" + restires +"</table></br>";
                     }
                     if (resservise.length > 0) {
                     resTables += "<table border='1' width='100%' bordercolor='#dddddd'><tr height='25px'><td colspan='5' align='center' style='color: #000000;'>Сервис</td></tr><tr height='25px'><td>Месяц</td><td>Обслужено</td><td>ССООМ</td><td>Доход</td><td>Прибыль</td></tr>" + resservise +"</table></br>";
                     }
                     if (resdesinf.length > 0) {
                     resTables += "<table border='1' width='100%' bordercolor='#dddddd'><tr height='25px'><td colspan='5' align='center' style='color: #000000;'>Дезинфекция</td></tr><tr height='25px'><td>Месяц</td><td>Обслужено</td><td>ССООМ</td><td>Доход</td><td>Прибыль</td></tr>" + resdesinf +"</table></br>";
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
$this->registerJs($script, View::POS_READY);
CanvasJsAsset::register($this);

$css = ".modal {
    overflow-y: auto;
}";
$this->registerCss($css);

$this->title = 'Общая статистика';
echo $this->render('../_tabs', ['action' => $group]);

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

$currentMonth = isset($searchModel->dateFrom)
    ? date('n', strtotime($searchModel->dateFrom))
    : date('n');
$currentMonth--;

$filters = '';
$periodForm = '';
$periodForm .= Html::dropDownList('period', $period, \common\models\Act::$periodList, ['class' =>'select-period form-control', 'style' => 'margin-right: 10px;']);
$periodForm .= Html::dropDownList('month', $currentMonth, $months, ['id' => 'month', 'class' => 'autoinput form-control', 'style' => $diff == 1 ? '' : 'display:none']);
$periodForm .= Html::dropDownList('half', $currentMonth < 5 ? 0 : 1, $halfs, ['id' => 'half', 'class' => 'autoinput form-control', 'style' => $diff == 6 ? '' : 'display:none']);
$periodForm .= Html::dropDownList('quarter', floor($currentMonth / 3), $quarters, ['id' => 'quarter', 'class' => 'autoinput form-control', 'style' => $diff == 3 ? '' : 'display:none']);
$periodForm .= Html::dropDownList('year', 10, range(date('Y') - 10, date('Y')), ['id' => 'year', 'class' => 'autoinput form-control', 'style' => $diff && $diff <= 12 ? '' : 'display:none']);
$periodForm .= Html::activeTextInput($searchModel, 'dateFrom', ['class' => 'date-from ext-filter hidden']);
$periodForm .= Html::activeTextInput($searchModel, 'dateTo',  ['class' => 'date-to ext-filter hidden']);
$periodForm .= Html::submitButton('Показать', ['class' => 'btn btn-primary date-send', 'style' => 'margin-left: 10px;']);

if ($admin) {
    $filters = 'Выбор компании: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->andWhere(['type' => Company::TYPE_OWNER])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
} elseif (!empty(Yii::$app->user->identity->company->children)) {
    $filters = 'Выбор филиала: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->andWhere(['parent_id' => Yii::$app->user->identity->company_id])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
}

$filters .= 'Выбор периода: ' . $periodForm;

/**
 * Конец виджета
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Общая статистика
    </div>
    <div class="panel-body">
        <?php
        //Pjax::begin();
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => false,
            'emptyCell' => '',

            'showFooter' => true,
            'floatHeader' => $admin,
            'floatHeaderOptions' => ['top' => '0'],
            'hover' => false,
            'striped' => false,
            'export' => false,
            'filterSelector' => '.ext-filter',
            'beforeHeader' => [
                [
                    'columns' => [
                        [
                            'content' => $filters . '<span class="pull-right btn btn-warning btn-sm compare" style="padding: 6px 8px; margin-top: 2px; border:1px solid #c18431;">Сравнить</span>',
                            'options' => ['colspan' => 8, 'style' => 'vertical-align: middle', 'class' => 'kv-grid-group-filter period-select'],
                        ],
                    ],
                    'options' => ['class' => 'filters extend-header'],
                ],
                [
                    'columns' => [
                        [
                            'content' => '&nbsp',
                            'options' => [
                                'colspan' => 8,
                            ]
                        ]
                    ],
                    'options' => ['class' => 'kv-group-header'],
                ],
            ],

            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn',
                    'footer' => 'Итого:',
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'service_type',
                    'label' => 'Услуга',
                    'content' => function ($data) use ($group) {
                        if (empty($data->service_type))
                            $title = '—';
                        else
                            $title = Html::a(Service::$listType[$data->service_type]['ru'], ['/stat/list', 'type' => $data->service_type, 'group' => $group]);

                        return $title;
                    },
                ],
                [
                    'attribute' => 'countServe',
                    'label' => 'Обслужено',
                    'footer' => $totalServe,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'ssoom',
                    'label' => 'ССООМ',
                    'contentOptions' => ['class' => 'success'],
                ],
                [
                    'attribute' => 'expense',
                    'label' => 'Расход',
                    'content' => function ($data) {
                        return Yii::$app->formatter->asDecimal($data->expense, 0);
                    },
                    'footer' => $totalExpense,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                    'visible' => $group == 'partner',
                ],
                [
                    'attribute' => 'income',
                    'label' => 'Доход',
                    'content' => function ($data) {
                        return Yii::$app->formatter->asDecimal($data->income, 0);
                    },
                    'footer' => $totalIncome,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                    'visible' => $group == 'company',
                ],
                [
                    'attribute' => 'profit',
                    'label' => 'Прибыль',
                    'content' => function ($data) {
                        return Html::tag('strong', Yii::$app->formatter->asDecimal($data->profit, 0));
                    },
                    'footer' => $totalProfit,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) use ($group) {
                            if (isset(Yii::$app->request->queryParams['ActSearch'])) {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/list', 'type' => $model->service_type, 'group' => $group, 'ActSearch' => Yii::$app->request->queryParams['ActSearch']]);
                            } else {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/list', 'type' => $model->service_type, 'group' => $group]);
                            }
                        },
                    ]
                ],
            ],
        ]);
        //Pjax::end();

        // Модальное окно сравнения
        $modalListsName = Modal::begin([
            'header' => '<h5>Выбор месяца</h5>',
            'id' => 'showListsName',
            'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
            'size'=>'modal-sm',
        ]);

        echo "<input type='checkbox' class='monthList' value='1'> Январь</br>";
        echo "<input type='checkbox' class='monthList' value='2'> Февраль</br>";
        echo "<input type='checkbox' class='monthList' value='3'> Март</br>";
        echo "<input type='checkbox' class='monthList' value='4'> Апрель</br>";
        echo "<input type='checkbox' class='monthList' value='5'> Май</br>";
        echo "<input type='checkbox' class='monthList' value='6'> Июнь</br>";
        echo "<input type='checkbox' class='monthList' value='7'> Июль</br>";
        echo "<input type='checkbox' class='monthList' value='8'> Август</br>";
        echo "<input type='checkbox' class='monthList' value='9'> Сентябрь</br>";
        echo "<input type='checkbox' class='monthList' value='10'> Октябрь</br>";
        echo "<input type='checkbox' class='monthList' value='11'> Ноябрь</br>";
        echo "<input type='checkbox' class='monthList' value='12'> Декабрь</br>";
        echo "</br><span class='btn btn-primary btn-sm addNewItem'>Сравнить</span></div>";

        Modal::end();
        // Модальное окно сравнения

        // Модальное окно
        $modalListsName = Modal::begin([
            'header' => '<h5 class="settings_name">Сравнение</h5>',
            'id' => 'showSettingsList',
            'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
            'size'=>'modal-lg',
        ]);

        echo "<div class='place_list' style='font-size: 15px; margin-left:15px; margin-right:15px;'></div>";

        Modal::end();
        // Модальное окно
        ?>
        <hr>

        <div class="col-sm-12">
            <div id="chart_div" style="width:100%;height:500px;"></div>
            <?php
            $js = "CanvasJS.addColorSet('blue', ['#428bca']);
                var dataTable = " . $chartData . ";
                var max = 0;
                dataTable.forEach(function (value) {
                    if (value.y > max) max = value.y;
                });
                var options = {
                    colorSet: 'blue',
                    dataPointMaxWidth: 40,
                    title: {
                        text: 'По месяцам',
                        fontColor: '#069',
                        fontSize: 22
                    },
                    subtitles: [
                        {
                            text: 'Прибыль',
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
                        title: 'Месяц',
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
                        valueFormatString: '### ### ###',
                        maximum: max + 0.1 * max
                    }
                };

                $('#chart_div').CanvasJSChart(options);
                ";
            $this->registerJs($js);
            ?>
        </div>

    </div>
</div>
