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
     * @return \common\models\Act[]|array
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
     * @param bool $isDisinfect
     * @return $this
     */
    public function byMonthlyDate($date, $isDisinfect = false)
    {
        //отдельная дата для дезинфекций
        if (!$date || $date == 'all') {
            $fromDate = ($isDisinfect) ? strtotime('+2 month') : strtotime('-1 month');
        } else {
            $fromDate = $date;
        }

        if (!$date) {
            $this->andWhere([
                "date_format(FROM_UNIXTIME(served_at), '%Y%m')" => date('Ym',
                    $fromDate)
            ]);
        } elseif ($date == 'all') {
            $this->andWhere([
                "<=",
                "date_format(FROM_UNIXTIME(served_at), '%Y%m')",
                date('Ym', $fromDate)
            ]);
        } else {
            $this->andWhere([
                "date_format(FROM_UNIXTIME(served_at), '%Y%m')" => date('Ym', $date)
            ]);
        }

        return $this;
    }
}