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
}