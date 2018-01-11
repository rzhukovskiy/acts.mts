<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "lock_info".
 *
 * @property integer $id
 * @property integer $partner_id
 * @property integer $type
 * @property string $period
 * @property string $comment
 */
class LockInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lock_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id', 'type', 'period', 'comment'], 'required'],
            [['partner_id', 'type'], 'integer'],
            [['period', 'comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'partner_id' => 'ID партнера',
            'type' => 'Тип',
            'period' => 'Период',
            'comment' => 'Комментарий',
        ];
    }
}
