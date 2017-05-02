<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.08.2016
 * Time: 0:27
 */

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * ActScope model
 * @package common\models
 * @property integer $id
 * @property integer $act_id
 * @property integer $company_id
 * @property integer $service_id
 * @property integer $price
 * @property integer $amount
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $description
 *
 * @property Service $service
 * @property Company $company
 * @property Act $act
 */
class ActScope extends ActiveRecord
{

    public $actsCount;

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
    public function attributeLabels()
    {
        return [
            'actsCount'    => 'Обслуживания',
        ];
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
            ['price', 'default', 'value' => 0],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAct()
    {
        return $this->hasOne(Act::className(), ['id' => 'act_id']);
    }
}