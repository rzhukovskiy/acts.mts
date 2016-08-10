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
 * @property integer $company_service_id
 * @property integer $expense
 * @property integer $price
 * @property integer $type
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $description
 *
 * @property CompanyService $companyService
 */
class ActScope extends ActiveRecord
{
    const TYPE_CLIENT = 0;
    const TYPE_PARTNER = 1;

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
     * @return Service
     */
    public function getCompanyService()
    {
        return $this->hasOne(CompanyService::className(), ['id' => 'company_service_id']);
    }
}