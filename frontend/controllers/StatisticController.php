<?php

namespace frontend\controllers;

use frontend\models\Act;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use frontend\models\search\ActSearch;

class StatisticController extends Controller
{
    public function actionList($type = null)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Act::find(),
            'pagination' => false
        ]);

        if (!empty($type))
            $dataProvider->query
                ->andWhere(['type_id' => $type]);

        $dataProvider->query
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
            $chartData['datasets'][0]['data'][]= $model->profit;
            $chartData['datasets'][0]['backgroundColor'][]= $this->generateRandomRgba(true);
            $totalProfit += $model->profit;
            $totalServe += $model->countServe;
            $totalExpense += $model->expense;
        }

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'chartData' => $chartData,
            'totalProfit' => $totalProfit,
            'totalServe' => $totalServe,
            'totalExpense' => $totalExpense,
        ]);
    }

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

    public function actionTotal()
    {
        return $this->render('total');
    }

}