<?php

namespace frontend\traits;

use common\components\DateHelper;
use yii\data\ActiveDataProvider;

/**
 * Generate data for charts for partner statistics
 *
 * Class ChartTrait
 * @package frontend\traits
 */

trait ChartTrait
{
    /**
     * @param $models
     * @return string
     */
    private function chartByMonth(array $models)
    {
        $data = $this->fillWithMonth();

        foreach ($models as $model) {
            $month = (int) date('m',strtotime($model->dateMonth));
            $data[$month]['y'] = $model->profit;
        }

        return json_encode($data);
    }


    /**
     * @param $models
     * @param $date
     * @return string
     */
    private function chartDataByDay(array $models, $date)
    {
        $daysCount = date('t', strtotime($date));
        $data = [];
        for ($i = 1; $i<= $daysCount; $i++) {
            $data[] = [
                'x' => $i,
                'y' => 0,
                'label' => $i,
            ];
        }

        foreach ($models as $model) {
            $day = (int) date('d',strtotime($model->dateMonth));
            $data[$day-1]['y'] = $model->profit;
        }

        return json_encode($data);
    }

    private function chartTotal(ActiveDataProvider $dataProvider)
    {
        $dataProvider->query
            ->select("DATE(FROM_UNIXTIME(served_at)) as dateMonth")
            ->addSelect('SUM(profit) as profit')
            ->andWhere(['YEAR(FROM_UNIXTIME(served_at))' => date('Y')])
            ->groupBy(["MONTH(FROM_UNIXTIME(served_at))"]);

        $models = $dataProvider->getModels();

        $data = $this->fillWithMonth();

        foreach ($models as $model) {
            $month = (int) date('m',strtotime($model->dateMonth));
            $data[$month-1]['y'] = $model->profit;
        }

        return json_encode($data);
    }

    /**
     * Fill default data in dataset
     *
     * @return array
     */
    private function fillWithMonth()
    {
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $month = DateHelper::getMonthNameByNum($i);
            $data[] = [
                'x' => $i,
                'y' => 0,
                'label' => $month[0],
            ];
        }
        return $data;
    }
}