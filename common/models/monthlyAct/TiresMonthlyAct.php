<?php

namespace common\models\monthlyAct;

use common\models\MonthlyAct;
use common\models\Service;


/**
 * TiresMonthlyAct
 */
class TiresMonthlyAct extends MonthlyAct implements MonthlyActInterface
{
    /**
     * @param \common\models\Act $act
     * @return mixed|void
     */
    public function saveFromAct($act)
    {

        $clientId = $act->client_id;
        $partnerId = $act->partner_id;
        $date = date('Y-m-00', $act->served_at);

        if ($this->checkExistByAct($partnerId, true, $date)) {
            self::saveAct($partnerId, true, $date);
        }
        if (self::checkExistByAct($clientId, false, $date)) {
            $this->saveAct($clientId, false, $date);
        }
    }

    /**
     * @param $clientId
     * @param $isPartner
     * @param $time
     * @return bool
     */
    private function checkExistByAct($clientId, $isPartner, $time)
    {
        $checkedMonthlyAct =
            self::find()
                ->andWhere(['client_id' => $clientId])
                ->andWhere(['act_date' => $time])
                ->andWhere(['is_partner' => $isPartner])
                ->andWhere(['type_id' => Service::TYPE_TIRES]);

        return !$checkedMonthlyAct->exists();
    }

    /**
     * @param $companyId
     * @param $isPartner
     * @param $date
     */
    private function saveAct($companyId, $isPartner, $date)
    {
        $monthlyAct = new MonthlyAct();
        $monthlyAct->client_id = $companyId;
        $monthlyAct->type_id = Service::TYPE_TIRES;
        $monthlyAct->is_partner = $isPartner;
        $monthlyAct->act_date = $date;
        $monthlyAct->save();
    }
}