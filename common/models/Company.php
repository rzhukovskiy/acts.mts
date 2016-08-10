<?php
namespace common\models;

use common\models\query\CompanyQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Company model
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property string $address
 * @property string $phone
 * @property string $director
 * @property integer $type
 * @property integer $status
 * @property integer $is_split
 * @property integer $is_infected
 * @property integer $is_main
 * @property integer $is_sign
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Company $parent
 * @property Company[] $children
 * @property Card[] $cards
 */
class Company extends ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_NEW     = 1;
    const STATUS_ARCHIVE = 2;
    const STATUS_REFUSE  = 3;
    const STATUS_ACTIVE  = 10;

    const TYPE_OWNER     = 1;
    const TYPE_WASH      = 2;
    const TYPE_SERVICE   = 3;
    const TYPE_TIRES     = 4;
    const TYPE_DISINFECT = 5;
    const TYPE_UNIVERSAL = 6;

    static $listType = [
        self::TYPE_OWNER => [
            'en' => 'owner',
            'ru' => 'Компания',
        ],
        self::TYPE_WASH => [
            'en' => 'wash',
            'ru' => 'Мойка',
        ],
        self::TYPE_SERVICE => [
            'en' => 'service',
            'ru' => 'Сервис',
        ],
        self::TYPE_TIRES => [
            'en' => 'tires',
            'ru' => 'Шиномонтаж',
        ],
        self::TYPE_DISINFECT => [
            'en' => 'disinfect',
            'ru' => 'Дезинфекция',
        ],
        self::TYPE_UNIVERSAL => [
            'en' => 'universal',
            'ru' => 'Универсальная',
        ],
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company}}';
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
            [['name'], 'required'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['type', 'default', 'value' => self::TYPE_OWNER],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * @return CompanyQuery
     */
    public static function find()
    {
        return new CompanyQuery(get_called_class());
    }

    /**
     * @return Company
     */
    public function getParent()
    {
        return $this->hasOne(Company::className(), ['id' => 'parent_id']);
    }

    /**
     * @return Company[]
     */
    public function getChildren()
    {
        return $this->hasMany(Company::className(), ['parent_id' => 'id']);
    }

    /**
     * @return Card[]
     */
    public function getCards()
    {
        return $this->hasMany(Card::className(), ['company_id' => 'id']);
    }
}
