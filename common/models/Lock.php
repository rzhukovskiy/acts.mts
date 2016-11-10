<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%lock}}".
 *
 * @property integer $id
 * @property integer $type
 * @property string $period
 */
class Lock extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lock}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'period'], 'required'],
            [['type'], 'integer'],
            [['period'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'period' => 'Period',
        ];
    }
}
