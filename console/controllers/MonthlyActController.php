<?php
namespace console\controllers;

use common\models\MonthlyAct;
use Yii;
use yii\console\Controller;
use yii\db\Expression;

class MonthlyActController extends Controller
{
    /**
     * @param bool $allDate
     * @return int
     */
    public function actionCreate($allDate = false)
    {
        $actDate = date('Y-m-d', strtotime('-1 month'));

        $isHasAct = MonthlyAct::find()->where(['act_date' => $actDate])->exists();
        if ($isHasAct) {
            echo "Monthly Acts already created!\n";

            return 0;
        }
        //Создаем за 1 месяц
        if (!$allDate) {
            $allAct =
                (new \yii\db\Query())->select('client_id,type_id,SUM(profit) as profit')
                    ->addSelect(new Expression('date_format(FROM_UNIXTIME(served_at), "%Y-%m-00") as date'))
                    ->from('act')
                    ->where(["date_format(FROM_UNIXTIME(served_at), '%Y%m')" => date('Ym', strtotime('-1 month'))])
                    ->groupBy('client_id,type_id,date')
                    ->all();
        } else {//Создаем за все месяцы
            $allAct =
                (new \yii\db\Query())->select('client_id,type_id,SUM(profit) as profit')
                    ->addSelect(new Expression('date_format(FROM_UNIXTIME(served_at), "%Y-%m-00") as date'))
                    ->where(["<=", "date_format(FROM_UNIXTIME(served_at), '%Y%m')", date('Ym', strtotime('-1 month'))])
                    ->from('act')
                    ->groupBy('client_id,type_id,date')
                    ->all();
        }

        if (!$allAct) {
            echo "Monthly Acts not created!\n";

            return 0;
        }
        foreach ($allAct as $act) {
            $MonthlyAct = new MonthlyAct();
            $MonthlyAct->client_id = $act['client_id'];
            $MonthlyAct->type_id = $act['type_id'];
            $MonthlyAct->profit = $act['profit'];
            $MonthlyAct->act_date = $act['date'];
            $MonthlyAct->save();
        }
        echo "Monthly Acts successfully created!\n";

        return 0;
    }

    /**
     *
     */
    public function actionClearAndCreateAll()
    {
        MonthlyAct::deleteAll();
        $this->actionCreate(true);
    }
}