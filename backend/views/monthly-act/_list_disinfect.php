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
use common\models\ActExport;
use common\models\Company;
use common\models\ActData;

$script = <<< JS

    // проверка нужен ли пересчет
    var checkRemoveRows = false;

    // Заполнение таблицы с информацией
    
    // Общая сумма
    var totalPayed = 0;
    $('td[data-col-seq=4]').each(function() {
        
        // Удаляем строки с нулевыми ценами
        if($(this).text() == '' || $(this).text() == '0') {
            $(this).parent().remove();
            checkRemoveRows = true;
        } else {
        totalPayed += Number($(this).text());
        }
        
    });

    var totalPayedText = totalPayed.toString();
    totalPayedText = totalPayedText.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');

    $(".totalSum").text(totalPayedText);
    // Общая сумма
    
    // Не оплачено
    var arrDataKey = [];
    var i = 0;
    
    $('td select[data-paymentstatus=0]').each(function() {
        arrDataKey[i] = $(this).parent().parent().attr("data-key");
        i++;
    });
    
    var noPayed = 0;
    for (var j=0; j < arrDataKey.length; j++) {
        noPayed += Number($("tr[data-key=" + arrDataKey[j] + "] td[data-col-seq=4]").text());
    }

    var noPayedText = noPayed.toString();
    noPayedText = noPayedText.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');

    $(".toPay").text(noPayedText);
    // Не оплачено
    
    // оплатили
    var payedSum = totalPayed - noPayed;

    var payedSumText = payedSum.toString();
    payedSumText = payedSumText.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
    
    $(".payed").text(payedSumText);
    // оплатили


    $(".totalActs").text($('tr[data-key]').length);
    $(".noSigned").text($('td[data-col-seq=act_status] select[data-actstatus=0]').length);
    $(".sendedScan").text($('td[data-col-seq=act_status] select[data-actstatus=1]').length);
    $(".signedScan").text($('td[data-col-seq=act_status] select[data-actstatus=3]').length);
    $(".sendedOriginal").text($('td[data-col-seq=act_status] select[data-actstatus=2]').length);
    $(".signed").text($('td[data-col-seq=act_status] select[data-actstatus=4]').length);
    $(".noAct").text($('td[data-col-seq=act_status] select[data-actstatus=5]').length);
    // Заполнение таблицы с информацией
    
    // пересчет нумерации
    if(checkRemoveRows == true) {
        var indexNumbers = 1;
        
        $('td[data-col-seq=0]').each(function() {
        
            $(this).text(indexNumbers);
            indexNumbers++;
        
        });
        
    }

JS;
$this->registerJs($script, \yii\web\View::POS_READY);

$GLOBALS['company'] = $company;
$GLOBALS['type'] = $type;

echo GridView::widget([
    'id'               => 'monthly-act-grid',
    'dataProvider'     => $dataProvider,
    'filterModel' => $searchModel,
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
                        'colspan' => $GLOBALS['company'] ? 11 : 12,
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
            'attribute'         => 'client_name',
            'header' => 'Клиент',
            'format' => 'raw',
            //'group'             => true,  // enable grouping
            //'options'           => ['class' => 'kv-grouped-header'],
            //'groupedRow'        => true,  // enable grouping
            //'groupOddCssClass'  => 'kv-group-header',  // configure odd group cell css class
            //'groupEvenCssClass' => 'kv-group-header', // configure even group cell css class
            'value' => function ($data) {

                if(isset($data->client)) {
                    return '<span class="showStatus">' . $data->client->name . "</span>";
                } else {
                    return 'error';
                }

            },
            'filter' => true,
        ],
        [
            'attribute' => 'client_city',
            'header' => 'Город',
            'value'  => function ($data) {
                return isset($data->client) ? $data->client->address : 'error';
            },
            'filter' => true,

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
                return (int) $data->profit;
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
            'attribute' => 'prepayment',
            'value' => function ($data) {
                if($data->prepayment) {
                    return $data->prepayment;
                } else {
                    return '-';
                }
            },
        ],
        [
            'header' => 'НДС',
            'visible' => !($GLOBALS['company']),
            'value' => function ($data) {
                $CompanyInfo = CompanyInfo::findOne(['company_id' => $data->client->id]);

                $nds = '-';

                if(isset($CompanyInfo->nds)) {
                    if($CompanyInfo->nds == 1) {
                        $nds = 'НДС';
                    }
                }

                return $nds;
            },
        ],
        [
            'header' => 'Дни до<br />оплаты',
            //'attribute' => 'payment_date',
            'value' => function ($data) {

                $company_id = 0;
                $company = $GLOBALS['company'];

                if($company) {
                    $company_id = $data->client_id;
                } else {

                    $arrCompany = Company::find()->where(['name' => $data->client->name])->select('id')->column();

                    if(count($arrCompany) > 0) {
                        if (isset($arrCompany[0])) {
                            $company_id = $arrCompany[0];
                        }
                    }

                }

                $getPay = CompanyInfo::find()->where(['company_id' => $data->client_id])->select('pay')->column();

                if (isset($getPay[0])) {

                    $arrPayData = explode(':', $getPay[0]);

                    if((count($arrPayData) > 1) && ($arrPayData[0] != 4)) {

                        $selpayDay = $arrPayData[1];

                        $dataFromAct = '';

                        if($company) {
                            $dataFromAct = date('Y-m-t', $data->created_at) . ' 00:00:01';
                            $dataFromAct = strtotime($dataFromAct);
                        } else {
                            $type = $GLOBALS['type'];

                            $dataExpl = '';

                            if (isset(Yii::$app->request->get('MonthlyActSearch')['act_date'])) {
                                $dataExpl = (string) Yii::$app->request->get('MonthlyActSearch')['act_date'];

                                $dataExplArr = explode('-', $dataExpl);

                                if (($dataExplArr[0] < 10) && (mb_strlen($dataExplArr[0]) == 1)) {
                                    $dataExpl = 0 . $dataExpl;
                                }

                            } else {
                                $dataExpl = date('m-Y', strtotime("-1 month"));
                            }

                            $resActLoad = ActExport::find()->where(['type' => $type, 'company' => $company, 'period' => $dataExpl, 'company_id' => $company_id])->andWhere(['like', 'name', '_Акт_'])->select('data_load')->column();

                            if (count($resActLoad) > 0) {
                                if (isset($resActLoad[0])) {
                                    $dataFromAct = $resActLoad[0];
                                }
                            }

                        }

                        if((($company) && ($dataFromAct != '') && (time() > $dataFromAct)) || ((!$company) && ($dataFromAct != '') && (time() > $dataFromAct))) {

                            if (($arrPayData[0] == 0) || ($arrPayData[0] == 2)) {

                                $dayOld = 0;
                                $timeAct = $dataFromAct;

                                while ($timeAct < time()) {

                                    $timeAct += 86400;

                                    if ((date('w', $timeAct) != 0) && (date('w', $timeAct) != 6)) {
                                        $dayOld++;
                                    }

                                }

                                return ($selpayDay - $dayOld) >= 1 ? ((int)($selpayDay - $dayOld)) : 0;

                            } else if (($arrPayData[0] == 1) || ($arrPayData[0] == 3)) {
                                return ($selpayDay - ((time() - $dataFromAct) / 86400)) >= 1 ? ((int)($selpayDay - ((time() - $dataFromAct) / 86400))) : 0;
                            }

                        } else {
                            return '-';
                        }

                    } else {
                        return '-';
                    }

                } else {
                    return '-';
                }

            },
        ],
        [
            'header' => 'Номер',
            'contentOptions' => ['class' => 'numberAct'],
            'value' => function ($data) {

                $type = $GLOBALS['type'];
                $company = $GLOBALS['company'];

                $name = $data->client->name;
                $name = trim($name);
                $name = str_replace('+', '', $name);
                $name = str_replace('_', '\_', $name);
                $name = str_replace(' ', '\_', $name);
                $name = str_replace('"', '', $name);
                $name = str_replace('"', '', $name);
                $name = str_replace('«', '', $name);
                $name = str_replace('»', '', $name);

                $period = '';

                if(isset(Yii::$app->request->queryParams['MonthlyActSearch']['act_date'])) {
                    $period = '0' . Yii::$app->request->queryParams['MonthlyActSearch']['act_date'];
                } else {
                    $period = date("m-Y", strtotime("-1 month"));
                }

                $numAct = ActData::find()->where(['AND', ['type' => $type], ['company' => $company], ['period' => $period]])->andWhere('`name` LIKE "%' . $name . '%"')->select('number')->column();

                if(isset($numAct)) {

                    if(count($numAct) > 0) {

                        if(isset($numAct[0])) {
                            return $numAct[0];
                        } else {
                            return '-';
                        }

                    } else {
                        return '-';
                    }

                } else {
                    return '-';
                }

            }
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
?>