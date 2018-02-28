<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "company_address".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $type
 * @property string $address
 */
class CompanyAddress extends \yii\db\ActiveRecord
{
    const FACT = 1;
    const URIDICH = 2;
    const POCHTA = 3;
    const ZACAZCHIK = 4;

    public static $listType = [
        self::FACT => 'Фактический',
        self::URIDICH => 'Юридический',
        self::POCHTA => 'Почтовый',
        self::ZACAZCHIK => 'Заказчика',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_address}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'type', 'address'], 'required'],
            [['company_id', 'type'], 'integer'],
            [['address'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'ID Компании',
            'type' => 'Тип',
            'address' => 'Адрес',
        ];
    }
}
