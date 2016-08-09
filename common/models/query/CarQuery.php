<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\Car]].
 *
 * @see \common\models\Car
 */
class CarQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    public function after($id)
    {
        return $this->andWhere(['id'=> $id]);
    }

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
}
