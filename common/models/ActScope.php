<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.08.2016
 * Time: 0:27
 */

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * ActScope model
 * @package common\models
 * @property integer $id
 * @property integer $act_id
 * @property integer $company_id
 * @property integer $company_service_id
 * @property integer $price
 * @property integer $amount
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $description
 *
 * @property CompanyService $companyService
 * @property Company $company
 * @property Act $act
 */
class ActScope extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%act_scope}}';
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
     * @return CompanyService
     */
    public function getCompanyService()
    {
        return $this->hasOne(CompanyService::className(), ['id' => 'company_service_id']);
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @return CompanyService
     */
    public function getAct()
    {
        return $this->hasOne(Act::className(), ['id' => 'act_id']);
    }
}