<?php

namespace common\models;

use Yii;
use common\traits\JsonTrait;

/**
 * This is the model class for table "task_user".
 *
 * @property integer $id
 * @property string $task
 * @property integer $from_user
 * @property integer $for_user
 * @property string $data
 * @property string $data_status
 * @property string $comment
 * @property string $comment_main
 * @property string $comment_watcher
 * @property string $title
 * @property integer $status
 * @property integer $priority
 */
class TaskUser extends \yii\db\ActiveRecord
{
    public $files;
    public $files_main;

    use JsonTrait;

    const PAYMENT_STATUS_NOT_DONE = 0;
    const PAYMENT_STATUS_DONE = 1;
    const PAYMENT_STATUS_TRUE = 2;

    const PRIORITY_STATUS_NOTFAST = 0;
    const PRIORITY_STATUS_QUICKLY = 1;
    const PRIORITY_STATUS_FAST = 2;

    public static $priorityStatus = [
        self::PRIORITY_STATUS_NOTFAST => 'Не срочно',
        self::PRIORITY_STATUS_QUICKLY => 'Срочно',
        self::PRIORITY_STATUS_FAST => 'Очень срочно',
    ];

    public static $executionStatus = [
        self::PAYMENT_STATUS_NOT_DONE => 'Не выполнено',
        self::PAYMENT_STATUS_DONE => 'В процессе',
        self::PAYMENT_STATUS_TRUE => 'Выполнено',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%task_user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task', 'from_user', 'for_user', 'priority'], 'required'],
            [['task', 'comment', 'comment_main', 'comment_watcher'], 'string', 'max' => 5000],
            [['from_user', 'status', 'for_user', 'is_archive', 'priority'], 'integer'],
            [['data', 'data_status'], 'string', 'max' => 20],
            [['title'], 'string', 'max' => 255],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 30],
            [['files_main'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 30],
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
            'title' => 'Тема',
            'from_user' => 'От пользователя',
            'data' => 'Сроки',
            'data_status' => 'Дата выбора статуса',
            'status' => 'Статус',
            'priority' => 'Приоритет',
            'for_user' => 'Для пользователя',
            'comment' => 'Комментарий ответственного',
            'comment_main' => 'Комментарий инициатора',
            'comment_watcher' => 'Комментарий наблюдателя',
            'is_archive' => 'Архив',
            'files' => 'Вложения инициатора',
            'files_main' => 'Вложения ответственного',
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

    static function payDis($val)
    {
        $currentUser = Yii::$app->user->identity;
        if (($val == self::PAYMENT_STATUS_TRUE) && ($currentUser) && ($currentUser->role != User::ROLE_ADMIN) && ($currentUser->id != 176)) {
            $disabled = true;
        } else {
            $disabled = false;
        }
        return $disabled;
    }

    static function colorForExecutionStatus($status)
    {
        $executionStatus = [
            self::PAYMENT_STATUS_TRUE => 'btn-success',
            self::PAYMENT_STATUS_NOT_DONE => 'btn-danger',
            self::PAYMENT_STATUS_DONE => 'btn-warning',
        ];
        return $executionStatus[$status];

    }

    public function upload()
    {
        $filePath = \Yii::getAlias('@webroot/files/task/' . $this->id . '/');

        if (!file_exists(\Yii::getAlias('@webroot/files/'))) {
            mkdir(\Yii::getAlias('@webroot/files/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/task/'))) {
            mkdir(\Yii::getAlias('@webroot/files/task/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/task/' . $this->id . '/'))) {
            mkdir(\Yii::getAlias('@webroot/files/task/' . $this->id . '/'), 0775);
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
