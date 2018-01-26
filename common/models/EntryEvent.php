<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "entry_event".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $comment
 * @property string $date_from
 * @property string $date_to
 */
class EntryEvent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%entry_event}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['comment'], 'string'],
            [['company_id'], 'integer'],
            [['date_from', 'date_to'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'ID Компании',
            'comment' => 'Комментарий не работы',
            'date_from' => 'Дата начала не работы',
            'date_to' => 'Дата окончания не работы',
        ];
    }
}
