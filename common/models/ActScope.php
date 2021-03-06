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
 * @property float $price
 * @property integer $amount
 * @property integer $parts
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
    public $name;
    public $address;
    public $client_id;
    public $partner_id;
    private $parts;

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
            'actsCount'    => 'Выполнений',
            'parts'    => 'Запасные части',
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
            [['parts'], 'safe'],
            [['parts'], 'default', 'value' => 0],
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

    public function getParts()
    {
        return $this->parts;
    }

    public function setParts($value)
    {
        $this->parts = $value;
    }

    /**
     * @return ActiveQuery
     */
    public function getAct()
    {
        return $this->hasOne(Act::className(), ['id' => 'act_id']);
    }
}