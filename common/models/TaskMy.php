<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "task_my".
 *
 * @property integer $id
 * @property string $task
 * @property integer $from_user
 * @property string $data_status
 * @property string $data
 * @property integer $status
 * @property integer $priority
 */
class TaskMy extends \yii\db\ActiveRecord
{
    public $files;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_my}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task', 'from_user'], 'required'],
            [['task'], 'string'],
            [['from_user', 'status', 'priority'], 'integer'],
            [['data_status', 'data'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task' => 'Задача',
            'from_user' => 'От пользователя',
            'data' => 'Сроки',
            'data_status' => 'Дата выбора статуса',
            'status' => 'Статус',
            'priority' => 'Приоритет',
            'files' => 'Вложения',
        ];
    }

    public function beforeSave($insert)
    {

        // Если это новая запись то обрабатываем данные из формы здесь
        if($this->isNewRecord) {
            $this->data = (String) strtotime($this->data);
        }
        return parent::beforeSave($insert);

    }

    public function upload()
    {
        $filePath = \Yii::getAlias('@webroot/files/mytask/' . $this->id . '/');

        if (!file_exists(\Yii::getAlias('@webroot/files/'))) {
            mkdir(\Yii::getAlias('@webroot/files/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/mytask/'))) {
            mkdir(\Yii::getAlias('@webroot/files/mytask/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/mytask/' . $this->id . '/'))) {
            mkdir(\Yii::getAlias('@webroot/files/mytask/' . $this->id . '/'), 0775);
        }

        foreach ($this->files as $file) {

            if (!file_exists($filePath . $file->baseName . '.' . $file->extension)) {
                $file->saveAs($filePath . $file->baseName . '.' . $file->extension);
            } else {

                $filename = $filePath . $file->baseName . '.' . $file->extension;
                $i = 1;

                while (file_exists($filename)) {
                    $filename = $filePath . $file->baseName . '(' . $i . ').' . $file->extension;
                    $i++;
                }

                $file->saveAs($filename);

            }

        }

    }
}
