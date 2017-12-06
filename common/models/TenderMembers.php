<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tender_members".
 *
 * @property integer $id
 * @property string $company_name
 * @property string $inn
 * @property string $city
 * @property string $comment
 */
class TenderMembers extends ActiveRecord
{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tender_members}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['comment'], 'string'],
            [['company_name', 'city'], 'string', 'max' => 255],
            [['inn'], 'string', 'max' => 30],
            [['winner'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_name' => 'Компания',
            'inn' => 'ИНН',
            'city' => 'Город',
            'comment' => 'Комментарий',
            'winner' => 'Победитель',
        ];
    }

    public function getTenderlinks()
    {
        return $this->hasOne(TenderLinks::className(), ['member_id' => 'id']);
    }

    public function getTenderlinksName()
    {
        return $this->tenderlinks->id;
    }

    public function getTender()
    {
        return $this->hasOne(Tender::className(), ['id' => 'id']);
    }

    public function getTenderName()
    {
        return $this->tender->name;
    }


}
