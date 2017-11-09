<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use common\models\Company;

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
                'attribute' => 'purchase_status',
                'header' => 'Статус<br />закупки',
                'format' => 'raw',
                'vAlign'=>'middle',
                'value' => function ($data) {

                    $arrPurchstatus = [1 => 'Рассматриваем', 2 => 'Отказались', 3 => 'Не успели', 4 => 'Подаёмся', 5 => 'Подались', 6 => 'Отказ заказчика', 7 => 'Победили', 8 => 'Заключен договор', 9 => 'Проиграли'];

                    if ($data->purchase_status) {
                        return $arrPurchstatus[$data->purchase_status];
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

                    $userList = ['2' => 'Алёна', '3' => 'Денис'];

                    $arrUserTend = explode(', ', $data->user_id);
                    $UserTendText = '';

                    if (count($arrUserTend) > 1) {

                        for ($i = 0; $i < count($arrUserTend); $i++) {
                            if(isset($userList[$arrUserTend[$i]])) {
                                $UserTendText .= $userList[$arrUserTend[$i]] . '<br />';
                            }
                        }

                    } else {

                        try {
                            if(isset($userList[$data->user_id])) {
                                $UserTendText = $userList[$data->user_id];
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
                'attribute' => 'customer',
                'vAlign'=>'middle',
                'header' => 'Заказчик',
                'value' => function ($data) {

                    if ($data->customer) {
                        return $data->customer;
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

                    $arrMethods = [1 => 'Электронный аукцион (открытый)', 2 => 'Электронный аукцион (закрытый)', 3 => 'Запрос котировок (открытый)', 4 => 'Запрос предложений (открытый)', 5 => 'Открытый редукцион', 6 => 'Запрос цен', 7 => 'Открытый аукцион'];

                    if ($data->method_purchase) {
                        return $arrMethods[$data->method_purchase];
                    } else {
                        return '-';
                    }
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

                    $ServicesList = ['2' => 'Мойка', '3' => 'Сервис', '4' => 'Шиномонтаж', '5' => 'Дезинфекция', '7' => 'Стоянка', '8' => 'Эвакуация'];

                    $arrServices = explode(', ', $data->service_type);
                    $serviceText = '';

                    if (count($arrServices) > 1) {

                        for ($i = 0; $i < count($arrServices); $i++) {
                            if(isset($ServicesList[$arrServices[$i]])) {
                                $serviceText .= $ServicesList[$arrServices[$i]] . '<br />';
                            }
                        }

                    } else {

                        try {
                            if(isset($ServicesList[$data->service_type])) {
                                $serviceText = $ServicesList[$data->service_type];
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
