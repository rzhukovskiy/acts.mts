<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\Entry]].
 *
 * @see \common\models\Entry
 */
class EntryQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\Entry[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\Entry|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
