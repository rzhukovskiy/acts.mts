<?php

namespace common\models\query;

use common\models\MonthlyAct;

/**
 * This is the ActiveQuery class for [[\common\models\MonthlyAct]].
 *
 * @see \common\models\MonthlyAct
 */
class MonthlyActQuery extends \yii\db\ActiveQuery
{
    public $type;
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\MonthlyAct[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\MonthlyAct|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $clientId
     * @param $servedTime
     * @return $this
     */
    public function byClient($servedTime, $clientId)
    {
        $this->andWhere(['client_id' => $clientId, 'is_partner' => MonthlyAct::NOT_PARTNER]);
        $this->andWhere(['act_date' => date('Y-m-00', $servedTime)]);

        return $this;
    }

    /**
     * @param $partnerId
     * @param $servedTime
     * @return $this
     */
    public function byPartner($servedTime, $partnerId)
    {
        $this->andWhere(['client_id' => $partnerId, 'is_partner' => MonthlyAct::PARTNER]);
        $this->andWhere(['act_date' => date('Y-m-00', $servedTime)]);

        return $this;
    }

    public function byType($serviceType)
    {
        $this->andWhere(['type_id' => $serviceType]);

        return $this;
    }
}
