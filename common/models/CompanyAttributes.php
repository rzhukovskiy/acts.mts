<?php

namespace common\models;

use common\traits\JsonTrait;
use Yii;

/**
 * This is the model class for table "{{%company_attributes}}".
 *
 * @property integer $id
 * @property string $company_id
 * @property string $name
 * @property integer $type
 * @property string $value
 *
 * @property Company $company
 */
class CompanyAttributes extends \yii\db\ActiveRecord
{
    use JsonTrait;

    const TYPE_OWNER_CITY = 1;
    const TYPE_OWNER_CAR = 2;
    const TYPE_SERVICE_MARK = 3;
    const TYPE_SERVICE_TYPE = 4;
    const TYPE_TIRE_SERVICE = 5;
    const TYPE_TYPE_CAR_CHANGE_TIRES = 6;
    const TYPE_TYPE_CAR_SELL_TIRES = 7;
    //На самом деле, это не атрибут, а наличие записи в таблице CompanyClient
    const TYPE_ORGANISATION = 8;

    static $listName = [
        self::TYPE_OWNER_CITY            => ['ru' => 'Города компании', 'en' => 'owner_city'],
        self::TYPE_OWNER_CAR             => ['ru' => 'Машины компании', 'en' => 'owner_car'],
        self::TYPE_SERVICE_MARK          => ['ru' => 'Марки ТС обслуживаемых сервисом', 'en' => 'service_mark'],
        self::TYPE_SERVICE_TYPE          => ['ru' => 'Услуги сервиса', 'en' => 'service_type'],
        self::TYPE_TIRE_SERVICE          => ['ru' => 'Услуги шиномонтажа', 'en' => 'tire_service'],
        self::TYPE_TYPE_CAR_CHANGE_TIRES => ['ru' => 'Типы ТС для шиномонтажа', 'en' => 'type_car_change_tires'],
        self::TYPE_TYPE_CAR_SELL_TIRES   => ['ru' => 'Типы ТС для продажи шин', 'en' => 'type_car_sell_tires'],
        self::TYPE_ORGANISATION          => ['ru' => 'Обслуживаемые организации', 'en' => 'type_organisation'],
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_attributes}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'type'], 'required'],
            [['company_id', 'type'], 'integer'],
            [['value'], 'string'],
            [['name'], 'string', 'max' => 255],
            [
                ['company_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Company::className(),
                'targetAttribute' => ['company_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'company_id' => 'Компания',
            'name'       => 'Название',
            'type'       => 'Тип',
            'value'      => 'Значение',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    /**
     *
     */
    public function afterFind()
    {
        $this->decodeJsonFields([
            'value',
        ]);

        parent::afterFind();
    }

    /**
     * @return bool
     */
    public function beforeValidate()
    {
        $this->encodeJsonFields([
            'value',
        ]);

        return parent::beforeValidate();
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!$this->name) {
            $this->name = self::$listName[$this->type]['ru'];
        }

        return parent::beforeSave($insert);
    }

    public function getTemplate()
    {
        $type = '_' . self::$listName[$this->type]['en'];
        $template = '/company-attribute/' . $type;
        $fullTemplate = Yii::getAlias('@backend') . '/views' . $template . '.php';
        if (is_file($fullTemplate)) {
            return $template;
        }

        return false;
    }
}
