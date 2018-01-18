<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "task_user_link".
 *
 * @property integer $id
 * @property integer $task_id
 * @property integer $for_user_copy
 */
class TaskUserLink extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_user_link}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id'], 'required'],
            [['task_id', 'for_user_copy'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'ID Задания',
            'for_user_copy' => 'Для пользователя(копия)',
        ];
    }
}
