<?php
namespace console\controllers;

use common\models\MonthlyAct;
use Yii;
use yii\console\Controller;

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

        $partnerAct = MonthlyAct::getPartnerAct($allDate);

        $clientAct = MonthlyAct::getClientAct($allDate);

        $allAct = array_merge($partnerAct, $clientAct);
        if (!$allAct) {
            echo "Monthly Acts not created!\n";

            return 0;
        }
        foreach ($allAct as $act) {
            $MonthlyAct = new MonthlyAct();
            $MonthlyAct->client_id = $act['company_id'];
            $MonthlyAct->type_id = $act['service_type'];
            $MonthlyAct->profit = $act['profit'];
            $MonthlyAct->is_partner = $act['is_partner'];
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