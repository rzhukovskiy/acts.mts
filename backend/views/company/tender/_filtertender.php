<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use common\models\Company;
use kartik\date\DatePicker;
use common\models\TenderLists;

?>

<?php
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

$columns = [

    [
                    'header' => '№',
                    'vAlign'=>'middle',
                    'class' => 'kartik\grid\SerialColumn'
                ],
                [
                    'attribute' => 'customer',
                    'header' => 'Заказчик',
                    'format' => 'raw',
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
                    'attribute' => 'inn_customer',
                    'header' => 'ИНН Заказчика',
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'value' => function ($data) {

                        if ($data->inn_customer) {
                            return $data->inn_customer;
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'attribute' => 'city',
                    'vAlign'=>'middle',
                    'filter' => false,
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
                        'filter' => false,
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
                    'attribute' => 'number_purchase',
                    'vAlign'=>'middle',
                    'header' => 'Номер закупки на площадке',
                    'filter' => false,
                    'value' => function ($data) {

                        if ($data->number_purchase) {
                            return $data->number_purchase;
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'attribute' => 'place',
                    'vAlign'=>'middle',
                    'header' => 'Электронная площадка',
                    'filter' => false,
                    'value' => function ($data) {

                        if ($data->place) {
                            return $data->place;
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'attribute' => 'cost_purchase_completion',
                    'header' => 'Стоимость закупки по завершению закупки без НДС',
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'value' => function ($data) {

                        if ($data->cost_purchase_completion) {
                            return $data->cost_purchase_completion . ' ₽';
                        } else {
                            return '-';
                        }
                    },
                ],


                [
                    'attribute' => 'date_contract',
                    'vAlign'=>'middle',
                    'header' => 'Дата заключения договора',
                    'value' => function ($data) {

                        if ($data->date_contract) {
                            return date('d.m.Y', $data->date_contract);
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'attribute' => 'term_contract',
                    'vAlign'=>'middle',
                    'header' => 'Дата окончания заключенного договора',
                    'value' => function ($data) {

                        if ($data->term_contract) {
                            return date('d.m.Y', $data->term_contract);
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'vAlign'=>'middle',
                    'header' => 'Осталось дней до окончания договора',
                    'value' => function ($model) {

            if($model->term_contract) {
                $timeNow = time();

                $showTotal = '';

                if ($model->term_contract > $timeNow) {

                    $totalDate = $model->term_contract - $timeNow;

                    $days = ((Int)($totalDate / 86400));
                    $totalDate -= (((Int)($totalDate / 86400)) * 86400);

                    if ($days < 0) {
                        $days = 0;
                    }

                    $showTotal .= $days . ' д.';

                } else {
                    $totalDate = $timeNow - $model->term_contract;

                    $days = ((Int)($totalDate / 86400));
                    $totalDate -= (((Int)($totalDate / 86400)) * 86400);

                    if ($days < 0) {
                        $days = 0;
                    }
                    $showTotal .= '- ' . $days . ' д.';
                }

                return $showTotal;
            }
                return '-';
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
];
echo GridView::widget([
'dataProvider' => $dataProvider,
'summary' => false,
'emptyText' => '',
'panel' => [
'type' => 'primary',
'heading' => 'Список договоров по дате окончания заключенного договора',
'before' => false,
'footer' => false,
'after' => false,
],
'resizableColumns' => false,
'hover' => false,
'striped' => false,
'export' => false,

'filterSelector' => '.ext-filter',
'beforeHeader' => [
    [
        'columns' => [
            [
                'content' => Html::a('Выгрузить', ['company/tendersexcel'], ['class' => 'pull-right btn btn-warning btn-sm', 'style' => 'margin-right:10px;']),
                'options' => [
                    'style' => 'vertical-align: middle',
                    'colspan' => count($columns),
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
                    'colspan' => count($columns),
                ]
            ]
        ],
        'options' => ['class' => 'kv-group-header'],
    ],
],
'columns' => $columns,
]);
?>

