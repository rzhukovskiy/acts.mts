<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * CompanyServiceType model
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $type
 *
 * @property Company $company
 */
class CompanyServiceType extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_service_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'type'], 'required'],
        ];
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }
}
