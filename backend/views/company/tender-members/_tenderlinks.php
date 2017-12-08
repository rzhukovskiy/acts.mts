<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use common\models\TenderLinks;
use yii\helpers\Url;

$script = <<< JS


// формат числа
window.onload=function(){
  var formatSum = $('td[data-col-seq="5"]');
  $(formatSum).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
  var formatSum5a = $('.kv-page-summary-container td:eq(5)');
  $(formatSum5a).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
  
};
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
        'showPageSummary' => true,
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
                'pageSummary' => 'Всего',
            ],
            [
                'attribute' => 'tender.customer',
                'vAlign'=>'middle',
                'filter' => false,
                'value' => function ($data) {

                    if ($data->tender->customer) {
                        return $data->tender->customer;
                    } else {
                        return '-';
                    }

                },
            ],
            [
                'attribute' => 'tender.inn_customer',
                'vAlign'=>'middle',
                'value' => function ($data) {

                    if ($data->tender->inn_customer) {
                        return $data->tender->inn_customer;
                    } else {
                        return '-';
                    }

                },
            ],

            [
                'attribute' => 'tender.place',
                'vAlign'=>'middle',
                'filter' => false,
                'value' => function ($data) {

                    if ($data->tender->place) {
                        return $data->tender->place;
                    } else {
                        return '-';
                    }

                },
            ],
            [
                'attribute' => 'tender.price_nds',
                'vAlign'=>'middle',
                'filter' => false,
                'pageSummary' => true,
                'pageSummaryFunc' => GridView::F_SUM,
                'value' => function ($data) {

                    if ($data->tender->price_nds) {
                        return $data->tender->price_nds;
                    } else {
                        return '-';
                    }

                },
            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'header' => 'Выиграл',
                'vAlign'=>'middle',
                'template' => '{view}',
                'contentOptions' => ['style' => 'min-width: 60px'],
                'buttons' => [
                    'view' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-plus"></span>');
                    },
                ],

            ],
            [
                'class' => 'kartik\grid\ActionColumn',
                'header' => 'Действие',
                'vAlign'=>'middle',
                'template' => '{update}',
                'contentOptions' => ['style' => 'min-width: 60px'],
                'buttons' => [
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-search"></span> ',
                            ['/company/fulltender', 'tender_id' => $model->id]);
                    },
                ],
            ],
        ],
    ]);
    ?>
</div>
