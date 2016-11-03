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
 * @property string $user_from
 * @property string $user_to
 * @property string $topic_id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $is_read
 *
 * @property User $recipient
 * @property User $author
 * @property Topic $topic
 *
 * @property string $topic_str
 */
class Message extends ActiveRecord
{
    public $topic_str;

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
            [['text', 'topic_str'], 'string'],
            [['text'], 'required'],
            [['user_from', 'user_to', 'topic_id', 'created_at', 'updated_at', 'is_read'], 'integer'],
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
            'user_from' => 'От',
            'user_to' => 'Кому',
            'topic_id' => 'Тема',
            'created_at' => 'Создано',
            'updated_at' => 'Updated At',
            'is_read' => 'Прочитано',
            'topic_str' => 'Тема',
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

    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'user_from']);
    }

    public function getRecipient()
    {
        return $this->hasOne(User::className(), ['id' => 'user_to']);
    }

    public function getTopic()
    {
        return $this->hasOne(Topic::className(), ['id' => 'topic_id']);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if (!$this->topic_id && $this->topic_str) {
                $modelTopic = new Topic();
                $modelTopic->topic = $this->topic_str;
                if (!$modelTopic->save()) {
                    return false;
                }
                $this->topic_id = $modelTopic->id;
            }
            return true;
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        $modelTopic = Topic::findOne($this->topic_id);
        if ($modelTopic) {
            $modelTopic->message_id = $this->id;
            $modelTopic->save();
        }

        parent::afterSave($insert, $changedAttributes);
    }
}
