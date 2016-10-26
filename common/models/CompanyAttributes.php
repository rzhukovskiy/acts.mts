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

    static $listName = [
        self::TYPE_OWNER_CITY            => 'Города компании',
        self::TYPE_OWNER_CAR             => 'Машины компании',
        self::TYPE_SERVICE_MARK          => 'Марки ТС обслуживаемых сервисом',
        self::TYPE_SERVICE_TYPE          => 'Услуги сервиса',
        self::TYPE_TIRE_SERVICE          => 'Услуги шиномонтажа',
        self::TYPE_TYPE_CAR_CHANGE_TIRES => 'Типы ТС для шиномонтажа',
        self::TYPE_TYPE_CAR_SELL_TIRES   => 'Типы ТС для продажи шин',
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

    public function beforeSave($insert)
    {
        if (!$this->name) {
            $this->name = self::$listName[$this->type];
        }

        return parent::beforeSave($insert);
    }
}
