<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * CompanyService model
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $service_id
 * @property integer $type_id
 * @property integer $price
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Company $company
 * @property Service $service
 * @property Type $type
 */
class CompanyService extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_service}}';
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
            [['type_id', 'service_id', 'company_id', 'price'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'Тип ТС',
            'service_id' => 'Услуга',
            'price' => 'Цена',
        ];
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'type_id']);
    }

    /**
     * @param $service_id int
     * @return int
     */
    public function getPriceForService($service_id)
    {
        $companyService =  self::findOne([
            'company_id' => $this->company_id,
            'type_id' => $this->type_id,
            'service_id' => $service_id,
        ]);

        return $companyService ? $companyService->price : 0;
    }

    public function beforeSave($insert)
    {
        $existed = CompanyService::findOne([
            'company_id' => $this->company_id,
            'type_id' => $this->type_id,
            'service_id' => $this->service_id,
        ]);

        if ($existed) {
            $existed->delete();
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return string
     */
    public function getSamePrices()
    {
        $samePrices = CompanyService::findAll([
            'company_id' => $this->company_id,
            'price'      => $this->price,
            'service_id' => $this->service_id,
        ]);

        $cnt = 0;
        $types = [];
        foreach($samePrices as $price) {
            $types[] = $price->type->name;
            $cnt++;
            if ($cnt == 3) {
                $types[] = '...';
                break;
            }
        }

        return implode(", ", $types);
    }
}
