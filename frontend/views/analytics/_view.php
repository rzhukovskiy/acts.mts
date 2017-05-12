<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $group string
 */

use common\models\Act;
use common\models\Mark;
use common\models\Type;
use kartik\grid\GridView;
use yii\helpers\Html;
use common\assets\CanvasJs\CanvasJsAsset;

CanvasJsAsset::register($this);

$columns = [];

if ($group == 'average') {

    $columns = [
        [
            'header' => '№',
            'vAlign'=>'middle',
            'class' => 'kartik\grid\SerialColumn',
            'contentOptions' => ['style' => 'max-width: 40px'],
        ],
        [
            'header' => 'Месяц',
            'vAlign'=>'middle',
            'pageSummary' => 'Всего',
            'contentOptions' => ['class' => 'value_0'],
            'filter' => Act::getDayList(),
            'value' => function ($data) {

                $months = [
                    'Январь',
                    'Февраль',
                    'Март',
                    'Апрель',
                    'Май',
                    'Июнь',
                    'Июль',
                    'Август',
                    'Сентябрь',
                    'Октябрь',
                    'Ноябрь',
                    'Декабрь',
                ];

                $index = date('n', $data->served_at) - 1;

                return $months[$index] . ' ' . date('Y', $data->served_at);
            },
        ],
        [
            'header' => 'Обслужено',
            'vAlign'=>'middle',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'value' => function ($data) {
                return $data->actsCount;
            },
        ],
        [
            'header' => 'Среднее кол-во<br />операций на 1 ТС',
            'vAlign'=>'middle',
            'pageSummary' => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'contentOptions' => ['class' => 'value_2'],
            'value' => function ($data) {
                $timeFrom = (date('Y', time()) - 1) . '-12-31 21:00:00';
                $timeFrom = strtotime($timeFrom);

                $timeTo = date('Y', time()) . '-12-31 21:00:00';
                $timeTo = strtotime($timeTo);

                return frontend\controllers\AnalyticsController::getWorkCars($data->client->id, $data->service_type, false, $data->actsCount, $timeFrom, $timeTo);
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
            'attribute' => 'day',
            'filter' => Act::getDayList(),
            'value' => function ($data) {
                return date('d-m-Y', $data->served_at);
            },
            'visible' => $group != 'count',
        ],
        [
            'attribute' => 'mark_id',
            'filter' => Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
            'value' => function ($data) {
                return isset($data->mark) ? $data->mark->name : 'error';
            },
        ],
        [
            'header' => 'Номер',
            'value' => function ($data) {
                //неудачная кострукция, $data может быть как Car так и Act. аттрибуты там по-разному называются
                return !empty($data->car_number) ? $data->car_number : $data->number;
            },
        ],
        [
            'attribute' => 'type_id',
            'filter' => Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column(),
            'value' => function ($data) {
                return isset($data->type) ? $data->type->name : 'error';
            },
        ],
        [
            'header' => 'Период обслуживаний',
            'value' => function ($data) {
                // Вывод среднего времени обслуживания
                if (isset($data->service_type)) {
                    return \frontend\controllers\AnalyticsController::getSrTime($data->car_number, $data->service_type);
                } else {
                    return \frontend\controllers\AnalyticsController::getSrTime($data->number, -1);
                }
            },
            'visible' => $group == 'count',
        ],
        [
            'header' => '',
            'mergeHeader' => false,
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{view}',
            'width' => '40px',
            'buttons' => [
                'view' => function ($url, $data, $key) use ($group, $searchModel) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>', [
                        'detail',
                        'ActSearch[dateFrom]' => $searchModel->dateFrom,
                        'ActSearch[dateTo]' => $searchModel->dateTo,
                        'ActSearch[car_number]' => $data->car_number,
                        'ActSearch[service_type]' => $searchModel->service_type,
                    ]);
                },
            ],
            'visible' => $group == 'count' && $count != 0,
        ],
    ];

}

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => false,
    'emptyText' => '',
    'floatHeader' => true,
    'showPageSummary' => true,
    'floatHeaderOptions' => ['top' => '0'],
    'panel' => [
        'type' => 'primary',
        'heading' => 'Обслуженные машины',
        'before' => false,
        'footer' => false,
        'after' => false,
    ],
    'hover' => false,
    'striped' => false,
    'export' => false,
    'columns' => $columns,
]);

// Разобраться почему не сходятся сред. кол операций
// что делать с десятичными дробями на графике??

if ($group == 'average') {
    $js = "CanvasJS.addColorSet('blue', ['#428bca']);
                var dataTable = [];
                var max = 0;
                $('.table tbody tr').each(function (id, value) {
                if($(this).find('.value_0').text() != '') {
                dataTable.push({
                    label: $(this).find('.value_0').text(),
                    y: parseFloat($(this).find('.value_2').text().replace(/\s+/g, '').replace(',', '')),
                });
                
                dataTable.forEach(function (value) {
                    if (value.y > max) max = value.y;
                });
                    
                }
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
                            text: 'Среднее кол-во операций на 1 ТС',
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

    echo "<div class=\"panel panel-primary\"><div class=\"panel-body\">
        <div class=\"col-sm-12\">
            <div id=\"chart_div\" style=\"width:100%;height:500px;\"></div>" . $this->registerJs($js) . "</div></div></div>";
}