<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use common\models\TenderLinks;
use yii\helpers\Url;
use common\models\TenderLists;

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
$GLOBALS['arrLists'] = $arrLists;
?>

<div class="panel-body">
    <?php

    $GLOBALS['member'] = Yii::$app->request->get('id');

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'hover' => false,
        'striped' => false,
        'export' => false,
        'summary' => false,
        'showPageSummary' => true,
        'emptyText' => '',
        'layout' => '{items}',
        'rowOptions' => function ($model) {

            // Выделяем цветом для каких типов
            if(isset($GLOBALS['tender_win'])) {
                if ($GLOBALS['tender_win'] == 1) {
                    return ['style' => 'background:#ffd5d5;'];
                } else {
                    return '';
                }
            } else {
                return '';
            }
        },
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
                'attribute' => 'tender.site_address',
                'vAlign'=>'middle',
                'filter' => false,
                'value' => function ($data) {

                    if (isset($GLOBALS['arrLists'][8][$data->tender->site_address])) {
                        return $GLOBALS['arrLists'][8][$data->tender->site_address];
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

                        // Определяем победителя тендера
                        $resWinner = TenderLinks::find()->where(['AND', ['tender_id' => $model->tender->id], ['member_id' => $GLOBALS['member']]])->select('winner')->asArray()->column();

                        $win = 0;
                        if(count($resWinner) > 0) {
                            $win = $resWinner[0];
                            $GLOBALS['tender_win'] = $resWinner[0];
                        }

                        if ($win == 1) {
                            return Html::a('<span class="glyphicon glyphicon-remove"></span>', ['/company/tendermemberwin', 'tender_id' => $model->tender->id, 'member_id' => $GLOBALS['member'], 'winner' => 0]);
                        } else {
                            return Html::a('<span class="glyphicon glyphicon-ok"></span>', ['/company/tendermemberwin', 'tender_id' => $model->tender->id, 'member_id' => $GLOBALS['member'], 'winner' => 1]);
                        }

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
