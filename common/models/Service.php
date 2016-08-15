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
    const TYPE_WASH      = Company::TYPE_WASH;
    const TYPE_SERVICE   = Company::TYPE_SERVICE;
    const TYPE_TIRES     = Company::TYPE_TIRES;
    const TYPE_DISINFECT = Company::TYPE_DISINFECT;

    static $listType = [
        self::TYPE_WASH => [
            'id' => self::TYPE_WASH,
            'en' => 'wash',
            'ru' => 'Мойка',
        ],
        self::TYPE_SERVICE => [
            'id' => self::TYPE_SERVICE,
            'en' => 'service',
            'ru' => 'Сервис',
        ],
        self::TYPE_TIRES => [
            'id' => self::TYPE_TIRES,
            'en' => 'tires',
            'ru' => 'Шиномонтаж',
        ],
        self::TYPE_DISINFECT => [
            'id' => self::TYPE_DISINFECT,
            'en' => 'disinfect',
            'ru' => 'Дезинфекция',
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
            'id' => 'ID',
            'description' => 'Услуга',
            'type' => 'Тип услуги',
            'is_fixed' => 'Цена фиксирована',
        ];
    }
}
