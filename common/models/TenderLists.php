<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tender_lists".
 *
 * @property integer $id
 * @property string $description
 * @property integer $required
 * @property integer $type
 */
class TenderLists extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tender_lists}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description', 'type'], 'required'],
            [['type', 'required'], 'integer'],
            [['description'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Название',
            'required' => 'Обязательный',
            'type' => 'Тип списка',
        ];
    }
}
