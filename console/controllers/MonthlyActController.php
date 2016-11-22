<?php
namespace console\controllers;

use common\models\Act;
use common\models\MonthlyAct;
use Yii;
use yii\console\Controller;

class MonthlyActController extends Controller
{
    /**
     * @param bool $allDate
     * @param bool $type
     * @return int
     */
    public function actionCreate($allDate = false, $type = false)
    {
        $actDate = date('Y-m-d', strtotime('-1 month'));

        $isHasAct = MonthlyAct::find()->where(['act_date' => $actDate])->exists();
        if ($isHasAct) {
            echo "Monthly Acts already created!\n";

            return 0;
        }
        $partnerAct = MonthlyAct::getPartnerAct($allDate, false, $type);
        $clientAct = MonthlyAct::getClientAct($allDate, false, $type);

        $allAct = array_merge($partnerAct, $clientAct);
        if (!$allAct) {
            echo "Monthly Acts not created!\n";

            return 0;
        }
        MonthlyAct::massSaveAct($allAct);
        echo "Monthly Acts successfully created!\n";

        return 0;
    }

    /**
     *
     */
    public function actionClearAndCreateAll()
    {
        /*
        MonthlyAct::deleteAll();
        $this->actionCreate('all');
        */
        $allAct = Act::find()->all();
        MonthlyAct::deleteAll();
        foreach ($allAct as $act) {
            MonthlyAct::getRealObject($act->service_type)->saveFromAct($act);
        }
    }
}