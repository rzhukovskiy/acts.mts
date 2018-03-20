<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "informing".
 *
 * @property integer $id
 * @property string $text
 * @property integer $from_user
 * @property string $date_create
 * @property integer $is_archive
 */
class Informing extends \yii\db\ActiveRecord
{

    const DISAGREE = 0;
    const AGREE = 1;

    public static $agreeStatus = [
        self::DISAGREE => 'Не ознакомлен',
        self::AGREE => 'Ознакомлен',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%informing}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text', 'from_user', 'date_create'], 'required'],
            [['text'], 'string', 'max' => 5000],
            [['from_user', 'is_archive'], 'integer'],
            [['date_create'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'text' => 'Текст',
            'from_user' => 'От пользоватя',
            'date_create' => 'Дата создания',
            'is_archive' => 'В архиве',
        ];
    }

}
