<?php

namespace common\models;

use common\models\query\CompanyOfferQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%company_offer}}".
 *
 * @property integer $id
 * @property string $company_id
 * @property string $process
 * @property string $mail_number
 * @property integer $communication_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Company $company
 *
 * @property string $communicate_str
 */
class CompanyOffer extends ActiveRecord
{
    public $communication_str;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_offer}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'created_at', 'updated_at'], 'required'],
            [['company_id', 'communication_at', 'created_at', 'updated_at'], 'integer'],
            [['process'], 'string', 'max' => 1000],
            [['mail_number'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'process' => 'Process',
            'mail_number' => 'Mail Number',
            'communication_at' => 'Дата следующей связи',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CompanyOfferQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CompanyOfferQuery(get_called_class());
    }

    /**
     * @return ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function beforeSave($insert)
    {
        if (!empty($this->communicate_str)) {
            $this->communication_at = \DateTime::createFromFormat('d-m-Y H:i', $this->communicate_str)->getTimestamp();
        }

        return parent::beforeSave($insert);
    }
}
