<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tender_hystory".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $user_id
 * @property string $remove_date
 * @property integer $remove_id
 */
class TenderHystory extends ActiveRecord
{

    public static $periodList = ['все время', 'месяц', 'квартал', 'полгода', 'год'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tender_hystory}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'user_id', 'remove_id'], 'integer'],
            [['remove_date'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Компания',
            'user_id' => 'Сотрудник',
            'remove_id' => 'Сотрудник',
            'remove_date' => 'Дата переноса',
        ];
    }
}
