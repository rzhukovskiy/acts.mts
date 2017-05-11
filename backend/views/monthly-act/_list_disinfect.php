<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\MonthlyActSearch
 * @var $admin boolean
 */
use common\models\MonthlyAct;
use kartik\grid\GridView;
use common\models\CompanyInfo;
use yii\helpers\Html;

echo GridView::widget([
    'id'               => 'monthly-act-grid',
    'dataProvider'     => $dataProvider,
    'summary'          => false,
    'emptyText'        => '',
    'panel'            => [
        'type'    => 'primary',
        'heading' => 'Сводные акты по ' . \common\models\Service::$listType[$type]['ru'],
        'before'  => false,
        'footer'  => false,
        'after'   => false,
    ],
    'resizableColumns' => false,
    'hover'            => false,
    'striped'          => false,
    'export'           => false,
    'showPageSummary'  => false,
    'filterSelector'   => '.ext-filter',
    'beforeHeader'     => [
        [
            'columns' => [
                [
                    'content' => $filters,
                    'options' => [
                        'style'   => 'vertical-align: middle',
                        'colspan' => 9,
                        'class'   => 'kv-grid-group-filter',
                    ],
                ]
            ],
            'options' => ['class' => 'extend-header'],
        ],
    ],
    'layout'           => '{items}',
    'columns'          => [
        [
            'header'      => '№',
            'class'       => 'kartik\grid\SerialColumn',
            'pageSummary' => 'Всего',
            'mergeHeader' => false,
            'width'       => '30px',
            'vAlign'      => GridView::ALIGN_TOP,
        ],
        [
            'attribute'         => 'client_id',
            //'group'             => true,  // enable grouping
            //'options'           => ['class' => 'kv-grouped-header'],
            //'groupedRow'        => true,  // enable grouping
            //'groupOddCssClass'  => 'kv-group-header',  // configure odd group cell css class
            //'groupEvenCssClass' => 'kv-group-header', // configure even group cell css class
            'value'             => function ($data) {
                return isset($data->client) ? $data->client->name : 'error';
            },
        ],
        [
            'header' => 'Город',
            'value'  => function ($data) {
                return isset($data->client) ? $data->client->address : 'error';
            },

        ],
        [
            'attribute' => 'service_id',
            'value'     => function ($data) {
                return $data->service->description;
            },
            'format'    => 'html',
        ],
        [
            'attribute'       => 'profit',
            'value'           => function ($data) {
                return $data->profit;
            },
            'pageSummary'     => true,
            'pageSummaryFunc' => GridView::F_SUM,
            'format'          => 'html',
        ],
        'payment_status' => [
            'attribute'      => 'payment_status',
            'value'          => function ($model, $key, $index, $column) {
                return Html::activeDropDownList($model,
                    'payment_status',
                    MonthlyAct::$paymentStatus,
                    [
                        'class'   => 'form-control change-payment_status',
                        'data-id' => $model->id,
						'data-paymentStatus' => $model->payment_status,
						MonthlyAct::payDis($model->payment_status)=>'disabled',
                    ]

                );
            },
            'filter'         => false,
            'format'         => 'raw',
            'contentOptions' => function ($model) {
                return [
                    'class' => MonthlyAct::colorForPaymentStatus($model->payment_status),
                    'style' => 'min-width: 100px'
                ];
            },
        ],
        [
            'header' => 'Дни до оплаты',
            //'attribute' => 'payment_date',
            'value' => function ($data) {

                $getPay = CompanyInfo::find()->where(['company_id' => $data->client_id])->select('pay')->column();

                if (isset($getPay[0])) {

                    $arrPayData = explode(':', $getPay[0]);

                    if((count($arrPayData) > 1) && ($arrPayData[0] != 4)) {

                        $selpayDay = $arrPayData[1];

                        if (($arrPayData[0] == 0) || ($arrPayData[0] == 2)) {

                            $dayOld = 0;
                            $timeAct = $data->created_at;

                            while ($timeAct < time()) {

                                $timeAct += 86400;

                                if ((date('w', $timeAct) != 0) && (date('w', $timeAct) != 6)) {
                                    $dayOld++;
                                }

                            }

                            return ($selpayDay - $dayOld) >= 1 ? ((int)($selpayDay - $dayOld)) : 0;

                        } else if (($arrPayData[0] == 1) || ($arrPayData[0] == 3)) {
                            return ($selpayDay - ((time() - $data->created_at) / 86400)) >= 1 ? ((int)($selpayDay - ((time() - $data->created_at) / 86400))) : 0;
                        }

                    } else {
                        return '-';
                    }

                } else {
                    return '-';
                }

            },
        ],
        'act_status'     => [
            'attribute'      => 'act_status',
            'value'          => function ($model, $key, $index, $column) {
                return Html::activeDropDownList($model,
                    'act_status',
                    MonthlyAct::passActStatus($model->act_status),
                    [
                        'class'   => 'form-control change-act_status',
                        'data-id' => $model->id,
						'data-actStatus' => $model->act_status,
                        MonthlyAct::actDis($model->act_status)=>'disabled',
                    ]);
            },
            'contentOptions' => function ($model) {
                return ['class' => MonthlyAct::colorForStatus($model->act_status), 'style' => 'min-width: 160px'];
            },
            'filter'         => false,
            'format'         => 'raw',

        ],
        /*
        'img'            => [
            'attribute' => 'img',
            'value'     => function ($data) {
                return $data->getImageList();
            },
            'filter'    => false,
            'format'    => 'raw'
        ],
        */
        [
            'class'          => 'yii\grid\ActionColumn',
            'template'       => '{update}{call}',
            'contentOptions' => ['style' => 'min-width: 50px'],
            'visibleButtons' => $visibleButton,
            'buttons'        => [
                'update' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-search"></span>',
                        ['/monthly-act/detail', 'id' => $model->id]);
                },
                'call'   => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-earphone"></span>',
                        ['/company/member', 'id' => $model->client_id]);
                },
            ]
        ],
    ],
]);
