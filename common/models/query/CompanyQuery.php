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
use yii\db\Expression;

class CompanyQuery extends ActiveQuery
{
    /**
     * @return ActiveQuery
     */
    public function active()
    {
        return $this->alias('company')->andWhere(['company.status' => Company::STATUS_ACTIVE]);
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
     * @param $address
     * @return $this
     */
    public function byAddress($address)
    {
        return $this->andWhere(['address' => $address]);
    }
    /**
     * @return ActiveQuery
     */
    public function created()
    {
        return $this->andWhere(['status' => Company::STATUS_NEW]);
    }

    /**
     * @return $this
     */
    public function addParentKey()
    {
        return $this->addSelect([
            new Expression('IF(IFNULL(company.parent_id,0)=0, company.id*1000, company.parent_id*1000+company.id) as parent_key')
        ]);
    }

    /**
     * @return $this
     */
    public function orderByParentKey()
    {
        return $this->addOrderBy(['is_nested' => SORT_DESC, 'parent_key' => SORT_ASC]);
    }


}