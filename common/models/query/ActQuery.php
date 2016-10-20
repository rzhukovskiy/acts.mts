<?php
/**
 * Created by PhpStorm.
 * User: ruslanzh
 * Date: 16/08/16
 * Time: 23:50
 */

namespace common\models\query;

use yii\db\ActiveQuery;

class ActQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return \common\models\Car[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\Car|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function withCountServe()
    {
        return $this->addSelect('COUNT({{%act}}.id) AS countServe');
    }

    /**
     * @param $date
     * @return $this
     */
    public function byMonthlyDate($date)
    {
        if (!$date) {
            $this->andWhere([
                "date_format(FROM_UNIXTIME(served_at), '%Y%m')" => date('Ym',
                    strtotime('-1 month'))
            ]);
        } elseif ($date == 'all') {
            $this->andWhere([
                "<=",
                "date_format(FROM_UNIXTIME(served_at), '%Y%m')",
                date('Ym', strtotime('-1 month'))
            ]);
        } else {
            $this->andWhere([
                "date_format(FROM_UNIXTIME(served_at), '%Y%m')" => date('Ym', $date)
            ]);
        }

        return $this;
    }
}