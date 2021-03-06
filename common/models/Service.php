<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Service model
 *
 * @property integer $id
 * @property string $description
 * @property integer $type
 * @property integer $is_fixed
 * @property integer $created_at
 * @property integer $updated_at
 */
class Service extends ActiveRecord
{
    const TYPE_WASH = Company::TYPE_WASH;
    const TYPE_SERVICE = Company::TYPE_SERVICE;
    const TYPE_TIRES = Company::TYPE_TIRES;
    const TYPE_DISINFECT = Company::TYPE_DISINFECT;
    const TYPE_PARKING = Company::TYPE_PARKING;
    const TYPE_PENALTY = Company::TYPE_PENALTY;

    static $listType = [
        self::TYPE_WASH      => [
            'id' => self::TYPE_WASH,
            'en' => 'wash',
            'ru' => 'Мойка',
            'in' => 'автомойки',
        ],
        self::TYPE_SERVICE   => [
            'id' => self::TYPE_SERVICE,
            'en' => 'service',
            'ru' => 'Сервис',
            'in' => 'сервиса',
        ],
        self::TYPE_TIRES     => [
            'id' => self::TYPE_TIRES,
            'en' => 'tires',
            'ru' => 'Шиномонтаж',
            'in' => 'шиномонтажа',
        ],
        self::TYPE_DISINFECT => [
            'id' => self::TYPE_DISINFECT,
            'en' => 'disinfect',
            'ru' => 'Дезинфекция',
            'in' => 'дезинфекции',
        ],
        self::TYPE_PARKING => [
            'id' => self::TYPE_PARKING,
            'en' => 'parking',
            'ru' => 'Стоянка',
            'in' => 'Стоянки',
        ],
        self::TYPE_PENALTY => [
            'id' => self::TYPE_PENALTY,
            'en' => 'penalty',
            'ru' => 'Штрафы',
            'in' => 'Штрафы',
        ],
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%service}}';
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
            [['description'], 'required'],
            [['is_fixed'], 'safe'],
            ['type', 'default', 'value' => self::TYPE_WASH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'description' => 'Услуга',
            'type'        => 'Тип услуги',
            'is_fixed'    => 'Цена фиксирована',
        ];
    }

    /**
     * @param null $type
     * @return array
     */
    public static function getServiceList($type = null)
    {
        $serviceList = self::find()->select(['description', 'id'])->orderBy('id ASC')->indexBy('id');
        if ($type) {
            $serviceList = $serviceList->where(['type' => $type]);
        }

        return $serviceList->column();
    }
}
