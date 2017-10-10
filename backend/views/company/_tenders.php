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
                'attribute' => 'service_type',
                'header' => 'Закупаемы<br />услуги',
                'format' => 'raw',
                'vAlign'=>'middle',
                'value' => function ($data) {

                    $ServicesList = Company::$listType;

                    $arrServices = explode(', ', $data->service_type);
                    $serviceText = '';

                    if (count($arrServices) > 1) {

                        for ($i = 0; $i < count($arrServices); $i++) {
                            if(isset($ServicesList[$arrServices[$i]]['ru'])) {
                                $serviceText .= $ServicesList[$arrServices[$i]]['ru'] . '<br />';
                            }
                        }

                    } else {

                        try {
                            if(isset($ServicesList[$data->service_type]['ru'])) {
                                $serviceText = $ServicesList[$data->service_type]['ru'];
                            }
                        } catch (\Exception $e) {
                            $serviceText = '-';
                        }

                    }

                    return $serviceText;

                },
            ],
            [
                'attribute' => 'date_request_start',
                'vAlign'=>'middle',
                'header' => 'Начало подачи<br />заявки',
                'value' => function ($data) {
                    return date('d.m.Y', $data->date_request_start);
                },
            ],
            [
                'attribute' => 'date_request_end',
                'vAlign'=>'middle',
                'header' => 'Окончание подачи<br />заявки',
                'value' => function ($data) {
                    return date('d.m.Y H:i', $data->date_request_end);
                },
            ],
            [
                'attribute' => 'time_bidding_start',
                'vAlign'=>'middle',
                'header' => 'Дата и время<br />начала торгов',
                'value' => function ($data) {
                    return date('d.m.Y H:i', $data->time_bidding_start);
                },
            ],
            [
                'attribute' => 'time_bidding_end',
                'vAlign'=>'middle',
                'header' => 'Дата и время<br />подведения торгов',
                'value' => function ($data) {
                    return date('d.m.Y H:i', $data->time_bidding_end);
                },
            ],
            [
                'attribute' => 'term_contract',
                'vAlign'=>'middle',
                'header' => 'Срок<br />договора',
                'value' => function ($data) {
                    return date('d.m.Y', $data->term_contract);
                },
            ],
            [
                'header' => 'Осталось',
                'vAlign'=>'middle',
                'value' => function ($data) {

                    $timeNow = time();

                    $showTotal = '';

                    if($data->term_contract > $timeNow) {

                        $totalDate = $data->term_contract - $timeNow;

                        $days = ((Int) ($totalDate / 86400));
                        $totalDate -= (((Int) ($totalDate / 86400)) * 86400);

                        if($days < 0) {
                            $days = 0;
                        }

                        $hours = (round($totalDate / 3600));
                        $totalDate -= (round($totalDate / 3600) * 3600);

                        if($hours < 0) {
                            $hours = 0;
                        }

                        $minutes = (round($totalDate / 60));

                        if($minutes < 0) {
                            $minutes = 0;
                        }

                        $showTotal .= $days . ' д.';
                        $showTotal .= ' ' . $hours . ' ч.';
                        $showTotal .= ' ' . $minutes . ' м.';

                    } else {
                        $totalDate = $timeNow - $data->term_contract;

                        $days = ((Int) ($totalDate / 86400));
                        $totalDate -= (((Int) ($totalDate / 86400)) * 86400);

                        if($days < 0) {
                            $days = 0;
                        }

                        $hours = (round($totalDate / 3600));
                        $totalDate -= (round($totalDate / 3600) * 3600);

                        if($hours < 0) {
                            $hours = 0;
                        }

                        $minutes = (round($totalDate / 60));

                        if($minutes < 0) {
                            $minutes = 0;
                        }

                        $showTotal .= '- ' . $days . ' д.';
                        $showTotal .= ' ' . $hours . ' ч.';
                        $showTotal .= ' ' . $minutes . ' м.';
                    }

                    return $showTotal;
                },
            ],
            [
                'attribute' => 'final_price',
                'header' => 'Окончательная цена<br />контракта',
                'vAlign'=>'middle',
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
