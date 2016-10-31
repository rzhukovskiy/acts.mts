<?php

namespace common\models;

use common\models\query\MessageQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%message}}".
 *
 * @property integer $id
 * @property string $text
 * @property string $user_id
 * @property string $topic_id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $is_read
 *
 * @property User $user
 * @property Topic $topic
 */
class Message extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%message}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text'], 'string'],
            [['user_id', 'topic_id'], 'required'],
            [['user_id', 'topic_id', 'created_at', 'updated_at', 'is_read'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'text' => 'Сообщение',
            'user_id' => 'От',
            'topic_id' => 'Тема',
            'created_at' => 'Создано',
            'updated_at' => 'Updated At',
            'is_read' => 'Прочитано',
        ];
    }

    /**
     * @inheritdoc
     * @return MessageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new MessageQuery(get_called_class());
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getTopic()
    {
        return $this->hasOne(Topic::className(), ['id' => 'topic_id']);
    }
}
