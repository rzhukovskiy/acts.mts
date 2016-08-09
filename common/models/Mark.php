<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%mark}}".
 *
 * @property integer $id
 * @property string $name
 */
class Mark extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mark}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 45],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\MarkQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\MarkQuery(get_called_class());
    }
}
