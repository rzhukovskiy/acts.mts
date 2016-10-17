<?php
namespace console\controllers;

use common\models\MonthlyAct;
use Yii;
use yii\console\Controller;

class MonthlyActController extends Controller
{
    public function actionInit()
    {
        $shift = '-2 month';
        $actDate = date('Y-m-d', strtotime($shift));

        $isHasAct = MonthlyAct::find()->where(['act_date' => $actDate])->exists();
        if ($isHasAct) {
            echo "Monthly Acts already created!\n";

            return 0;
        }
        $allAct =
            (new \yii\db\Query())->select('client_id,type_id,SUM(profit) as profit')
                ->from('act')
                ->where(["date_format(FROM_UNIXTIME(created_at), '%Y%m')" => date('Ym', strtotime($shift))])
                ->groupBy('client_id,type_id')
                ->all();
        if (!$allAct) {
            echo "Monthly Acts not created!\n";

            return 0;
        }
        foreach ($allAct as $act) {
            $MonthlyAct = new MonthlyAct();
            $MonthlyAct->client_id = $act['client_id'];
            $MonthlyAct->type_id = $act['type_id'];
            $MonthlyAct->profit = $act['profit'];
            $MonthlyAct->profit = $act['profit'];
            $MonthlyAct->act_date = $actDate;
            $MonthlyAct->save();
        }
        echo "Monthly Acts successfully created!\n";

        return 0;
    }
}