<?php

namespace common\models;

use common\models\query\ActErrorQuery;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "act_error".
 *
 * @property integer $id
 * @property string $act_id
 * @property string $error_type
 * 
 * @property Act $act
 */
class ActError extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'act_error';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['act_id', 'error_type'], 'required'],
            [['act_id', 'error_type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'act_id' => 'Act ID',
            'error_type' => 'Error Type',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getAct()
    {
        return $this->hasOne(Act::className(), ['id' => 'act_id']);
    }

    /**
     * @inheritdoc
     * @return ActErrorQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ActErrorQuery(get_called_class());
    }
}
