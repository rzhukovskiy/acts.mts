<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%contact}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $task_name
 * @property string $comment
 * @property integer $status
 *
 * @property Company $company
 */
class Plan extends \yii\db\ActiveRecord
{
    const STATUS_NOT_DONE = 1;
    const STATUS_PROCESS = 2;
    const STATUS_DONE = 3;
    static $listStatus = [
        self::STATUS_NOT_DONE => 'не выполнено',
        self::STATUS_PROCESS  => 'в процессе',
        self::STATUS_DONE     => 'выполнено'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%plan}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'task_name'], 'required'],
            [['user_id', 'status'], 'integer'],
            [['task_name'], 'string', 'max' => 1000],
            [['comment'], 'string'],
            [
                ['user_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => User::className(),
                'targetAttribute' => ['user_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'user_id'   => 'Сотрудник',
            'task_name' => 'Задача',
            'status'    => 'Статус',
            'comment'   => 'Комментарий'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Список классов для статуса
     * @param $status
     * @return mixed
     */
    static function colorForStatus($status)
    {
        $planStatus = [
            self::STATUS_NOT_DONE => 'monthly-act-danger',
            self::STATUS_PROCESS  => 'monthly-act-warning',
            self::STATUS_DONE     => 'monthly-act-success',
        ];

        return $planStatus[$status];
    }

}
