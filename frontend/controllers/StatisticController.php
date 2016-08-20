<?php

namespace frontend\controllers;

use common\models\Company;
use frontend\models\Act;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use frontend\models\search\ActSearch;
use common\models\Service;

class StatisticController extends Controller
{
    private $colors = [
        2 => [
            'border' => 'rgb(91, 192, 222)',
            'bg' => 'rgba(91, 192, 222, .2)',
        ],
        3 => [
            'border' => 'rgb(92, 184, 92)',
            'bg' => 'rgba(92, 184, 92, .2)',
        ],
        4 => [
            'border' => 'rgb(240, 173, 78)',
            'bg' => 'rgba(240, 173, 78, .2)',
        ],
        5 => [
            'border' => 'rgb(217, 83, 79)',
            'bg' => 'rgba(217, 83, 79, .2)',
        ],
        6 => [
            'border' => 'rgb(119, 119, 119)',
            'bg' => 'rgba(119, 119, 119, .2)'
        ]
    ];
    private $month = ["1" => "Январь", "2" => "Февраль", "3" => "Март", "4" => "Апрель", "5" => "Май", "6" => "Июнь", "7" => "Июль", "8" => "Август", "9" => "Сентябрь", "10" => "Октябрь", "11" => "Ноябрь", "12" => "Декабрь"];

    /**
     * @param null $type
     * @return string
     */
    public function actionList($type = null)
    {
        $searchModel = new ActSearch();
        $searchModel->scenario = 'search_by_date';
        $dataProvider = $searchModel->searchByDate(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        if (!empty($type)) {
            $dataProvider->query
                ->andWhere(['type_id' => $type]);

            $this->view->title = Company::$listType[$type][ru] . '. Статистика';
        }

        $dataProvider->query
            ->addSelect('served_at')
            ->addSelect('COUNT({{%act}}.id) AS countServe')
            ->addSelect('SUM(expense) as expense')
            ->addSelect('SUM(profit) as profit')
            ->addSelect('partner_id')
            ->groupBy('partner_id')
            ->orderBy('profit DESC')
            ->with(['partner', 'client']);

        $models = $dataProvider->getModels();
        $totalProfit = 0;
        $totalServe = 0;
        $totalExpense = 0;
        $chartData = [];
        foreach ($models as $index => $model) {
            $chartData['labels'][] = $model->partner->name;
            $chartData['datasets'][0]['data'][] = $model->profit;
            $chartData['datasets'][0]['backgroundColor'][] = $this->generateRandomRgba(true);
            $totalProfit += $model->profit;
            $totalServe += $model->countServe;
            $totalExpense += $model->expense;
        }

        return $this->render('list', [
            'type' => $type,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'chartData' => $chartData,
            'totalProfit' => number_format($totalProfit, 0, '', ' '),
            'totalServe' => number_format($totalServe, 0, '', ' '),
            'totalExpense' => number_format($totalExpense, 0, '', ' '),
        ]);
    }

    public function actionTotal($type = null)
    {
        $searchModel = new ActSearch();
        $searchModel->scenario = 'search_by_date';
        $dataProvider = $searchModel->searchByDate(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        if (!is_null($type))
            $dataProvider->query->andWhere(['type_id' => $type]);

        // группировать по типу, суммировать затраты, прибыль
        $dataProvider->query
            ->groupBy('service_type')
            ->addSelect('COUNT({{%act}}.id) AS countServe')
            ->addSelect('SUM(expense) as expense')
            ->addSelect('SUM(profit) as profit')
            ->addSelect('service_type')
            ->orderBy('profit DESC')
            ->with(['partner', 'client', 'type']);

        $models = $dataProvider->getModels();
        $totalProfit = 0;
        $totalServe = 0;
        $totalExpense = 0;
        $chartData = [];
        foreach ($models as $index => $model) {
            $chartData['labels'][] = Service::$listType[$model->service_type]['ru'];
            $chartData['datasets'][0]['data'][] = $model->profit;
            $chartData['datasets'][0]['backgroundColor'][] = $this->colors[$model->service_type]['border'];
            $totalProfit += $model->profit;
            $totalServe += $model->countServe;
            $totalExpense += $model->expense;
        }

        return $this->render('total', [
            'type' => $type,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'chartData' => $chartData,
            'totalProfit' => $totalProfit,
            'totalServe' => $totalServe,
            'totalExpense' => $totalExpense,
            'monthChart' => $this->monthCartData($dataProvider, $searchModel),
        ]);
    }

    private function monthCartData($dataProvider, $searchModel)
    {
        $currentYear = date('Y');
        $currentMonth = isset($searchModel->dateTo) ? (int)date('m', strtotime($searchModel->dateTo))-1 : date('m');

        $models = $dataProvider->query
            ->addSelect('COUNT(id) as numActs')
            ->groupBy(["DATE_FORMAT(FROM_UNIXTIME(served_at),('%Y-%m'))", 'service_type'])
            ->addSelect('SUM(profit) as profit')
            ->addSelect('type_id')
            ->addSelect('service_type')
            ->addSelect("YEAR(FROM_UNIXTIME(served_at)) as year")
            ->addSelect("MONTH(FROM_UNIXTIME(served_at)) as month")
            ->andWhere(["YEAR(FROM_UNIXTIME(served_at))" => $currentYear])
            ->with(['type']);

        $labels = [];
        $data = [];

        for ($i = 0; $i <= $currentMonth; $i++) {
            $labels[] = $this->month[$i+1];
            $data[$i] = [
                'x' => $i,
                'y' => 0
            ];
        }
        $dataSet = [];
        foreach (Service::$listType as $key => $service) {
            $dataSet[$key] = [
                'label' => Service::$listType[$key]['ru'],
                'lineTension' => 0,
                'fill' => true,
                'pointRadius' => 7,
                'pointHoverRadius' => 10,
                'pointBorderWidth' => 2,
                'borderColor' => $this->colors[$key]['border'],
                'backgroundColor' => $this->colors[$key]['bg'],
                'data' => $data,
            ];
        }

        $totalChart = [
            'label' => 'Всего',
            'lineTension' => 0,
            'fill' => true,
            'pointRadius' => 7,
            'pointHoverRadius' => 10,
            'pointBorderWidth' => 2,
            'borderColor' => $this->colors[6]['border'],
            'backgroundColor' => $this->colors[6]['bg'],
            'data' => $data,
        ];

        foreach ($models->all() as $model) {
            $dataSet[$model->service_type]['data'][(int)$model->month-1] = [
                'x' => (int)$model->month,
                'y' => $model->profit
            ];
            $tempTotal =  $totalChart['data'][(int)$model->month-1]['y'];
            $totalChart['data'][(int)$model->month-1] = [
                'x' => (int)$model->month,
                'y' => $tempTotal + $model->profit,
            ];
        }
        $dataSet[] = $totalChart;

        $monthData = [
            'labels' => $labels,
            'datasets' =>  array_values($dataSet),
        ];

        return $monthData;
    }

    /**
     * Генерация рандомного цвета
     * TODO: перенести в helper
     *
     * @param bool $hex
     * @param int $alfa
     * @return string
     */
    private function generateRandomRgba($hex = false, $alfa = 1)
    {
        $hash = md5('color' . rand(1, 99));

        $color = "rgba(" .
            hexdec(substr($hash, 0, 2)) . ", " .
            hexdec(substr($hash, 2, 2)) . ", " .
            hexdec(substr($hash, 4, 2)) . ", " .
            $alfa .
            ")";

        $colorArray = array(
            hexdec(substr($hash, 0, 2)), // r
            hexdec(substr($hash, 2, 2)), // g
            hexdec(substr($hash, 4, 2)), //b
            $alfa);

        if ($hex) {
            $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
        }

        return $color;
    }

}