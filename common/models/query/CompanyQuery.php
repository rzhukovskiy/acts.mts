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
     * @return ActiveQuery
     */
    public function created()
    {
        return $this->andWhere(['status' => Company::STATUS_NEW]);
    }
}