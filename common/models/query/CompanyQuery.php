<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.08.16
 * Time: 17:39
 */

namespace common\models\query;

use common\models\Company;
use yii\db\ActiveQuery;

class CompanyQuery extends ActiveQuery
{
    /**
     * @return ActiveQuery
     */
    public function active()
    {
        return $this->andWhere(['status' => Company::STATUS_ACTIVE]);
    }

    /**
     * @param integer $type
     * @return $this
     */
    public function byType($type)
    {
        return $this->andWhere(['type' => $type]);
    }

    /**
     * @return ActiveQuery
     */
    public function created()
    {
        return $this->andWhere(['status' => Company::STATUS_NEW]);
    }
}