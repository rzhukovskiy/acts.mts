<?php

namespace common\models\monthlyAct;

use common\models\MonthlyAct;
use common\models\Service;


/**
 * DisinfectMonthlyAct
 */
class DisinfectMonthlyAct extends MonthlyAct implements MonthlyActInterface
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
        foreach ($act->scopes as $scope) {
            $serviceId = $scope->service_id;
            if ($this->checkExistByAct($partnerId, MonthlyAct::PARTNER, $date, $serviceId)) {
                $this->saveAct($partnerId, MonthlyAct::PARTNER, $date, $serviceId);
            }
            if ($this->checkExistByAct($clientId, MonthlyAct::NOT_PARTNER, $date, $serviceId)) {
                $this->saveAct($clientId, MonthlyAct::NOT_PARTNER, $date, $serviceId);
            }
        }

    }

    /**
     * @param $clientId
     * @param $isPartner
     * @param $time
     * @param $serviceId
     * @return bool
     */
    private function checkExistByAct($clientId, $isPartner, $time, $serviceId)
    {
        $checkedMonthlyAct =
            self::find()
                ->andWhere(['client_id' => $clientId])
                ->andWhere(['act_date' => $time])
                ->andWhere(['is_partner' => $isPartner])
                ->andWhere(['service_id' => $serviceId])
                ->andWhere(['type_id' => Service::TYPE_DISINFECT]);


        return !$checkedMonthlyAct->exists();
    }

    /**
     * @param $companyId
     * @param $isPartner
     * @param $date
     * @param $serviceId
     */
    private function saveAct($companyId, $isPartner, $date, $serviceId)
    {
        $monthlyAct = new MonthlyAct();
        $monthlyAct->client_id = $companyId;
        $monthlyAct->type_id = Service::TYPE_DISINFECT;
        $monthlyAct->is_partner = $isPartner;
        $monthlyAct->service_id = $serviceId;
        $monthlyAct->act_date = $date;
        $monthlyAct->save();
    }
}