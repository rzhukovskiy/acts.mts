<?php

namespace frontend\traits;

use common\models\User;
use Yii;
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
     * Switch data by roles
     *
     * @param array $models
     * @return array|string
     */
    private function chartByMonthRoles(array $models)
    {
        /** @var User $model */
        $model = Yii::$app->user->identity;
        switch ($model->role) {
            case User::ROLE_CLIENT :
                $chartData = $this->chartByMonth($models, 'income');
                break;
            case User::ROLE_PARTNER :
                $chartData = $this->chartByMonth($models, 'expense');
                break;
            case User::ROLE_ADMIN :
                $chartData = $this->chartByMonth($models);
                break;
            default:
                $chartData = [];
        }

        return $chartData;
    }

    /**
     * @param $models
     * @return string
     */
    private function chartByMonth(array $models, $field = 'profit')
    {
        $data = $this->fillWithMonth();

        $maxYear = 0;
        foreach ($models as $model) {
            $maxYear = $maxYear > date('Y', strtotime($model->dateMonth)) ? $maxYear : date('Y', strtotime($model->dateMonth));
        }

        foreach ($models as $model) {
            if ( date('Y', strtotime($model->dateMonth)) == $maxYear) {
                $month = (int) date('m', strtotime($model->dateMonth));
                $data[$month-1]['y'] = $model->$field; // нумерация отстает от месяца на 1
            }
        }

        return json_encode($data);
    }

    /**
     * Switch data by roles
     *
     * @param array $models
     * @param $date
     * @param string $field
     * @return array|string
     */
    private function chartDataByDayRoles(array $models, $date, $field = 'profit')
    {
        /** @var User $model */
        $model = Yii::$app->user->identity;
        switch ($model->role) {
            case User::ROLE_CLIENT :
                $chartData = $this->chartDataByDay($models, $date, 'income');
                break;
            case User::ROLE_PARTNER :
                $chartData = $this->chartDataByDay($models, $date, 'expense');
                break;
            case User::ROLE_ADMIN :
                $chartData = $this->chartDataByDay($models, $date);
                break;
            default:
                $chartData = [];
        }

        return $chartData;
    }

    /**
     * @param $models
     * @param $date
     * @return string
     */
    private function chartDataByDay(array $models, $date, $field = 'profit')
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
            $data[$day-1]['y'] = $model->$field; // нумерация отстает от месяца на 1
        }

        return json_encode($data);
    }

    /**
     *
     *
     * @param ActiveDataProvider $dataProvider
     * @return string
     */
    private function chartTotal(ActiveDataProvider $dataProvider)
    {
        $dataProvider->query
            ->select("DATE(FROM_UNIXTIME(served_at)) as dateMonth")
            ->addSelect('SUM(profit) as profit')
            ->andWhere(['YEAR(FROM_UNIXTIME(served_at))' => date('Y')])
            ->groupBy(["MONTH(FROM_UNIXTIME(served_at))"]);
        //Иначе не дает сортировать по ssoom
        $dataProvider->sort=false;

        $models = $dataProvider->getModels();

        $data = $this->fillWithMonth();

        foreach ($models as $model) {
            $month = (int) date('m',strtotime($model->dateMonth));
            $data[$month-1]['y'] = $model->profit; // нумерация отстает от месяца на 1
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