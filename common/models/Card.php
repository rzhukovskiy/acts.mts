<?php
namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Card model
 *
 * @property int $id
 * @property int $company_id
 * @property int $number
 * @property int $status
 * @property int $is_lost
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

    const TYPE_FREE = 0;
    const TYPE_NON_FREE = 1;

    public $car_number;
    public $car_type;
    public $car_mark;

    public $cardStatus = [
        'Активна',
        'Заблокирована'
    ];

    static $cardType = [
        self::TYPE_FREE     => 'Свободны',
        self::TYPE_NON_FREE => 'Заняты',
    ];

    public static function markLost($number)
    {
        $model = self::findOne(['number' => $number]);

        if ($model) {
            $model->is_lost = 1;
            $model->save();
        }
    }

    public static function markFounded($number)
    {
        $model = self::findOne(['number' => $number]);

        if ($model) {
            $model->is_lost = 0;
            $model->save();
        }
    }

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
            [['is_lost'], 'safe'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'number'     => 'Номер карты',
            'company_id' => 'Компания',
            'status'     => 'Статус',
            'created_at' => 'Создана',
            'updated_at' => 'Изменена',
            'company'    => 'Компания',
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert && !$this->number) {
            $salt = self::randomSalt();
            $this->number = $salt . str_pad($this->company_id, 4, "0", STR_PAD_LEFT);
        } elseif ($insert) {
            $numPointList = explode(',', $this->number);
            if (count($numPointList) > 1) {
                foreach ($numPointList as $range) {
                    $card = clone $this;
                    $card->number = $range;
                    $card->save();
                }
            }

            $numPointList = explode('-', $this->number);
            if (count($numPointList) > 1) {
                for ($num = intval($numPointList[0]); $num < intval($numPointList[1]); $num++) {
                    $card = clone $this;
                    $card->number = $num;
                    $card->save();
                }
                $this->number = intval($numPointList[1]);
            }

            $existed = Card::findOne(['number' => $this->number]);
            if ($existed) {
                if ($existed->company_id != $this->company_id) {
                    //делаем актам с этой картой статус починенных,
                    //чтобы не вызвало ошибку не совпадения владельца карты и машины
                    Act::updateAll(['status' => Act::STATUS_FIXED], ['card_id' => $existed->id]);

                    $existed->company_id = $this->company_id;
                    $existed->save();
                }

                return false;
            }
        }

        return true;
    }

    public function randomSalt()
    {
        return str_pad(rand(1, 9999), 4, "0", STR_PAD_RIGHT);
    }

    /**
     * @return array
     */
    static public function getRange()
    {
        $cards =
            Card::find()
                ->select('number, company_id, company.name as company_name')
                ->joinWith('company')
                ->andWhere(['company.status' => Company::STATUS_ACTIVE])
                ->orderBy(['number' => SORT_ASC])
                ->indexBy('number')
                ->asArray()
                ->all();

        $max = array_pop($cards);
        array_push($cards, $max);
        $arr = [];
        $free = [];
        $nonFree = [];
        $company = false;
        $data = [];
        $count = -1;

        for ($i = 1; $i <= $max['number']; $i++) {
            $count++;
            //Для выборки
            if (isset($cards[$i])) {
                if (count($free) != 0) {
                    $arr[] = [$free[0], $i - 1, self::TYPE_FREE, $count];
                    $free = [];
                    $count = 0;
                }
                if (!$company) {
                    $company = $cards[$i]['company_name'];
                }
                if ($company != $cards[$i]['company_name']) {
                    $arr[] = [$nonFree[0], $i - 1, self::TYPE_NON_FREE, $count, $company];
                    $nonFree = [];
                    $company = ($cards[$i]['company_name']) ? $cards[$i]['company_name'] : false;
                    $count = 0;
                }

                if (count($nonFree) == 0) {
                    $nonFree[] = $i;
                }
                //Для отсутствующих
            } else {
                if (count($nonFree) != 0) {
                    $arr[] = [$nonFree[0], $i - 1, self::TYPE_NON_FREE, $count, $company];
                    $nonFree = [];
                    $company = false;
                    $count = 0;
                }
                if (count($free) == 0) {
                    $free[] = $i;
                }
            }
        }
        if (count($nonFree) != 0) {
            $arr[] = [$nonFree[0], $i, self::TYPE_NON_FREE, $count, $company];
        }
        if (count($free) != 0) {
            $arr[] = [$free[0], $i, $count, self::TYPE_FREE];
        }
        ArrayHelper::multisort($arr, 2);
        foreach ($arr as $val) {
            $data[] = [
                'type'         => $val[2],
                'val'          => ($val[0] != $val[1]) ? ($val[0] . ' - ' . $val[1]) : $val[0],
                'count'        => $val[3],
                'company_name' => ArrayHelper::getValue($val, 4 , 'Нет компании'),
            ];
        }

        return $data;
    }
}