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
 * @property integer $company_id
 * @property integer $user_id
 * @property string $process
 * @property string $mail_number
 * @property integer $communication_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Company $company
 *
 * @property string $communication_str
 */
class CompanyOffer extends ActiveRecord
{
    private $communication_str;
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
            [['company_id'], 'required'],
            [['company_id', 'communication_at', 'created_at', 'updated_at'], 'integer'],
            [['process', 'communication_str'], 'string', 'max' => 1000],
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
            'company_id' => 'Компания',
            'process' => 'Комментарии',
            'mail_number' => 'Номер почтового отправления',
            'communication_at' => 'Дата следующей связи',
            'communication_str' => 'Дата следующей связи',
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

    public function getCommunication_str()
    {
        return $this->communication_at ? date('d-m-Y H:i', $this->communication_at) : '';
    }

    public function setCommunication_str($value)
    {
        $this->communication_str = $value;
    }

    public function beforeSave($insert)
    {
        if (!empty($this->communication_str)) {
            $this->communication_at = \DateTime::createFromFormat('d-m-Y H:i', $this->communication_str)->getTimestamp();
        }

        $newProcessLength = strlen(trim($this->process));
        $oldProcessLength = strlen(trim($this->getOldAttribute('process')));
        if ($this->process && ($newProcessLength > $oldProcessLength)) {
            $this->process .= '
' . (new \DateTime())->format('d-m-Y H:i');
        }

        return parent::beforeSave($insert);
    }
}
