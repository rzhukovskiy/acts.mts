<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tender_members".
 *
 * @property integer $id
 * @property integer $tender_id
 * @property string $company_name
 * @property string $inn
 * @property string $city
 * @property string $comment
 */
class TenderMembers extends ActiveRecord
{

    public $tender_id;

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
            [['company_name', 'inn'], 'required'],
            [['comment'], 'string'],
            [['company_name', 'city'], 'string', 'max' => 255],
            [['inn'], 'string', 'max' => 30],
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
            'company_name' => 'Компания',
            'inn' => 'ИНН',
            'city' => 'Город',
            'comment' => 'Комментарий',
        ];
    }
    public function beforeSave($insert)
    {
            if ($this->inn && $this->isNewRecord) {

                $arr_links = TenderMembers::find()->where(['inn' => $this->inn])->select('id')->column();

                if (count($arr_links) > 0) {
                    $id_links = $arr_links[0];
                    // проверяем наличие связи
                    $resWinner = TenderLinks::find()->where(['AND', ['tender_id' => $this->tender_id], ['member_id' => $id_links]])->select('id')->asArray()->column();

                    if (count($resWinner) == 0) {

                        $tenderlinks = new TenderLinks();
                        $tenderlinks->member_id = $id_links;
                        $tenderlinks->tender_id = $this->tender_id;
                        $tenderlinks->save();

                    }

                    return false;
                } else {
                    return parent::beforeSave($insert);
                }

            } else {
                $inn_exists = TenderMembers::find()->where(['AND', ['!=', 'id', $this->id], ['inn' => $this->inn]])->exists();
                if ($inn_exists) {
                    return false;
                }

                return parent::beforeSave($insert);
            }

    }

    public function afterSave($insert, $changedAttributes)
    {
            if ($insert) {
                $tenderlinks = new TenderLinks();
                $tenderlinks->member_id = $this->id;
                $tenderlinks->tender_id = $this->tender_id;

                $tenderlinks->save();
            }
        parent::afterSave($insert, $changedAttributes);
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
