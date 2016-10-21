<?php
namespace common\models;

use common\models\query\CompanyQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Company model
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property string $address
 * @property string $director
 * @property integer $type
 * @property integer $status
 * @property integer $is_split
 * @property integer $is_infected
 * @property integer $is_main
 * @property integer $is_sign
 * @property integer $schedule
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property CompanyInfo $info
 * @property CompanyOffer $offer
 * @property Act[] $acts
 * @property Company $parent
 * @property Company[] $children
 * @property Card[] $cards
 * @property Car[] $cars
 * @property Requisites[] $requisites
 * @property CompanyServiceType[] $serviceTypes
 * @property Entry[] $entries
 *
 * @property string $cardList
 * @property array $requisitesList
 * @property array $serviceList
 */
class Company extends ActiveRecord
{
    public $cardList;
    public $requisitesList;
    private $serviceList;

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
            [['parent_id', 'director', 'is_split', 'is_sign', 'cardList', 'requisitesList', 'serviceList', 'schedule'], 'safe'],
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
            'cardList' => 'Список карт',
            'is_split' => 'Разделять прицеп',
            'schedule' => 'По записи',
            'is_sign' => 'Подпись',
            'director' => 'Директор',
            'serviceList' => 'Сервисы',
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
     * @return ActiveQuery
     */
    public function getInfo()
    {
        return $this->hasOne(CompanyInfo::className(), ['company_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getOffer()
    {
        return $this->hasOne(CompanyOffer::className(), ['company_id' => 'id']);
    }

    /**
     * @param $type_id integer
     * @return ActiveQuery
     */
    public function getDurationByType($type_id)
    {
        return $this->hasMany(CompanyDuration::className(), ['company_id' => 'id'])->where(['type_id' => $type_id]);
    }

    /**
     * @return ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Company::className(), ['id' => 'parent_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(Company::className(), ['parent_id' => 'id']);
    }

    /**
     * @param string $day
     * @return ActiveQuery
     */
    public function getEntries($day)
    {
        return $this->hasMany(Entry::className(), ['company_id' => 'id'])->where(['DAY(FROM_UNIXTIME(`start_at`))' => $day])->orderBy('start_at');
    }

    /**
     * @return ActiveQuery
     */
    public function getCards()
    {
        return $this->hasMany(Card::className(), ['company_id' => 'id'])->orderBy('number');
    }

    /**
     * @return ActiveQuery
     */
    public function getCars()
    {
        return $this->hasMany(Car::className(), ['company_id' => 'id'])->orderBy('number');
    }

    /**
     * @return ActiveQuery
     */
    public function getActs()
    {
        if ($this->type == self::TYPE_OWNER) {
            return $this->hasMany(Act::className(), ['client_id' => 'id']);
        } else {
            return $this->hasMany(Act::className(), ['partner_id' => 'id']);
        }
    }

    /**
     * @return ActiveQuery
     */
    public function getRequisites()
    {
        return $this->hasMany(Requisites::className(), ['company_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getServiceTypes()
    {
        return $this->hasMany(CompanyServiceType::className(), ['company_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCompanyExclude()
    {
        return $this->hasMany(CompanyExclude::className(), ['company_id' => 'id']);
    }

    public function getCarsCount()
    {
        return count($this->getCars()->where('type_id != 7')->all());
    }

    public function getTrucksCount()
    {
        return count($this->getCars()->where('type_id = 7')->all());
    }

    public function getRequisitesByType($service_type, $field)
    {
        foreach ($this->requisites as $requisites) {
            if ($requisites->type == $service_type && isset($requisites->$field)) {
                return $requisites->$field;
            }
        }

        if ($service_type != Service::TYPE_WASH) {
            return $this->getRequisitesByType(Service::TYPE_WASH, $field);
        }
        return false;
    }

    public function getServiceList()
    {
        if ($this->serviceTypes) {
            return ArrayHelper::map($this->serviceTypes,'type', 'type');
        }
        return [];
    }

    public function setServiceList($value)
    {
        $this->serviceList = $value;
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
            if ($i == $cnt && $card->number - 1 == $previous) {
                $range .= $card->number;
            }
            $previous = $card->number;
        }

        return $range;
    }

    /**
     * @param $day
     * @return Entry[]
     */
    public function getFreeTimeArray($day)
    {
        if (!count($this->getEntries($day)->all())) {
            return [];
        }
        $workStart = $this->info->start_at ? date('H:i', $this->info->start_at) : '00:00';
        $workEnd = $this->info->end_at ? date('H:i', $this->info->end_at) : '24:00';


        $points[] = [
            'value' => '00:00',
            'type' => 's',
        ];
        $points[] = [
            'value' => $workStart,
            'type' => 'e',
        ];
        $points[] = [
            'value' => $workEnd,
            'type' => 's',
        ];
        $points[] = [
            'value' => '24:00',
            'type' => 'e',
        ];
        /** @var Entry $entry */
        foreach($this->getEntries($day)->all() as $entry) {
            $points[] = [
                'value' => date('H:i', $entry->start_at),
                'type' => 's',
            ];
            $points[] = [
                'value' => date('H:i', $entry->end_at),
                'type' => 'e',
            ];
        }

        usort($points, function($first, $second) {
            if ($first['value'] == $second['value']) {
                return $first['type'] < $second['type'];
            } else {
                return $first['value'] > $second['value'];
            }
        });

        $res = [];
        $i = 0;
        $j = 0;
        foreach ($points as $point) {
            if ($point['type'] == 'e' && $j < count($points) - 1) {
                $res[$i]['start'] = $point['value'];
            }
            if ($point['type'] == 's' && !empty($res[$i]['start'])) {
                if ($res[$i]['start'] == $point['value']) {
                    unset($res[$i]['start']);
                } else {
                    $res[$i]['end'] = $point['value'];
                    $i++;
                }
            }
            $j++;
        }

        return $res;
    }

    /**
     * Все картнеры клиента определенного типа и города
     * @param $type
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCompanyPartner($type)
    {
        $partner = Company::find()->select(['id', 'name'])->byType($type)->byAddress($this->address)->all();;
        $partner = ArrayHelper::map($partner, 'id', 'name');

        return $partner;
    }

    /**
     * Исключаемые партнеры
     * @return array
     */
    public function getExcludedIds()
    {
        $ids = PartnerExclude::find()->select(['partner_id'])->column();

        return $ids;
    }

    /**
     * Инвертирование исключаемых партнеров
     * @param $type
     * @param $excludeIds
     * @return array
     */
    public function getInvertIds($type, $excludeIds)
    {
        $ids = array_keys($this->getCompanyPartner($type));

        return array_diff($ids, $excludeIds);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        /**
         * смотрим есть ли карты и сохраняем
         */
        if (!empty($this->cardList)) {
            $card = new Card();
            $card->company_id = $this->id;
            $card->number = $this->cardList;
            $card->save();
        }

        /**
         * смотрим есть ли сервисы и сохраняем
         */
        if (!empty($this->serviceList)) {
            CompanyServiceType::deleteAll(['company_id' => $this->id]);
            foreach ($this->serviceList as $serviceTypeId) {
                $serviceType = new CompanyServiceType();
                $serviceType->company_id = $this->id;
                $serviceType->type = $serviceTypeId;
                $serviceType->save();
            }
        }

        /**
         * реквизиты. разделяются по типам сервисов Service::$serviceList
         */
        if (!empty($this->requisitesList)) {
            foreach ($this->requisitesList as $requisitesData) {
                if (!empty($requisitesData['Requisites']['id'])) {
                    $requisites = Requisites::findOne(['id' => $requisitesData['Requisites']['id']]);
                } else {
                    $requisites = new Requisites();
                }
                $requisites->load($requisitesData);
                $requisites->company_id = $this->id;
                $requisites->save();
            }
        }
    }

    /**
     * Набор данных для выпадайки
     *
     * @param null|integer $type
     * @param boolean $useUniversal
     * @return array
     */
    public static function dataDropDownList($type = null, $useUniversal = false)
    {
        $query = static::find();

        if (!is_null($type)) {
            $query = $query->andWhere(['{{%company}}.type' => $type]);
            //Если успользуем универсальные компании, то проверяем их на подходящий тип и подмешиваем
            if ($useUniversal) {
                $query = $query->joinWith([
                    'serviceTypes service_type',
                ]);
                $query = $query->orWhere(['service_type.type' => $type]);
            }
        }

        $query = $query->asArray()->all();

        return ArrayHelper::map($query, 'id', 'name');
    }

    /**
     * Эмулируем софт-делит
     *
     * @return bool
     */
    public function beforeDelete()
    {
        $this->status = self::STATUS_DELETED;
        $this->save();

        return false;
    }

    /**
     * @param $type integer
     * @return ActiveDataProvider
     */
    public function getPriceDataProvider($type)
    {
        return new ActiveDataProvider([
            'query' => CompanyService::find()->joinWith('service')->where(['type' => $type, 'company_id' => $this->id])->groupBy('`price` + `service_id`'),
            'pagination' => false,
            'sort' => [
                'defaultOrder' => [
                    'type_id' => SORT_DESC,
                ]
            ],
        ]);
    }

    /**
     * @return ActiveDataProvider
     */
    public function getDurationDataProvider()
    {
        return new ActiveDataProvider([
            'query'      => CompanyDuration::find()->where(['company_id' => $this->id]),
            'pagination' => false,
            'sort'       => [
                'defaultOrder' => [
                    'type_id' => SORT_DESC,
                ]
            ],
        ]);
    }

    /**
     * @param $type integer
     * @return ActiveDataProvider
     */
    public function getMergedPriceDataProvider($type)
    {
        return new ActiveDataProvider([
            'query' => CompanyService::find()->joinWith('service')->where(['type' => $type, 'company_id' => $this->id])->groupBy('`type_id`'),
            'pagination' => false,
            'sort' => [
                'defaultOrder' => [
                    'type_id' => SORT_DESC,
                ]
            ],
        ]);
    }

    /**
     * @return ActiveDataProvider
     */
    public function getCarDataProvider()
    {
        return new ActiveDataProvider([
            'query' => Car::find()->where(['company_id' => $this->id]),
            'pagination' => false,
            'sort' => [
                'defaultOrder' => [
                    'number' => SORT_DESC,
                ]
            ],
        ]);
    }
}
