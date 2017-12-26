<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\MonthlyActSearch
 * @var $admin boolean
 */
use common\models\MonthlyAct;
use kartik\grid\GridView;
use common\models\CompanyInfo;
use common\models\ActExport;
use common\models\Company;
use common\models\ActData;
use common\models\ExpenseCompany;
use yii\bootstrap\Tabs;
use kartik\date\DatePicker;

$script = <<< JS

// формат числа
window.onload=function(){
        var formatSum3 = $('td[data-col-seq="profit"]');
        $(formatSum3).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
   var formatSum3a = $('.kv-page-summary-container td:eq(3)');
  $(formatSum3a).each(function (id, value) {
       var thisId = $(this);
       thisId.text(thisId.text().replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 "));
});
};
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

$this->title = 'Расходы на мойку';

$requestType = Yii::$app->request->get('type');

$items = [];
$i = 0;
foreach ($listType as $type_id => $typeData) {

    // В меню добавление перед прочим
    if($i == 10) {
            $items[] = [
            'label' => 'Мойка',
            'url' => ["/expense/wash"],
            'active' => Yii::$app->controller->id == 'expense' && Yii::$app->controller->action->id == 'wash',
        ];
        $items[] = [
            'label' => 'Сервис',
            'url' => ["/expense/service"],
            'active' => Yii::$app->controller->id == 'expense' && Yii::$app->controller->action->id == 'service',
        ];
        $items[] = [
            'label' => 'Шиномонтаж',
            'url' => ["/expense/tires"],
            'active' => Yii::$app->controller->id == 'expense' && Yii::$app->controller->action->id == 'tires',
        ];
    }

    $items[] = [
        'label' => ExpenseCompany::$listType[$type_id]['ru'],
        'url' => ["/expense/statexpense", 'type' => $type_id],
        'active' => Yii::$app->controller->id == 'expense' && $requestType == $type_id,
    ];
    $i++;
}

$items[] = [
    'label' => 'Общее',
    'url' => ["/expense/stattotal"],
    'active' => Yii::$app->controller->id == 'expense' && Yii::$app->controller->action->id == 'stattotal',
];

echo Tabs::widget([
    'encodeLabels' => false,
    'items' => $items,
]);

$GLOBALS['company'] = $company;
$GLOBALS['type'] = $type;
$GLOBALS['period'] = $searchModel->act_date;

//Настройки фильтров
$filters = 'Период: ' . DatePicker::widget([
        'model' => $searchModel,
        'attribute' => 'act_date',
        'type' => DatePicker::TYPE_INPUT,
        'language' => 'ru',
        'pluginOptions' => [
            'autoclose' => true,
            'changeMonth' => true,
            'changeYear' => true,
            'showButtonPanel' => true,
            'format' => 'm-yyyy',
            'maxViewMode' => 2,
            'minViewMode' => 1,
            //'endDate'         => '-1m'
        ],
        'options' => [
            'class' => 'form-control ext-filter',
        ]
    ]);
//Настройки кнопок
?>

<div class="panel panel-primary">
    <div class="panel-heading">
Список расходов
</div>
    <div class="panel-body">
<?php

$columns = [
    [
        'header' => '№',
        'class' => 'kartik\grid\SerialColumn'
    ],
    'client' => [
        'attribute' => 'client_name',
        'header' => 'Клиент',
        'pageSummary' => 'Всего',
        'format' => 'raw',
        'value' => function ($data) {

            if(isset($data->client)) {
                return '<span class="showStatus">' . $data->client->name . "</span>";
            } else {
                return 'error';
            }

        },
        'filter' => true,
    ],
    'city' => [
        'attribute' => 'client_city',
        'header' => 'Город',
        'value' => function ($data) {
            return isset($data->client) ? $data->client->address : 'error';
        },
        'filter' => true,
    ],
    'profit' => [
        'attribute' => 'profit',
        'pageSummary' => true,
        'pageSummaryFunc' => GridView::F_SUM,
        'format' => 'html',
        'value' => function ($data) {

            $intVal = (Int) $data->profit;
            $checkVal = $data->profit - $intVal;

            if($checkVal > 0) {
                return $data->profit;
            } else {
                return $intVal;
            }

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

            // Период
            $period = '';

            if(isset(Yii::$app->request->queryParams['MonthlyActSearch']['act_date'])) {

                $dataFilter = explode('-', Yii::$app->request->queryParams['MonthlyActSearch']['act_date']);

                if($dataFilter[0] > 9) {
                    $period = Yii::$app->request->queryParams['MonthlyActSearch']['act_date'];
                } else {
                    $period = '0' . Yii::$app->request->queryParams['MonthlyActSearch']['act_date'];
                }

            } else {
                $period = date("m-Y", strtotime("-1 month"));
            }
            // Период

            $numAct = '';

            // проверяем и выводим мфп
            $posMFP = strpos($name, "МФП");

            if($posMFP === false) {
                $numAct = ActData::find()->where(['AND', ['type' => $type], ['company' => $company], ['period' => $period]])->andWhere('`name` LIKE "%' . $name . '%"')->select('number')->column();
            } else {
                $numAct = ActData::find()->where(['AND', ['type' => $type], ['company' => $company], ['period' => $period]])->andWhere('`name` LIKE "%МФП%"')->select('number')->column();
            }

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

];

echo GridView::widget([
    'id' => 'monthly-act-grid',
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'summary' => false,
    'showPageSummary' => true,
    'emptyText' => '',
    'beforeHeader' => [
        [
            'columns' => [
                [
                    'content' => $filters,
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
    'resizableColumns' => false,
    'hover' => false,
    'striped' => false,
    'export' => false,
    'filterSelector' => '.ext-filter',

    'layout' => '{items}',
    'columns' => $columns,
]);
?>
    </div>
</div>
