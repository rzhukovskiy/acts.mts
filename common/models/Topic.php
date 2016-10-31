<?php

namespace common\models;

use common\models\query\TopicQuery;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%topic}}".
 *
 * @property integer $id
 * @property string $topic
 * @property string $from
 * @property string $to
 * @property string $message_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $recipient
 * @property User $author
 * @property Message $lastMessage
 */
class Topic extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%topic}}';
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
            [['from', 'to'], 'required'],
            [['from', 'to', 'message_id', 'created_at', 'updated_at'], 'integer'],
            [['topic'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'topic' => 'Тема',
            'from' => 'От кого',
            'to' => 'Кому',
            'message_id' => 'Последнее сообщение',
            'created_at' => 'Созадно',
            'updated_at' => 'Последний ответ',
        ];
    }

    /**
     * @inheritdoc
     * @return TopicQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TopicQuery(get_called_class());
    }

    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'from']);
    }

    public function getRecipient()
    {
        return $this->hasOne(User::className(), ['id' => 'to']);
    }

    public function getLastMessage()
    {
        return $this->hasOne(Message::className(), ['id' => 'message_id']);
    }
}
