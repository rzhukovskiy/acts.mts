<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "expense_company".
 *
 * @property integer $id
 * @property integer $type
 * @property string $name
 */
class ExpenseCompany extends ActiveRecord
{
    const TYPE_PAY = 1;
    const TYPE_CREDIT = 2;
    const TYPE_COMMUNICATION = 3;
    const TYPE_RENTALS = 4;
    const TYPE_MUNICIPAL = 5;
    const TYPE_STATIONERY = 6;
    const TYPE_MAIL = 7;
    const TYPE_TYPOGRAPH = 8;
    const TYPE_TECHNIK = 9;
    const TYPE_FURNITURE = 10;
    const TYPE_OTHERS = 11;

    static $listType = [
        self::TYPE_PAY     => [
            'en' => 'pay',
            'ru' => 'Заработная плата',
        ],
        self::TYPE_CREDIT      => [
            'en' => 'credit',
            'ru' => 'Кредит',
        ],
        self::TYPE_COMMUNICATION   => [
            'en' => 'communication',
            'ru' => 'Связь',
        ],
        self::TYPE_RENTALS     => [
            'en' => 'rentals',
            'ru' => 'Аренда',
        ],
        self::TYPE_MUNICIPAL => [
            'en' => 'municipal',
            'ru' => 'Коммунальные платежи',
        ],
        self::TYPE_STATIONERY => [
            'en' => 'stationery',
            'ru' => 'Канцелярские товары',
        ],
        self::TYPE_MAIL => [
            'en' => 'mail',
            'ru' => 'Почта',
        ],
        self::TYPE_TYPOGRAPH => [
            'en' => 'tipography',
            'ru' => 'Типография',
        ],
        self::TYPE_TECHNIK => [
            'en' => 'technik',
            'ru' => 'Техника',
        ],
        self::TYPE_FURNITURE => [
            'en' => 'furniture',
            'ru' => 'Мебель',
        ],
        self::TYPE_OTHERS => [
            'en' => 'others',
            'ru' => 'Прочее',
        ],
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%expense_company}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name'], 'required'],
            [['type'], 'integer'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Тип',
            'name' => 'Имя',
        ];
    }
}
