<?php

use kartik\grid\GridView;
use yii\helpers\Html;

$script = <<< JS
// Сортировка по дням до оплаты

var i = 0;

var arrayDataKey = [];
$.map($(".table tbody tr"), function(el) {

    if($(el).data("key") > 0) {
        arrayDataKey[i] = [];
        arrayDataKey[i][0] = el;
        arrayDataKey[i][2] = $(el).children("td:first").text();

        if($(el).children("td").eq(3).text() == "-") {
            arrayDataKey[i][3] = Number(-1);
        } else {
            arrayDataKey[i][3] = Number($(el).children("td").eq(3).text());
        }

        i++;
    }
});

function ReplaceItemsTable(firstEl, secEl) {

    var content1 = $(arrayDataKey[firstEl][0]).html();
    var content1i = arrayDataKey[firstEl][2];
    var content2 = $(arrayDataKey[secEl][0]).html();
    var content2i = arrayDataKey[secEl][2];

    $(arrayDataKey[firstEl][0]).html(content2).show();
    $(arrayDataKey[secEl][0]).html(content1).show();
    $(arrayDataKey[firstEl][0]).children("td:first").text(content1i);
    $(arrayDataKey[secEl][0]).children("td:first").text(content2i);

    i = 0;

    arrayDataKey = [];
    $.map($(".table tbody tr"), function(el) {

        if($(el).data("key") > 0) {
            arrayDataKey[i] = [];
            arrayDataKey[i][0] = el;
            arrayDataKey[i][2] = $(el).children("td:first").text();

            if($(el).children("td").eq(3).text() == "-") {
                arrayDataKey[i][3] = Number(-1);
            } else {
                arrayDataKey[i][3] = Number($(el).children("td").eq(3).text());
            }

            i++;
        }
    });

}

// Кнопка сортировать
var readyToSort = 1;
var typeSort = 1;

var rangeTitle = document.querySelectorAll("[data-col-seq='3']");

rangeTitle[0].style.cursor= "pointer";
//rangeTitle[0].style.color= "#23527c";
rangeTitle[0].style.textDecoration= "underline";

rangeTitle[0].addEventListener("click", function() {

    if(readyToSort == 1) {

        if(typeSort == 0) {
            typeSort = 1;
        } else {
            typeSort = 0;
        }

        readyToSort = 0;

        if(typeSort == 0) {

            var min = 0;
            var min_i = 0;

            for (var z = 0; z < i; z++) {

                min = arrayDataKey[z][3];
                min_i = z;

                for (var j = z; j < i; j++) {

                    if (arrayDataKey[j][3] < min) {
                        min = arrayDataKey[j][3];
                        min_i = j;
                    }
                }

        if (z != min_i) {
            ReplaceItemsTable(min_i, z);
        }
        
    }

    } else {

            var min = 0;
            var min_i = 0;

            for (var z = 0; z < i; z++) {

                min = arrayDataKey[z][3];
                min_i = z;

                for (var j = z; j < i; j++) {

                    if (arrayDataKey[j][3] > min) {
                        min = arrayDataKey[j][3];
                        min_i = j;
                    }
                }

        if (z != min_i) {
            ReplaceItemsTable(min_i, z);
        }
        
     }
     
    }

        readyToSort = 1;

    }

}, false);

// Сортировка по дням до оплаты
JS;
$this->registerJs($script, \yii\web\View::POS_READY);
?>

    <div class="panel-body">
        <?php

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
                    'attribute' => 'company_name',
                    'vAlign'=>'middle',
                    'filter' => false,
                    'header' => 'Компания',
                    'value' => function ($data) {

                        if ($data->company_name) {
                            return $data->company_name;
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'vAlign'=>'middle',
                    'header' => 'Кол-во тендеров',
                    'value' => function ($data) {
                        return \backend\controllers\CompanyController::getCount($data->id);
                    },
                ],
                [
                    'attribute' => 'inn',
                    'vAlign'=>'middle',
                    'header' => 'ИНН',
                    'value' => function ($data) {

                        if ($data->inn) {
                            return $data->inn;
                        } else {
                            return '-';
                        }

                    },
                ],

                [
                    'attribute' => 'city',
                    'vAlign'=>'middle',
                    'filter' => false,
                    'header' => 'Город',
                    'value' => function ($data) {

                        if ($data->city) {
                            return $data->city;
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'comment',
                    'vAlign'=>'middle',
                    'filter' => false,
                    'header' => 'Комментарий',
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
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-search"></span>',
                                ['/company/fulltendermembers', 'id' => $model->id]);
                        },
                    ],
                ],
            ],
        ]);
        ?>
</div>
