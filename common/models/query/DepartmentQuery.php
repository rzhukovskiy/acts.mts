<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\Department]].
 *
 * @see \app\models\Department
 */
class DepartmentQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \app\models\Department[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\Department|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
