<?php

namespace common\models\monthlyAct;

use common\models\MonthlyAct;
use common\models\Service;


/**
 * ServiceMonthlyAct
 */
class ServiceMonthlyAct extends MonthlyAct implements MonthlyActInterface
{
    /**
     * @param \common\models\Act $act
     * @return mixed|void
     */
    public function saveFromAct($act)
    {
        $id = $act->id;
        $clientId = $act->client_id;
        $partnerId = $act->partner_id;
        $number = $act->number;
        $date = date('Y-m-00', $act->served_at);

        if ($this->checkExistByAct($partnerId, true, $date, $id)) {
            self::saveAct($partnerId, true, $date, $id, $number);
        }
        if (self::checkExistByAct($clientId, false, $date, $id)) {
            $this->saveAct($clientId, false, $date, $id, $number);
        }
    }

    /**
     * @param $clientId
     * @param $isPartner
     * @param $date
     * @param $id
     * @return bool
     */
    private function checkExistByAct($clientId, $isPartner, $date, $id)
    {
        $checkedMonthlyAct =
            self::find()
                ->andWhere(['client_id' => $clientId])
                ->andWhere(['act_date' => $date])
                ->andWhere(['is_partner' => $isPartner])
                ->andWhere(['act_id' => $id])
                ->andWhere(['type_id' => Service::TYPE_SERVICE]);

        return !$checkedMonthlyAct->exists();
    }

    /**
     * @param $companyId
     * @param $isPartner
     * @param $date
     * @param $id
     * @param $number
     */
    private function saveAct($companyId, $isPartner, $date, $id, $number)
    {
        $monthlyAct = new MonthlyAct();
        $monthlyAct->client_id = $companyId;
        $monthlyAct->type_id = Service::TYPE_SERVICE;
        $monthlyAct->is_partner = $isPartner;
        $monthlyAct->act_date = $date;
        $monthlyAct->act_id = $id;
        $monthlyAct->number = $number;
        $monthlyAct->save();
    }
}
