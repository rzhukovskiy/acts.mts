<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use common\models\Company;
use common\models\TenderLists;

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
$GLOBALS['usersList'] = $usersList;

$arrsite = [];
if (isset($arrLists[8])){
    $arrsite = $arrLists[8];
    asort($arrsite);
}
$arrtype = [];
if (isset($arrLists[9])){
    $arrtype = $arrLists[9];
    asort($arrtype);
}
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Контроль денежных средств
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', ['company/newcontroltender'], ['class' => 'btn btn-success btn-sm']) ?>
        </div>
    </div>
    <div class="panel-body">
        <?php

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
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
                    'attribute' => 'user_id',
                    'header' => 'Сотрудник',
                    'filter' => Html::activeDropDownList($searchModel, 'user_id', isset($GLOBALS['usersList']) ? $GLOBALS['usersList'] : [], ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
                    'pageSummary' => 'Всего',
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'value' => function ($data) {

                        if ($data->user_id) {
                            return $GLOBALS['usersList'][$data->user_id];
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'attribute' => 'send',
                    'header' => 'Отправили',
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_SUM,
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'value' => function ($data) {

                        if ($data->send) {
                            return $data->send;
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'date_send',
                    'vAlign'=>'middle',
                    'filter' => false,
                    'header' => 'Дата отправки',
                    'value' => function ($data) {

                        if ($data->date_send) {
                            return date('d.m.Y', $data->date_send);
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'platform',
                    'vAlign'=>'middle',
                    'header' => 'Площадка',
                    'value' => function ($data) {

                        if ($data->platform) {
                            return $data->platform;
                        } else {
                            return '-';
                        }

                    },
                ],

                [
                    'attribute' => 'customer',
                    'header' => 'Заказчик',
                    'format' => 'raw',
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
                    'attribute' => 'purchase',
                    'vAlign'=>'middle',
                    'header' => 'Закупка',
                    'value' => function ($data) {

                        if ($data->purchase) {
                            return $data->purchase;
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'type_payment',
                    'header' => 'Тип платежа',
                    'filter' => Html::activeDropDownList($searchModel, 'type_payment', isset($arrtype) ? $arrtype : [], ['class' => 'form-control', 'prompt' => 'Все типы']),
                    'format' => 'raw',
                    'vAlign'=>'middle',
                    'value' => function ($data) {

                        if ($data->type_payment) {
                            return $GLOBALS['arrLists'][9][$data->type_payment];
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'return',
                    'vAlign'=>'middle',
                    'header' => 'Возврат',
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_SUM,
                    'value' => function ($data) {

                        if ($data->return) {
                            return $data->return;
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'date_return',
                    'vAlign'=>'middle',
                    'filter' => false,
                    'header' => 'Дата возврата',
                    'value' => function ($data) {

                        if ($data->date_return) {
                            return date('d.m.Y', $data->date_return);
                        } else {
                            return '-';
                        }

                    },
                ],
                [
                    'attribute' => 'balance_work',
                    'vAlign'=>'middle',
                    'header' => 'Остаток в работе',
                    'pageSummary' => true,
                    'pageSummaryFunc' => GridView::F_SUM,
                    'value' => function ($data) {

                        if ($data->balance_work) {
                            return $data->balance_work;
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
                                ['/company/fullcontroltender', 'id' => $model->id]);
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
