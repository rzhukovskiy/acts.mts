<?php

namespace common\models\monthlyAct;

use common\models\MonthlyAct;
use common\models\query\MonthlyActQuery;
use common\models\Service;
use yii\db\Expression;


/**
 * PenaltyMonthlyAct
 */
class PenaltyMonthlyAct extends MonthlyAct implements MonthlyActInterface
{
    static $type = Service::TYPE_PENALTY;

    /**
     * @param \common\models\Act $act
     * @return mixed|void
     */
    public function saveFromAct($act)
    {
        $clientId = $act->client_id;
        $partnerId = $act->partner_id;
        $date = date('Y-m-00', $act->served_at);
        if ($this->checkExistByAct($partnerId, MonthlyAct::PARTNER, $date)) {
            $this->saveAct($partnerId, MonthlyAct::PARTNER, $date);
        }
        if ($this->checkExistByAct($clientId, MonthlyAct::NOT_PARTNER, $date)) {
            $this->saveAct($clientId, MonthlyAct::NOT_PARTNER, $date);
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
            $profit->select([new Expression('SUM(expense) as profit')]);
        } else {
            $profit->select([new Expression('SUM(income) as profit')]);
        }
        $profit->andWhere([
            "date_format(FROM_UNIXTIME(served_at), '%Y-%m-00')" => $this->act_date
        ])->andWhere([
            "service_type" => self::$type,
        ]);

        $profit = $profit->scalar();

        return isset($profit) ? $profit : 0;
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
                ->andWhere(['type_id' => self::$type]);

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
        $monthlyAct->type_id = self::$type;
        $monthlyAct->is_partner = $isPartner;
        $monthlyAct->act_date = $date;
        $monthlyAct->save();
        $fh = fopen(\Yii::$app->getRuntimePath() . '/logs/act.log', 'a');
        $data =
            '[' .
            date('Y-m-d H:i:s') .
            '] Акт ' .
            $monthlyAct->id .
            ' тип ' .
            $monthlyAct->type_id .
            ' Id компании ' .
            $monthlyAct->client_id .
            ' Партнер ' .
            $monthlyAct->is_partner .
            ' дата акта ' .
            $monthlyAct->act_date .
            "\r\n";
        fwrite($fh, $data);
        fclose($fh);
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