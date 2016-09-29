<?php

namespace common\models\query;
use common\models\Department;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\app\models\Department]].
 *
 * @see \app\models\Department
 */
class DepartmentQuery extends ActiveQuery
{
    public function active()
    {
        return $this->andWhere(['status' => Department::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     * @return \common\models\Department[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\Department|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
