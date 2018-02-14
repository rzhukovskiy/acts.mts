<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Tabs;
use common\assets\CanvasJs\CanvasJsAsset;

CanvasJsAsset::register($this);

$this->title = 'Статистика денежных средств';

$script = <<< JS
// формат числа
window.onload=function(){
  var formatSum2a = $('.kv-page-summary-container td:eq(2)');
  var persent = 0;
  var formatSum2 = $('td[data-col-seq="2"]');
  $(formatSum2).each(function (id, value) {
       var thisId = $(this);
       
       persent = parseFloat(thisId.text())/parseFloat(formatSum2a.text())*100;
       thisId.parent('tr').find('td[data-col-seq="3"]').text(persent.toFixed(2) + '%');
       persent = 0;

       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});

formatSum2a.text(formatSum2a.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
};

JS;
$this->registerJs($script, \yii\web\View::POS_READY);

$GLOBALS['namePlace'] = $namePlace;
$GLOBALS['type'] = $type;
if ($type == 1) {
    $name = 'Возвратные';
} else {
    $name = 'Невозвратные';
}
$GLOBALS['name'] = $name;

if (Yii::$app->controller->action->id == 'statprice') {
    echo Tabs::widget([
        'items' => [
            ['label' => 'Возвратные', 'url' => ['/company/statprice', 'type' => 1], 'active' => $type == 1],
            ['label' => 'Невозвратные', 'url' => ['/company/statprice', 'type' => 2], 'active' => $type == 2],
        ],
    ]);
} else {
    echo Tabs::widget([
        'items' => [
            ['label' => $name, 'url' => ['/company/statprice', 'type' => $type]],
            ['label' => 'Подробная статистика', 'active' => Yii::$app->controller->action->id == 'showstatprice'],
        ],
    ]);
}

if (Yii::$app->controller->action->id == 'statprice') {
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
                if ($data->send) {
                    return $data->send;
                } else {
                    return '-';
                }

            },
        ],
        [
            'header' => '%',
            'vAlign'=>'middle',
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
                        ['/company/showstatprice', 'site_address' => $data->site_address, 'type' => $GLOBALS['type']]);
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
        if ($data->tender_id) {
            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                ['/company/fulltender', 'tender_id' => $data->tender_id]);
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
        Статистика денежных средств
    </div>
    <div class="panel-body">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'showPageSummary' => true,
            'emptyText' => '',
            'layout' => '{items}',
            'columns' => $column,
        ]);
        ?>
    </div>
</div>

<?php
if (Yii::$app->controller->action->id == 'statprice') {
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
