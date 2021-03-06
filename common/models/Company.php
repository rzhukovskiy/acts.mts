<?php
namespace common\models;

use common\components\DateHelper;
use common\models\query\CompanyQuery;
use common\models\DepartmentCompany;
use common\models\search\CarSearch;
use frontend\models\Penalty;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
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
 * @property integer $is_nested
 * @property integer $status
 * @property integer $is_split
 * @property integer $is_infected
 * @property integer $is_main
 * @property integer $is_sign
 * @property integer $is_act_sign
 * @property integer $schedule
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $old_id
 * @property integer $use_penalty
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
 * @property CompanyAttributes[] $companyAttribute
 * @property CompanyMember[] $members
 * @property CompanyClient[] $companyClient
 * @property CompanyTime[] $companyTime
 *
 * @property string $cardList
 * @property array $requisitesList
 * @property array $serviceList
 * @property string $workTime
 * @property string $car_type
 */
class Company extends ActiveRecord
{
    public $cardList;
    public $requisitesList;
    public $services;
    public $cartypes;

    private $workTime;
    private $car_type;
    private $serviceList;
    private $fullAddress;
    private $depart_user_name;
    private $tender_user_name;

    const STATUS_DELETED = 0;
    const STATUS_NEW = 1;
    const STATUS_ARCHIVE = 2;
    const STATUS_REFUSE = 3;
    const STATUS_ARCHIVE3 = 4;
    const STATUS_TENDER = 5;
    const STATUS_NEW2 = 6;
    const STATUS_ACTIVE = 10;

    const SCENARIO_OFFER = 'offer';
    const SCENARIO_DEFAULT = 'default';

    const IS_NOT_NESTED = 0;
    const IS_NESTED = 1;

    const TYPE_OWNER = 1;
    const TYPE_WASH = 2;
    const TYPE_SERVICE = 3;
    const TYPE_TIRES = 4;
    const TYPE_DISINFECT = 5;
    const TYPE_UNIVERSAL = 6;
    const TYPE_PARKING = 7;
    const TYPE_PENALTY = 8;

    static $listType = [
        self::TYPE_OWNER     => [
            'en' => 'owner',
            'ru' => '????????????????',
        ],
        self::TYPE_WASH      => [
            'en' => 'wash',
            'ru' => '??????????',
        ],
        self::TYPE_SERVICE   => [
            'en' => 'service',
            'ru' => '????????????',
        ],
        self::TYPE_TIRES     => [
            'en' => 'tires',
            'ru' => '????????????????????',
        ],
        self::TYPE_DISINFECT => [
            'en' => 'disinfect',
            'ru' => '??????????????????????',
        ],
        self::TYPE_UNIVERSAL => [
            'en' => 'universal',
            'ru' => '??????????????????????????',
        ],
        self::TYPE_PARKING => [
            'en' => 'parking',
            'ru' => '??????????????',
        ],
        self::TYPE_PENALTY => [
            'en' => 'penalty',
            'ru' => '????????????',
        ],
    ];

    static $subTypeService = [
        self::TYPE_OWNER => [
            'en' => 'service',
            'ru' => '????????????',
        ],
        self::TYPE_WASH => [
            'en' => 'evacuator',
            'ru' => '??????????????????',
        ],
        self::TYPE_SERVICE => [
            'en' => 'shop',
            'ru' => '?????????????? ??????????????????',
        ],
        self::TYPE_TIRES => [
            'en' => 'refrigeration',
            'ru' => '?????????????????????? ????????????????????????',
        ],
        self::TYPE_DISINFECT => [
            'en' => 'repair',
            'ru' => '???????????? ????????????????',
        ],
        self::TYPE_UNIVERSAL => [
            'en' => 'autonom',
            'ru' => '??????????????????',
        ],
    ];

    static $listStatus = [
        self::STATUS_NEW     => [
            'en' => 'new',
            'ru' => '????????????',
        ],
        self::STATUS_NEW2     => [
            'en' => 'new2',
            'ru' => '???????????? 2',
        ],
        self::STATUS_ACTIVE  => [
            'en' => 'archive',
            'ru' => '????????????????',
        ],
        self::STATUS_ARCHIVE  => [
            'en' => 'archive',
            'ru' => '??????????',
        ],
        self::STATUS_REFUSE  => [
            'en' => 'refuse',
            'ru' => '?????????? 2',
        ],
        self::STATUS_ARCHIVE3  => [
            'en' => 'archive3',
            'ru' => '?????????? 3',
        ],
        self::STATUS_TENDER  => [
            'en' => 'tender',
            'ru' => '??????????????',
        ],
        self::STATUS_DELETED => [
            'en' => 'deleted',
            'ru' => '??????????????????',
        ],
    ];

    //???????????? ?? ?????????? ?????????? ???????????????? ?????????? ????????????????
    static $listCompanyAttributes = [
        self::TYPE_OWNER   => [
            CompanyAttributes::TYPE_OWNER_CITY,
            CompanyAttributes::TYPE_OWNER_CAR
        ],
        self::TYPE_WASH    => [
            CompanyAttributes::TYPE_ORGANISATION
        ],
        self::TYPE_SERVICE => [
            CompanyAttributes::TYPE_SERVICE_MARK,
            CompanyAttributes::TYPE_SERVICE_TYPE,
            CompanyAttributes::TYPE_ORGANISATION
        ],
        self::TYPE_TIRES   => [
            CompanyAttributes::TYPE_TIRE_SERVICE,
            CompanyAttributes::TYPE_TYPE_CAR_CHANGE_TIRES,
            CompanyAttributes::TYPE_TYPE_CAR_SELL_TIRES,
            CompanyAttributes::TYPE_ORGANISATION,

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
            [['name'], 'unique'],
            [['is_nested', 'car_type', 'use_penalty'], 'integer'],
            [
                [
                    'parent_id',
                    'director',
                    'is_split',
                    'is_sign',
                    'is_act_sign',
                    'cardList',
                    'requisitesList',
                    'serviceList',
                    'schedule',
                    'workTime',
                ],
                'safe'
            ],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['type', 'default', 'value' => self::TYPE_OWNER],
            ['car_type', 'default', 'value' => 0],
            ['status', 'in', 'range' => [self::STATUS_REFUSE, self::STATUS_ARCHIVE, self::STATUS_ARCHIVE3, self::STATUS_TENDER, self::STATUS_ACTIVE, self::STATUS_DELETED, self::STATUS_NEW, self::STATUS_NEW2]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'name'        => '????????????????',
            'address'     => '??????????',
            'parent_id'   => '????????????????????????',
            'cardList'    => '???????????? ????????',
            'is_split'    => '?????????????????? ????????????',
            'schedule'    => '???? ????????????',
            'is_sign'     => '??????????????',
            'is_act_sign' => '?????????????? ?? ???????????? ?? ????????',
            'director'    => '????????????????',
            'serviceList' => '??????????????',
            'fullAddress' => '??????????',
            'depart_user_name' => 'ID ????????????????????',
            'tender_user_name' => 'ID ????????????????????',
            'expensive' => '??????????????????',
            'workTime'    => '?????????? ????????????',
            'car_type'    => '?????? ????',
            'use_penalty'    => '???????????????? ??????????????',
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
     * @return string
     */
    public function getFullAddress()
    {

        if (!$this->fullAddress) {

            if ($this->info) {

                $arrFullAddress = [];

                if (isset($this->info->city)) {
                    if (mb_strlen($this->info->city) > 0) {
                        $arrFullAddress[] = $this->info->city;
                    }
                }

                if (isset($this->info->street)) {
                    if (mb_strlen($this->info->street) > 0) {
                        $arrFullAddress[] = $this->info->street;
                    }
                }

                if (isset($this->info->house)) {
                    if (mb_strlen($this->info->house) > 0) {
                        $arrFullAddress[] = $this->info->house;
                    }
                }

                if (isset($this->info->index)) {
                    if (mb_strlen($this->info->index) > 0) {
                        $arrFullAddress[] = $this->info->index;
                    }
                }

                $this->fullAddress = implode(', ', $arrFullAddress);

                /*$this->fullAddress = $this->info ? implode(', ',
                    [
                        $this->info->city,
                        $this->info->street,
                        $this->info->house,
                        $this->info->index,
                    ]) : false;*/

            } else {
                return false;
            }

        }

        return $this->fullAddress;

    }

    // ?????? ????????????????????, ?????????????? ?????????????? ????????????????
    public function getdepart_user_name()
    {

        if (!$this->depart_user_name) {
            $userName = DepartmentCompany::find()->where(['`department_company`.`company_id`' => $this->id])->andWhere(['`department_company`.`remove_date`' => null])->andWhere(['!=', '`department_company`.`user_id`', 0])->leftJoin('`user`', '`user`.`id` = `department_company`.`user_id`')->select('`user`.`username`')->column();

            if(isset($userName[0])) {
                $this->depart_user_name = $userName[0];
            }

        }

        return $this->depart_user_name;
    }

    // ?????? ????????????????????, ?????????????? ?????????????? ????????????
    public function getTender_user_name()
    {

        if (!$this->tender_user_name) {
            $userName = TenderHystory::find()->where(['`tender_hystory`.`company_id`' => $this->id])->andWhere(['`tender_hystory`.`remove_date`' => null])->andWhere(['!=', '`tender_hystory`.`user_id`', 0])->leftJoin('`user`', '`user`.`id` = `tender_hystory`.`user_id`')->select('`user`.`username`')->column();

            if(isset($userName[0])) {
                $this->tender_user_name = $userName[0];
            }

        }

        return $this->tender_user_name;
    }

    public function setFullAddress($value)
    {
        $this->fullAddress = $value;
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
        return $this->hasMany(Entry::className(), ['company_id' => 'id'])
            ->where(['DATE_FORMAT(FROM_UNIXTIME(start_at), "%d-%m-%Y")' => $day])
            ->orderBy('start_at');
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
    public function getCompanyServices()
    {
        return $this->hasMany(CompanyService::className(), ['company_id' => 'id'])->orderBy('type_id');
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
    public function getMembers()
    {
        return $this->hasMany(CompanyMember::className(), ['company_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCompanyAttribute()
    {
        return $this->hasMany(CompanyAttributes::className(), ['company_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCompanyClient()
    {
        return $this->hasMany(CompanyClient::className(), ['company_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPartnerExclude()
    {
        return $this->hasMany(PartnerExclude::className(), ['company_id' => 'id']);
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
            return ArrayHelper::map($this->serviceTypes, 'type', 'type');
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
     * @return ActiveQuery
     */
    public function getCompanyTime()
    {
        return $this->hasMany(CompanyTime::className(), ['company_id' => 'id'])->orderBy('day');
    }

    /**
     * @param string $day
     * @return CompanyTime
     */
    public function getCompanyTimeByDay($day)
    {
        $dayOfWeek = date('w',strtotime($day));
        $dayOfWeek = $dayOfWeek == 0 ? 7 : $dayOfWeek;
        return CompanyTime::findOne(['company_id' => $this->id, 'day' => $dayOfWeek]);
    }

    public function setCar_type($value)
    {
        $this->car_type = $value;
    }

    public function getCar_type()
    {
        return $this->car_type;
    }

    public function setWorkTime($value)
    {
        $this->workTime = $value;
    }

    public function getWorkTime()
    {
        $res = [];
        foreach ($this->companyTime as $day) {
            $res[] = gmdate('H:i', $day->start_at) . ' - ' . gmdate('H:i', $day->end_at);
        }

        return implode("\n", $res);
    }
    public function getWorkTimeArray()
    {
        $res = [];
        foreach ($this->companyTime as $workDay) {
            $res[$workDay->day]['start_time'] = gmdate('H:i', $workDay->start_at);
            $res[$workDay->day]['end_time'] = gmdate('H:i', $workDay->end_at);
        }

        return $res;
    }

    public function getWorkTimeHtml()
    {
        $res = [];

        foreach ($this->companyTime as $workDay) {
            $res[$workDay->day] = DateHelper::getWeekDayName($workDay->day) . ': ';
            if (($workDay->end_at - $workDay->start_at) / 3600 == 24) {
                $res[$workDay->day] .= '??????????????????????????';
            } else {
                $res[$workDay->day] .= gmdate('H:i', $workDay->start_at) . ' - ' . gmdate('H:i', $workDay->end_at);
            }
        }

        for ($day = 1; $day < 8; $day++) {
            if (empty($res[$day])) {
                $res[$day] = DateHelper::getWeekDayName($day) . ': <span class="text-danger">????????????????</span>';
            }
        }

        ksort($res);
        return implode("<br />", $res);
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
        $modelCompanyTime = $this->getCompanyTimeByDay($day);

        if(isset($modelCompanyTime)) {
            $workStart = $modelCompanyTime->start_at ? gmdate('H:i', $modelCompanyTime->start_at) : '00:00';
            $workEnd = $modelCompanyTime->end_at ? gmdate('H:i', $modelCompanyTime->end_at) : '24:00';
        } else {
            $workStart = '00:00';
            $workEnd = '24:00';
        }

        $points[] = [
            'value' => '00:00',
            'type'  => 's',
        ];
        $points[] = [
            'value' => $workStart,
            'type'  => 'e',
        ];
        $points[] = [
            'value' => $workEnd,
            'type'  => 's',
        ];
        $points[] = [
            'value' => '24:00',
            'type'  => 'e',
        ];
        /** @var Entry $entry */
        foreach ($this->getEntries($day)->all() as $entry) {
            $points[] = [
                'value' => date('H:i', $entry->start_at),
                'type'  => 's',
            ];
            $points[] = [
                'value' => date('H:i', $entry->end_at),
                'type'  => 'e',
            ];
        }

        usort($points,
            function ($first, $second) {
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
     * ?????? ???????????????? ?????????????? ?????????????????????????? ???????? ?? ????????????
     * @param $type
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getCompanyPartner($type)
    {
        $partner = Company::find()->where(['OR', ['status' => 10],['status' => 2]])->select(['id', 'name'])->byType($type)->all();
        $partner = ArrayHelper::map($partner, 'id', 'name');

        return $partner;
    }

    /**
     * ?????????????????????? ????????????????
     * @return array
     */
    public function getExcludedIds($id)
    {
        $ids = PartnerExclude::find()->where(['client_id' => $id])->select(['partner_id'])->column();

        return $ids;
    }

    /**
     * ???????????????????????????? ?????????????????????? ??????????????????
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
     * @return bool
     */
    public function beforeSave($insert)
    {

        // ???????????????? ??????????????
        if($this->use_penalty == 1) {

            $modelInfo = CompanyInfo::findOne(['company_id' => $this->id]);

            if (isset($modelInfo)) {

                if (mb_strlen($modelInfo->inn) > 3) {
                } else {
                    $this->use_penalty = 0;
                }

            } else {
                $this->use_penalty = 0;
            }
        }
        // ???????????????? ??????????????

        if (!empty($this->parent_id)) {
            $this->is_nested = self::IS_NESTED;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {

        // ???????????????? ??????????????
        if($this->use_penalty == 1) {

            $modelInfo = CompanyInfo::findOne(['company_id' => $this->id]);

            if(isset($modelInfo)) {

                if(mb_strlen($modelInfo->inn) > 3) {

                    $modelPenalty = new Penalty();
                    $modelPenalty->createToken();

                    // ???????????????? ??????????
                    $token = $modelPenalty->createToken();
                    $resToken = json_decode($token[1], true);

                    // ?????????????????? ???????????????????? ??????????
                    $modelPenalty->setParams(['token' => $resToken['token']]);

                    // ?????????????? ???????????? ??????????????
                    $newClient = $modelPenalty->createClient($this->id . '@mtransservice.ru', $this->name, $modelInfo->inn);
                    $resNewClient = json_decode($newClient[1], true);

                    if(isset($resNewClient['errors'])) {
                        // ????????????

                        if(isset($resNewClient['errors']['email'])) {
                        } else {
                            Company::updateAll(['use_penalty' => 0], 'id = ' . $this->id);
                        }

                    } else {

                        // ???????????? ????????????

                        // ???????????????????? ????
                        $resCars = Car::find()->where(['company_id' =>$this->id])->andWhere(['not', ['cert' => null]])->select('number, cert')->orderBy('id')->asArray()->all();

                        for($i = 0; $i < count($resCars); $i++) {

                            $addCars = $modelPenalty->createClientCar($this->id . '@mtransservice.ru', ['name' => '', 'cert' => $resCars[$i]['cert'], 'reg' => $resCars[$i]['number']]);
                            $resAddCars = json_decode($addCars[1], true);

                            if(isset($resAddCars['errors'])) {
                                // ????????????
                            } else {
                                Car::updateAll(['is_penalty' => 1], 'company_id = ' . $this->id . ' AND number="' . $resCars[$i]['number'] . '"');
                            }

                        }

                        // ???????????????????? ????

                    }

                } else {
                }

            } else {
            }

        } else {

            // ???????????? ????????????????????????????????, ???????? ?????????????? ?????????????? ?????????? ????????????????

            // ?????? ???????????????????? ?????????????? ?? ?????? ?? ??????????????, ?????????????? ?????? ???????????????? ???? ???????? ????????????????????
            /*if (!$insert) {

                $modelPenalty = new Penalty();
                $modelPenalty->createToken();

                // ???????????????? ??????????
                $token = $modelPenalty->createToken();
                $resToken = json_decode($token[1], true);

                // ?????????????????? ???????????????????? ??????????
                $modelPenalty->setParams(['token' => $resToken['token']]);

                // ???????????????? ??????????????
                $delCliend = $modelPenalty->deleteClient($this->id . '@mtransservice.ru');
                $resDelClient = json_decode($delCliend[1], true);

                if (isset($resDelClient['errors'])) {
                    // ????????????
                    Company::updateAll(['use_penalty' => 1], 'id = ' . $this->id);
                } else {
                    // ???????????? ????????????

                    Car::updateAll(['is_penalty' => 0], 'company_id = ' . $this->id);

                }

            }*/
            // ???????????? ????????????????????????????????, ???????? ?????????????? ?????????????? ?????????? ????????????????
        }
        // ???????????????? ??????????????

        //???????????????? ???????????????????????? ????????????????
        if ($this->is_nested == self::IS_NESTED &&
            !empty($this->parent->children) &&
            $this->parent->is_nested == self::IS_NOT_NESTED
        ) {
            $this->parent->is_nested = self::IS_NESTED;
            $this->parent->save();
        }

        if ($this->info && $this->info->city != $this->address) {
            $this->info->city = $this->address;
            $this->info->save();
        }

        /**
         * ?????????????????? ?????????? ????????????
         */
        if (!empty($this->workTime)) {
            CompanyTime::deleteAll(['company_id' => $this->id]);

            if (is_array($this->workTime)) {
                if ($this->workTime['type'] == CompanyTime::TYPE_EVERYDAY) {
                    $arrayWorkTime = [];
                    for ($day = 1; $day < 8; $day++) {
                        $arrayWorkTime[$day]['start_time'] = $this->workTime['start_time'];
                        $arrayWorkTime[$day]['end_time'] = $this->workTime['end_time'];
                    }

                    $this->workTime = $arrayWorkTime;
                } elseif ($this->workTime['type'] == CompanyTime::TYPE_WHOLEDAY) {
                    $arrayWorkTime = [];
                    for ($day = 1; $day < 8; $day++) {
                        $arrayWorkTime[$day]['start_time'] = '00:00';
                        $arrayWorkTime[$day]['end_time'] = '24:00';
                    }

                    $this->workTime = $arrayWorkTime;
                }

                unset($this->workTime['type']);
                unset($this->workTime['start_time']);
                unset($this->workTime['end_time']);
                foreach ($this->workTime as $day => $data) {
                    $modelCompanyTime = new CompanyTime();
                    $modelCompanyTime->company_id = $this->id;
                    $modelCompanyTime->day = $day;
                    if ($data['start_time']) {
                        list($hrs, $mnts) = explode(':', trim($data['start_time']));
                        $modelCompanyTime->start_at = $hrs * 3600 + $mnts * 60;
                    }
                    if ($data['end_time']) {
                        list($hrs, $mnts) = explode(':', trim($data['end_time']));
                        $modelCompanyTime->end_at = $hrs * 3600 + $mnts * 60;
                        if (86400 - $modelCompanyTime->end_at <= 600) {
                            $modelCompanyTime->end_at = 86400;
                        }
                    }
                    if ($modelCompanyTime->start_at != $modelCompanyTime->end_at) {
                        $modelCompanyTime->save();
                    }
                }
            } else {
                $day = 1;

                foreach (explode("\n", $this->workTime) as $row) {
                    list($start, $end) = explode('-', $row);
                    $modelCompanyTime = new CompanyTime();
                    $modelCompanyTime->company_id = $this->id;
                    $modelCompanyTime->day = $day;
                    if ($start) {
                        list($hrs, $mnts) = explode(':', trim($start));
                        $modelCompanyTime->start_at = $hrs * 3600 + $mnts * 60;
                    }
                    if ($end) {
                        list($hrs, $mnts) = explode(':', trim($end));
                        $modelCompanyTime->end_at = $hrs * 3600 + $mnts * 60;
                    }
                    $modelCompanyTime->save();
                    $day++;
                }
            }
        }

        /**
         * ?????????????? ???????? ???? ?????????? ?? ??????????????????
         */
        if (!empty($this->cardList)) {
            $card = new Card();
            $card->company_id = $this->id;
            $card->number = $this->cardList;
            $card->save();

            // ???????????????? ???? ?????????????????????? ?? ???????????? ?????? ???????????????? ??????????????????
            $singlCard = true;

            $numPointList = explode(',', $this->cardList);
            if (count($numPointList) > 1) {
                $singlCard = false;
            }

            $numPointList = explode('-', $this->cardList);
            if (count($numPointList) > 1) {
                $singlCard = false;
            }

            if($singlCard == true) {
                // ???????????????????? ?? ?????????????? ?????????????????? ????????
                $newChange = new Changes();
                $newChange->type = Changes::TYPE_CARD;
                $newChange->user_id = Yii::$app->user->identity->id;
                $newChange->old_value = (String) $card->number;
                $newChange->new_value = (String) $card->company_id;
                $newChange->status = Changes::NEW_CARD;
                $newChange->date = (String)time();
                $newChange->save();
                // ???????????????????? ?? ?????????????? ?????????????????? ????????
            }
            // ???????????????? ???? ?????????????????????? ?? ???????????? ?????? ???????????????? ??????????????????

        }

        /**
         * ?????????????? ???????? ???? ?????????????? ?? ??????????????????
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
         * ??????????????????. ?????????????????????? ???? ?????????? ???????????????? Service::$serviceList
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
     * ?????????? ???????????? ?????? ??????????????????
     *
     * @param null|integer $type
     * @param boolean|array $sort
     * @param boolean $useUniversal
     * @param boolean $isActive
     * @return array
     */
    public static function dataDropDownList($type = null, $useUniversal = false, $sort = false, $isActive = false)
    {
        $query = static::find();
        $query->alias('company');
        if ($isActive) {
            $query->active();
        }
        if (!is_null($type)) {
            $query = $query->andWhere(['company.type' => $type]);
            //???????? ???????????????????? ?????????????????????????? ????????????????, ???? ?????????????????? ???? ???? ???????????????????? ?????? ?? ??????????????????????
            if ($useUniversal) {
                $query = $query->joinWith([
                    'serviceTypes service_type',
                ]);
                $query = $query->orWhere(['service_type.type' => $type]);
            }
        }
        if ($type == self::TYPE_OWNER) {
            $query = $query->addSelect(['company.*'])->addParentKey()->orderByParentKey();
        }

        if ($sort) {
            $query->addOrderBy($sort);
        }
        $query = $query->asArray()->active()->all();

        return ArrayHelper::map($query, 'id', 'name');
    }

    /**
     * ?????????????????? ????????-??????????
     *
     * @return bool
     */
    public function beforeDelete()
    {

        // ???????????????? ??????????????
        if($this->use_penalty == 1) {
            $this->use_penalty = 0;

            $modelPenalty = new Penalty();
            $modelPenalty->createToken();

            // ???????????????? ??????????
            $token = $modelPenalty->createToken();
            $resToken = json_decode($token[1], true);

            // ?????????????????? ???????????????????? ??????????
            $modelPenalty->setParams(['token' => $resToken['token']]);

            // ???????????????? ??????????????
            $delCliend = $modelPenalty->deleteClient($this->id . '@mtransservice.ru');
            $resDelClient = json_decode($delCliend[1], true);

            if (isset($resDelClient['errors'])) {
                // ????????????
            } else {
                // ???????????? ????????????

                Car::updateAll(['is_penalty' => 0], 'company_id = ' . $this->id);

            }
        }
        // ???????????????? ??????????????

        $this->status = self::STATUS_DELETED;
        $this->save();

        if ($this->is_nested == self::IS_NESTED &&
            empty($this->parent->children) &&
            $this->parent->is_nested == self::IS_NESTED
        ) {
            $this->parent->is_nested = self::IS_NOT_NESTED;
            $this->parent->save();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isHaveAttribute()
    {
        $attribute = array_key_exists($this->type, self::$listCompanyAttributes);

        return $attribute;
    }

    /**
     * ???????????? ?????? ??????????????????, ?????????????????????????????? ???? ?????????????????? ?? ????????????????????
     * @return mixed
     */
    public static function getSortedItemsForDropdown()
    {
        $list = Company::dataDropDownList(self::TYPE_OWNER, false);

        return $list;
    }

    /**
     * @param $type integer
     * @return ActiveDataProvider
     */
    public function getPriceDataProvider($type)
    {

        if($type == Company::TYPE_TIRES) {

            return new ActiveDataProvider([
                'query' => CompanyService::find()
                    ->joinWith('service')
                    ->where(['type' => $type, 'company_id' => $this->id]),
                'pagination' => false,
                'sort' => [
                    'defaultOrder' => [
                        'type_id' => SORT_DESC,
                    ]
                ],
            ]);

        } else {

            return new ActiveDataProvider([
                'query' => CompanyService::find()
                    ->joinWith('service')
                    ->where(['type' => $type, 'company_id' => $this->id])
                    ->groupBy('`price` + `service_id`'),
                'pagination' => false,
                'sort' => [
                    'defaultOrder' => [
                        'type_id' => SORT_DESC,
                    ]
                ],
            ]);

        }

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
            'query'      => CompanyService::find()
                ->joinWith('service')
                ->where(['type' => $type, 'company_id' => $this->id])
                ->groupBy('`type_id`'),
            'pagination' => false,
            'sort'       => [
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
        $dataProvider = $this->getCarSearchModel()->search(Yii::$app->request->queryParams);
        $dataProvider->sort = [
            'defaultOrder' => [
                'number' => SORT_DESC,
            ]
        ];

        return $dataProvider;
    }

    /**
     * @return \common\models\search\CarSearch
     */
    public function getCarSearchModel()
    {
        $searchModel = new CarSearch(['scenario' => Car::SCENARIO_OWNER]);
        $searchModel->load(Yii::$app->request->queryParams);
        $searchModel->company_id = $this->id;

        return $searchModel;
    }

    /**
     * @return ActiveDataProvider
     */
    public function getCardDataProvider()
    {
        return new ActiveDataProvider([
            'query'      => Card::find()->where(['company_id' => $this->id]),
            'pagination' => false,
            'sort'       => [
                'defaultOrder' => [
                    'number' => SORT_DESC,
                ]
            ],
        ]);
    }
}
