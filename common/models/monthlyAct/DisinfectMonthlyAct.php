<?php

namespace common\models\monthlyAct;

use common\models\MonthlyAct;
use common\models\query\MonthlyActQuery;
use common\models\Service;
use yii\db\Expression;


/**
 * DisinfectMonthlyAct
 */
class DisinfectMonthlyAct extends MonthlyAct implements MonthlyActInterface
{
    static $type = Service::TYPE_DISINFECT;

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
     * @return false|int|null|string
     */
    public function getProfit()
    {
        $partnerClientId = ($this->is_partner == self::PARTNER) ? 'partner_id' : 'client_id';
        $profit = (new \yii\db\Query())->from('{{%act}} act')->where([$partnerClientId => $this->client_id]);

        if ($this->is_partner == self::PARTNER) {
            $profit->select([new Expression('COUNT(scopes.id)')]);
        } else {
            $profit->select([new Expression('SUM(scopes.price*scopes.amount) as profit')]);
        }
        $profit->join('LEFT JOIN', '{{%act_scope}} scopes', 'scopes.act_id = act.id')
            ->andWhere([
                "date_format(FROM_UNIXTIME(served_at), '%Y-%m-00')" => $this->act_date
            ])
            ->andWhere([
                "service_type" => Service::TYPE_DISINFECT,
            ])
            ->andWhere(['scopes.service_id' => $this->service_id])
            ->andWhere(['scopes.company_id' => $this->client_id]);

        $profit = $profit->scalar();

        return isset($profit) ? $profit : 0;
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

    //-------------------
    //Служебная часть
    //----------------

    /**
     *
     */
    public function init()
    {
        $this->type_id = self::$type;
        parent::init();
    }

    /**
     * @return MonthlyActQuery
     */
    public static function find()
    {
        return new MonthlyActQuery(get_called_class(), ['type' => self::$type]);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->type_id = self::$type;

        return parent::beforeSave($insert);
    }
}