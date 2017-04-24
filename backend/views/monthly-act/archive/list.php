<?php

use common\assets\CanvasJs\CanvasJsAsset;
use common\models\Company;
use yii\bootstrap\Html;
use common\components\DateHelper;

/**
 * @var $this yii\web\View
 * @var $group string
 * @var $type integer
 * @var $searchModel \frontend\models\search\ActSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $totalServe float
 * @var $totalProfit float
 * @var $totalExpense float
 * @var $title string
 */


$this->title = "Архив актов";
$filters = 'Выбор компании: ' . Html::activeDropDownList($searchModel,
        'client_id',
        Company::dataDropDownList($type, true, ['name' => SORT_ASC], true),
        ['prompt' => 'все', 'class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
$filters .= \frontend\widgets\datePeriod\DatePeriodWidget::widget([
    'model'        => $searchModel,
    'dateFromAttr' => 'dateFrom',
    'dateToAttr'   => 'dateTo',
]);
/**
 * Конец виджета
 */

?>
<?php
echo $this->render('_tabs',
    [
        'type'        => $type,
        'listType'    => $listType,
        'searchModel' => $searchModel
    ]);
?>
<?php
echo \kartik\grid\GridView::widget([
    'id'               => 'monthly-act-grid',
    'dataProvider'     => $dataProvider,
    'showPageSummary'  => false,
    'summary'          => false,
    'emptyText'        => '',
    'panel'            => [
        'type'    => 'primary',
        'heading' => 'Архив актов по ' . \common\models\Company::$listType[$type]['ru'],
        'before'  => false,
        'footer'  => false,
        'after'   => false,
    ],
    'resizableColumns' => false,
    'hover'            => false,
    'striped'          => false,
    'export'           => false,
    'filterSelector'   => '.ext-filter',
    'beforeHeader'     => [
        [
            'columns' => [
                [
                    'content' => $filters,
                    'options' => [
                        'colspan' => 8,
                        'style'   => 'vertical-align: middle',
                        'class'   => 'kv-grid-group-filter period-select'
                    ],
                ],
            ],
            'options' => ['class' => 'filters extend-header'],
        ],
        [
            'columns' => [
                [
                    'content' => '&nbsp',
                    'options' => [
                        'colspan' => 8,
                    ]
                ]
            ],
            'options' => ['class' => 'kv-group-header'],
        ],
    ],
    'columns'          => [
        [
            'header'        => '№',
            'class'         => 'yii\grid\SerialColumn',
            'footer'        => 'Итого:',
            'footerOptions' => ['style' => 'font-weight: bold'],
        ],
        [
            'attribute' => 'client_id',
            'label'     => 'Клиент',
            'content'   => function ($data) use ($type) {
                return Html::a($data->client->name,
                    \yii\helpers\Url::to([
                        '/monthly-act/archive',
                        'type'                        => $type,
                        'MonthlyActSearch[client_id]' => $data->client_id
                    ]));
            },
            'format'    => 'raw'
        ],
        [
            'attribute' => 'act_date',
            'label'     => 'Дата',
            'content'   => function ($data) {

    // Фикс ошибки вывода даты на англ языке
    $dataArr = explode('-', $data->dateFix());
    if(count($dataArr) == 3) {

        $monthName = [
            1 => ['Январь', 'Января', 'Январе'],
            2 => ['Февраль', 'Февраля', 'Феврале'],
            3 => ['Март', 'Марта', 'Марте'],
            4 => ['Апрель', 'Апреля', 'Апреле'],
            5 => ['Май', 'Мая', 'Мае'],
            6 => ['Июнь', 'Июня', 'Июне'],
            7 => ['Июль', 'Июля', 'Июле'],
            8 => ['Август', 'Августа', 'Августе'],
            9 => ['Сентябрь', 'Сентября', 'Сентябре'],
            10 => ['Октябрь', 'Октября', 'Октябре'],
            11 => ['Ноябрь', 'Ноября', 'Ноябре'],
            12 => ['Декабрь', 'Декабря', 'Декабре']
        ];

        $mountID = (int) $dataArr[1];
        return $monthName[$mountID][0] . ' ' . $dataArr[0];
    } else {
        return Yii::$app->formatter->asDate($data->dateFix(), 'LLLL yyyy');
    }

            },
            'visible'   => $searchModel->client_id
        ],
        [
            'attribute'         => 'type_id',
            'label'             => 'Услуга',
            'group'             => true,  // enable grouping
            'options'           => ['class' => 'kv-grouped-header'],
            'groupedRow'        => true,  // enable grouping
            'groupOddCssClass'  => 'kv-group-header',  // configure odd group cell css class
            'groupEvenCssClass' => 'kv-group-header', // configure even group cell css class
            'content'           => function ($data) {
                return Company::$listType[$data->type_id]['ru'];
            },
            'visible'           => $searchModel->client_id && $searchModel->type_id == Company::TYPE_OWNER
        ],
        [
            'attribute' => 'service_id',
            'label'     => 'Услуга',
            'content'   => function ($data) {
                return $data->service->description;
            },
            'visible'   => $searchModel->client_id && $searchModel->type_id == Company::TYPE_DISINFECT
        ],
        [
            'attribute' => 'number',
            'label'     => 'Номер',
            'content'   => function ($data) {
                return $data->number;
            },
            'visible'   => $searchModel->client_id && $searchModel->type_id == Company::TYPE_SERVICE
        ],
        'profit' => [
            'attribute'     => 'profit',
            'value'         => function ($data) {
                return $data->profit;
            },
            'format'        => 'html',
            'footer'        => $totalProfit,
            'footerOptions' => ['style' => 'font-weight: bold'],
            'visible'       => $searchModel->client_id
        ],


    ],
]);
?>
