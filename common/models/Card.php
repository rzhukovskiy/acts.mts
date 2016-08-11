<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Card model
 *
 * @property int $id
 * @property int $company_id
 * @property int $number
 * @property int $status
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Company $company
 */
class Card extends ActiveRecord
{

    const STATUS_NOT_ACTIVE = 0;
    const STATUS_ACTIVE = 1;

    public $cardStatus = [
        'Активна',
        'Заблокирована'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%card}}';
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
            [['number', 'company_id'], 'required'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'number' => 'Номер карты',
            'company_id' => 'Компания',
            'status' => 'Статус',
            'created_at' => 'Создана',
            'updated_at' => 'Изменена',
            'company' => 'Компания',
        );
    }

// TODO: refactor to Yii2
//
//    public function beforeSave()
//    {
//        if ($this->isNewRecord && !$this->number) {
//            $salt = self::randomSalt();
//            $this->number = $salt . str_pad($this->company_id, 4, "0", STR_PAD_LEFT);
//        } elseif($this->getIsNewRecord()) {
//            $numPointList = explode('-', $this->number);
//            if(count($numPointList) > 1) {
//                for ($num = intval($numPointList[0]); $num < intval($numPointList[1]); $num++) {
//                    $card = clone $this;
//                    $card->number = $num;
//                    $card->save();
//                }
//                $this->number = intval($numPointList[1]);
//            }
//            $existed = Card::model()->find('number = :number', [':number' => $this->number]);
//            if ($existed) {
//                Act::model()->updateAll(['is_fixed' => 1], 'card_id = :card_id', [':card_id' => $existed->id]);
//                $existed->company_id = $this->company_id;
//                $existed->save();
//                return false;
//            }
//        }
//        return true;
//    }

    public function randomSalt()
    {
        return str_pad(rand(1, 9999), 4, "0", STR_PAD_RIGHT);
    }
}