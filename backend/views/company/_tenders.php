<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use common\models\Company;
use common\models\TenderLists;

$script = <<< JS
// формат числа
window.onload=function(){
  var formatSum = $('td[data-col-seq="9"]');
  $(formatSum).each(function (id, value) {
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

<div class="panel panel-primary">
    <div class="panel-heading">
        ТЕНДЕРЫ :: ИСТОРИЯ
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', ['company/newtender', 'id' => $model->id], ['class' => 'btn btn-success btn-sm']) ?>
        </div>
    </div>
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
                'attribute' => 'purchase_status',
                'header' => 'Статус<br />закупки',
                'format' => 'raw',
                'vAlign'=>'middle',
                'value' => function ($data) {

                    if ($data->purchase_status) {
                        return isset($GLOBALS['arrLists'][0][$data->purchase_status]) ? $GLOBALS['arrLists'][0][$data->purchase_status] : '-';
                    } else {
                        return '-';
                    }
                },
            ],
            [
                'attribute' => 'user_id',
                'header' => 'Сотрудник',
                'format' => 'raw',
                'vAlign'=>'middle',
                'value' => function ($data) {

                    $arrUserTend = explode(', ', $data->user_id);
                    $UserTendText = '';

                    if (count($arrUserTend) > 1) {

                        for ($i = 0; $i < count($arrUserTend); $i++) {

                            $index = $arrUserTend[$i];

                            if(isset($GLOBALS['arrLists'][1][$index]) ? $GLOBALS['arrLists'][1][$index] : '-') {
                                $UserTendText .= (isset($GLOBALS['arrLists'][1][$index]) ? $GLOBALS['arrLists'][1][$index] : '-') . '<br />';
                            }
                        }

                    } else {

                        try {
                            if(isset($GLOBALS['arrLists'][1]) ? $GLOBALS['arrLists'][1][$data->user_id] : '-') {
                                $UserTendText = isset($GLOBALS['arrLists'][1]) ? $GLOBALS['arrLists'][1][$data->user_id] : '-';
                            }
                        } catch (\Exception $e) {
                            $UserTendText = '-';
                        }

                    }

                    return $UserTendText;
                },
            ],
            [
                'attribute' => 'date_request_end',
                'vAlign'=>'middle',
                'header' => 'Окончание подачи<br /> заявки',
                'value' => function ($data) {

                    if ($data->date_request_end) {
                        return date('d.m.Y', $data->date_request_end);
                    } else {
                        return '-';
                    }

                },
            ],
            [
                'attribute' => 'time_bidding_end',
                'vAlign'=>'middle',
                'header' => 'Дата и время<br /> подведения итогов',
                'value' => function ($data) {

                    if ($data->time_bidding_end) {
                        return date('d.m.Y', $data->time_bidding_end);
                    } else {
                        return '-';
                    }

                },
            ],

            [
                'attribute' => 'method_purchase',
                'header' => 'Способ<br />закупки',
                'format' => 'raw',
                'vAlign'=>'middle',
                'value' => function ($data) {

                    $arrMethodsTend = explode(', ', $data->method_purchase);
                    $methodsText = '';

                    if (count($arrMethodsTend) > 1) {

                        for ($i = 0; $i < count($arrMethodsTend); $i++) {

                            $index = $arrMethodsTend[$i];

                            if(isset($GLOBALS['arrLists'][2][$index]) ? $GLOBALS['arrLists'][2][$index] : '-') {
                                $methodsText .= (isset($GLOBALS['arrLists'][2][$index]) ? $GLOBALS['arrLists'][2][$index] : '-') . '<br />';
                            }
                        }
                    } else {

                        try {
                            if(isset($GLOBALS['arrLists'][2]) ? $GLOBALS['arrLists'][2][$data->method_purchase] : '-') {
                                $methodsText = isset($GLOBALS['arrLists'][2]) ? $GLOBALS['arrLists'][2][$data->method_purchase] : '-';
                            }
                        } catch (\Exception $e) {
                            $methodsText = '-';
                        }

                    }
                    return $methodsText;
                },
            ],
            [
                'attribute' => 'city',
                'vAlign'=>'middle',
                'header' => 'Город, <br />Область поставки',
                'value' => function ($data) {

                    if ($data->city) {
                        return $data->city;
                    } else {
                        return '-';
                    }

                },
            ],
            [
                'attribute' => 'service_type',
                'header' => 'Закупаемые<br />услуги',
                'format' => 'raw',
                'vAlign'=>'middle',
                'value' => function ($data) {

                    $arrServices = explode(', ', $data->service_type);
                    $serviceText = '';

                    if (count($arrServices) > 1) {

                        for ($i = 0; $i < count($arrServices); $i++) {

                            $index = $arrServices[$i];

                            if(isset($GLOBALS['arrLists'][3][$index]) ? $GLOBALS['arrLists'][3][$index] : '-') {
                                $serviceText .= (isset($GLOBALS['arrLists'][3][$index]) ? $GLOBALS['arrLists'][3][$index] : '-') . '<br />';
                            }
                        }

                    } else {

                        try {
                            if(isset($GLOBALS['arrLists'][3]) ? $GLOBALS['arrLists'][3][$data->service_type] : '-') {
                                $serviceText = isset($GLOBALS['arrLists'][3]) ? $GLOBALS['arrLists'][3][$data->service_type] : '-';
                            }
                        } catch (\Exception $e) {
                            $serviceText = '-';
                        }

                    }

                    return $serviceText;

                },
            ],
            [
                'attribute' => 'price_nds',
                'vAlign'=>'middle',
                'header' => 'Максимальная<br /> стоимость закупки',
                'value' => function ($data) {

                    if ($data->price_nds) {
                        return $data->price_nds;
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
                            ['/company/fulltender', 'tender_id' => $model->id]);
                    },
                ],
            ],
        ],
    ]);
    ?>
    </div>
</div>
