<?php

use yii\helpers\Html;
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

        if(Yii::$app->controller->action->id == 'new') {

            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'hover' => false,
                'striped' => false,
                'export' => false,
                'summary' => false,
                'emptyText' => '',
                'layout' => '{items}',
                'columns' => [
                    [
                        'attribute' => 'user_id',
                        'contentOptions' => ['class' => 'value_0'],
                        'value' => function ($data) {
                            return $GLOBALS['authorMembers'][$data->user_id];
                        },
                    ],
                    [
                        'attribute' => 'companyNum',
                        'contentOptions' => ['class' => 'value_1', 'style' => 'width: 300px'],
                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'template' => '{view}',
                        'contentOptions' => ['style' => 'min-width: 120px'],
                        'buttons' => [
                            'view' => function ($url, $model, $key) {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                    ['/activity/shownew', 'user_id' => $model->user_id, 'type' => $GLOBALS['type']]);
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
                            return date('H:i d.m.Y', $data->company->created_at);
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
                
                var arrLable = $(this).find('.value_0').text().split(' ');
                arrLable = arrLable[1].split('.');
                arrLable = arrLable[1] + '.' + arrLable[2];
                
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
                            text: 'По месяцам',
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
                        valueFormatString: '### ### ###.#',
                        maximum: max + 0.1 * max
                    }
                };

                $('#chart_div').CanvasJSChart(options);
                ";
}

$this->registerJs($js);

?>