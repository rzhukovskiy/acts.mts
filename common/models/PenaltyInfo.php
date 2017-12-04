<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "penalty_info".
 *
 * @property integer $id
 * @property integer $pen_id
 * @property integer $car_id
 * @property integer $act_id
 * @property integer $company_id
 * @property string $description
 * @property string $postNumber
 * @property string $postedAt
 * @property string $violationAt
 * @property string $amount
 * @property string $totalAmount
 * @property integer $isDiscount
 * @property string $discountDate
 * @property string $discountSize
 * @property integer $isExpired
 * @property string $penaltyDate
 * @property integer $isPaid
 * @property string $docType
 * @property string $docNumber
 * @property integer $enablePics
 * @property string $pics
 */
class PenaltyInfo extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%penalty_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pen_id', 'act_id', 'car_id', 'company_id', 'description', 'postNumber', 'postedAt', 'violationAt', 'amount', 'totalAmount', 'penaltyDate'], 'required'],
            [['pen_id', 'car_id', 'company_id', 'isDiscount', 'isExpired', 'isPaid', 'enablePics'], 'integer'],
            [['description', 'pics'], 'string'],
            [['amount', 'totalAmount'], 'number'],
            [['postNumber', 'violationAt', 'discountDate', 'penaltyDate', 'docType'], 'string', 'max' => 30],
            [['postedAt', 'docNumber'], 'string', 'max' => 20],
            [['discountSize'], 'string', 'max' => 5],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pen_id' => 'ID Штрафа',
            'act_id' => 'ID акта',
            'car_id' => 'Транспортное средство',
            'company_id' => 'Владелец ТС',
            'description' => 'Описание',
            'postNumber' => 'Номер постановления',
            'postedAt' => 'Дата постановления',
            'violationAt' => 'Дата/время нарушения',
            'amount' => 'Сумма штрафа',
            'totalAmount' => 'Итоговая сумма',
            'discountSize' => 'Скидка',
            'isDiscount' => 'Доступна скидка',
            'discountDate' => 'Дата скидки',
            'isExpired' => 'Оплата просрочена',
            'penaltyDate' => 'Дата окончания оплаты',
            'isPaid' => 'Оплачен',
            'docType' => 'Тип документа',
            'docNumber' => 'Номер документа',
            'enablePics' => 'Имеются ли изображения?',
            'pics' => 'Список изображений',
        ];
    }
}
