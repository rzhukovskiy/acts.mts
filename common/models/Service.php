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
            'en' => 'wash',
            'ru' => 'услуги мойки',
        ],
        self::TYPE_SERVICE => [
            'en' => 'service',
            'ru' => 'услуги сервиса',
        ],
        self::TYPE_TIRES => [
            'en' => 'tires',
            'ru' => 'услуги шиномонтажа',
        ],
        self::TYPE_DISINFECT => [
            'en' => 'disinfect',
            'ru' => 'услуги дезинфекции',
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
            [['description', 'company_id'], 'required'],
            ['type', 'default', 'value' => self::TYPE_WASH],
        ];
    }
}
