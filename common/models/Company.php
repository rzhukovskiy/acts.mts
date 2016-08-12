<?php
namespace common\models;

use common\models\query\CompanyQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

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
 *
 * @property string $cardList
 * @property array $requisitesList
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
            [['name', 'address'], 'required'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['type', 'default', 'value' => self::TYPE_OWNER],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'address' => 'Город',
            'parent_id' => 'Родительская',
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

    public function getCarsCount()
    {
        return count(Car::findAll(['company_id' => $this->id]));
    }

    public function getTrucksCount()
    {
        return count(Car::findAll(['company_id' => $this->id]));
    }

    /**
     * @return string
     */
    public function getCardsAsString()
    {
        $range = '';
        $previous = -1;
        $i = 0;
        $cnt = count($this->cards);
        foreach ($this->cards as $card) {
            $i++;
            if ($card->number - 1 == $previous) {
                if (substr($range, -1) != '-') {
                    $range .= '-';
                }
            } else {
                if ($previous > 0) {
                    if (substr($range, -1) == '-') {
                        $range .= $previous . ', ';
                    } else {
                        $range .= ', ';
                    }
                }
                $range .= $card->number;
            }
            if ($i == $cnt) {
                $range .= $card->number;
            }
            $previous = $card->number;
        }

        return $range;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (empty($this->parent_id)) {
            $this->is_main = 1;
        }

        return true;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (!empty($this->cardList)) {
            $card = new Card();
            $card->company_id = $this->id;
            $card->number = $this->cardList;
            $card->save();
        }

        if (!empty($this->requisitesList)) {
            foreach ($this->requisitesList as $requisitesData) {
                $requisites = new Requisites();
                $requisites->load($requisitesData);
                $requisites->save();
            }
        }
    }


    public static function dataDropDownList()
    {
        return ArrayHelper::map(static::find()->asArray()->all(), 'id', 'name');
    }
}