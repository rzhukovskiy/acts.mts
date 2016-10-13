<?php
namespace common\models;

use common\components\ArrayHelper;
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
 * @property string $car_number
 * @property string $car_type
 * @property string $car_mark
 *
 * @property Company $company
 */
class Card extends ActiveRecord
{

    const STATUS_NOT_ACTIVE = 0;
    const STATUS_ACTIVE = 1;

    public $car_number;
    public $car_type;
    public $car_mark;

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

    public function beforeSave($insert)
    {
        if ($insert && !$this->number) {
            $salt = self::randomSalt();
            $this->number = $salt . str_pad($this->company_id, 4, "0", STR_PAD_LEFT);
        } elseif($insert) {
            $numPointList = explode('-', $this->number);
            if(count($numPointList) > 1) {
                for ($num = intval($numPointList[0]); $num < intval($numPointList[1]); $num++) {
                    $card = clone $this;
                    $card->number = $num;
                    $card->save();
                }
                $this->number = intval($numPointList[1]);
            }
            $existed = Card::findOne(['number' => $this->number]);
            if ($existed) {
                Act::updateAll(['is_fixed' => 1], ['card_id' => $existed->id]);
                $existed->company_id = $this->company_id;
                $existed->save();
                return false;
            }
        }
        return true;
    }

    public function randomSalt()
    {
        return str_pad(rand(1, 9999), 4, "0", STR_PAD_RIGHT);
    }
}