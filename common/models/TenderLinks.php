<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "tender_members".
 *
 * @property integer $id
 * @property integer $tender_id
 * @property integer $member_id
 * @property integer $winner
 */
class TenderLinks extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tender_links}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tender_id', 'member_id'], 'required'],
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
            'tender_id' => 'ID тендера',
            'member_id' => 'ID конкурента',
            'winner' => 'Победитель',
        ];
    }

    public function getTendermembers()
    {
        return $this->hasOne(TenderMembers::className(), ['id' => 'member_id']);
    }

}
