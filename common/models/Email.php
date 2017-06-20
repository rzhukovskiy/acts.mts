<?php

namespace common\models;

use yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "email".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property string $title
 * @property string $text
 */
class Email extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%email}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'text', 'title'], 'required'],
            [['type'], 'integer'],
            [['text'], 'string', 'max' => 5000],
            [['name', 'title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название шаблона',
            'type' => 'Тип компаний',
            'title' => 'Заголовок письма',
            'text' => 'Текс письма',
        ];
    }
}
