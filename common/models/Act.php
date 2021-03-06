<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.08.2016
 * Time: 0:27
 */

namespace common\models;

use common\components\Translit;
use common\models\query\ActQuery;
use yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;

/**
 * Act model
 * @package common\models
 *
 * @property integer $id
 * @property integer $client_id
 * @property integer $partner_id
 * @property integer $type_id
 * @property integer $type_client
 * @property integer $mark_id
 * @property integer $card_id
 * @property integer $card_number
 * @property integer $car_id
 * @property integer $extra_car_id
 * @property integer $status
 * @property float $expense
 * @property float $income
 * @property float $profit
 * @property integer $service_type
 * @property integer $served_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $check
 * @property string $car_number
 * @property string $extra_car_number
 *
 * @property array $serviceList
 * @property array $clientServiceList
 * @property array $partnerServiceList
 *
 * @property Company $client
 * @property Company $partner
 * @property Type $type
 * @property Mark $mark
 * @property Card $card
 * @property Car $car
 * @property ActError $actErrors
 * @property ActScope $scopes
 * @property ActScope[] $clientScopes
 * @property ActScope[] $partnerScopes
 */
class Act extends ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_CLOSED = 1;
    const STATUS_FIXED = 2;

    const SCENARIO_ERROR = 'error';
    const SCENARIO_LOSSES = 'losses';
    const SCENARIO_ASYNC = 'async';
    const SCENARIO_DOUBLE = 'double';
    const SCENARIO_PARTNER = 'partner';
    const SCENARIO_HISTORY = 'history';
    const SCENARIO_CLIENT = 'client';
    const SCENARIO_CAR = 'car';
    const SCENARIO_CREATE = 'create';

    const ERROR_EXPENSE = 'expense';
    const ERROR_INCOME = 'income';
    const ERROR_CHECK = 'check';
    const ERROR_CARD = 'card';
    const ERROR_CAR = 'car';
    const ERROR_TRUCK = 'track';
    const ERROR_LOST = 'lost';

    const ACT_WIDTH = 1024;
    const ACT_HEIGHT = 768;

    public $serviceList;
    public $clientServiceList;
    public $partnerServiceList;
    public $time_str;
    public $actsCount;
    public $carsCount;
    public $errorMessage = [];
    public $byAdmin = false;
    /**
     * @var UploadedFile
     */
    public $image;

    public static $listStatus = [
        self::STATUS_NEW    => [
            'ru' => '??????????',
            'en' => 'new',
        ],
        self::STATUS_CLOSED => [
            'ru' => '????????????',
            'en' => 'closed',
        ],
        self::STATUS_FIXED  => [
            'ru' => '??????????????????',
            'en' => 'fixed',
        ]
    ];
    /**
     * @var array
     */
    public static $periodList = ['?????? ??????????', '??????????', '??????????????', '??????????????', '??????'];
    public static $listErrors = [
        self::ERROR_EXPENSE,
        self::ERROR_INCOME,
        self::ERROR_CHECK,
        self::ERROR_CARD,
        self::ERROR_CAR,
        self::ERROR_TRUCK,
        self::ERROR_LOST,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%act}}';
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
            [['partner_id', 'car_number'], 'required'],
            ['check', 'filter', 'filter' => 'trim'],
            ['check', 'default'],
            ['check', 'unique'],
            [
                [
                    'extra_number',
                    'card_id',
                    'check',
                    'expense',
                    'income',
                    'profit',
                    'service_type',
                    'serviceList',
                    'time_str',
                    'partnerServiceList',
                    'clientServiceList',
                    'mark_id',
                    'type_id', 
                    'type_client',
                    'card_number'
                ],
                'safe'
            ],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg'],
            ['service_type', 'default', 'value' => Service::TYPE_WASH],
            ['status', 'default', 'value' => self::STATUS_NEW],
            ['check', 'default', 'value' => null],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'served_at'    => '????????',
            'partner_id'   => '??????????????',
            'city'   => '??????????',
            'client_id'    => '????????????',
            'card_id'      => '??????????',
            'car_id'       => '??????????',
            'extra_car_id' => '??/??',
            'card_number'  => '??????????',
            'car_number'   => '??????????',
            'extra_car_number' => '??/??',
            'mark_id'      => '??????????',
            'type_id'      => '??????',
            'type_client'      => '?????? ??????????????',
            'income'       => '??????????',
            'expense'      => '??????????',
            'check'        => '??????',
            'period'       => '????????????',
            'day'          => '????????',
            'time_str'     => '????????',
            'image'        => '???????????????? ????????',
            'actsCount'    => '????????????????????????',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\ActQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ActQuery(get_called_class());
    }

    /**
     * @return ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Company::className(), ['id' => 'client_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Company::className(), ['id' => 'partner_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCard()
    {
        return $this->hasOne(Card::className(), ['id' => 'card_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCar()
    {
        return $this->hasOne(Car::className(), ['id' => 'car_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getMark()
    {
        return $this->hasOne(Mark::className(), ['id' => 'mark_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'type_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPenaltyinfo()
    {
        return $this->hasOne(PenaltyInfo::className(), ['act_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getScopes()
    {
        return $this->hasMany(ActScope::className(), ['act_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getActErrors()
    {
        return $this->hasMany(ActError::className(), ['act_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getPartnerScopes()
    {
        return $this->hasMany(ActScope::className(), ['act_id' => 'id', 'company_id' => 'partner_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getClientScopes()
    {
        return $this->hasMany(ActScope::className(), ['act_id' => 'id', 'company_id' => 'client_id']);
    }

    /**
     * @return string[]
     */
    public static function getDayList()
    {
        return array_combine(range(1, 31), range(1, 31));
    }

    /**
     * @param $car Car
     * @param $serviceId int
     */
    public function disinfectCar($car, $serviceId)
    {
        $this->client_id    = $car->company_id;
        $this->car_number   = $car->number;
        $this->car_id       = $car->id;
        $this->service_type = Service::TYPE_DISINFECT;

        $this->serviceList = [
            0 => [
                'service_id' => $serviceId,
                'amount'     => 1,
            ]
        ];
        $this->save();
    }

    /**
     * @return bool|string
     */
    public function getImageLink()
    {
        $path = \Yii::getAlias('@frontend/web/');
        if (file_exists($path . 'files/checks/' . $this->id . '.jpg')) {
            return '/files/checks/' . $this->id . '.jpg';
        }
        if (file_exists($path . 'files/checks/' . $this->id . '.png')) {
            return '/files/checks/' . $this->id . '.png';
        }

        return false;
    }

    public function hasError($error)
    {
        $hasError = false;
        switch ($error) {
            case self::ERROR_EXPENSE:
                $hasError = !$this->expense;
                foreach ($this->partnerScopes as $scope) {
                    $hasError = $hasError || ($scope->price == 0) ? 1 : 0;
                }
                break;
            case self::ERROR_INCOME:
                $hasError = !$this->income;
                foreach ($this->clientScopes as $scope) {
                    $hasError = $hasError || ($scope->price == 0) ? 1 : 0;
                }
                break;
            case self::ERROR_CHECK:
                $hasError = $this->service_type == Service::TYPE_WASH && (!$this->check || !$this->getImageLink());
                break;
            case self::ERROR_CARD:
                $hasError = ($this->service_type != Service::TYPE_DISINFECT && $this->service_type != Service::TYPE_PENALTY && !$this->card_id) ||
                        (!empty($this->car) && !empty($this->card) && $this->card->company_id != $this->car->company_id);
                break;
            case self::ERROR_CAR:
                $hasError = !($this->car_id) ||
                    ($this->service_type != Service::TYPE_DISINFECT && $this->service_type != Service::TYPE_PENALTY && !empty($this->car) && $this->car->company_id != $this->client_id);
                break;
            case self::ERROR_TRUCK:
                $hasError = (isset($this->client) && $this->client->is_split && !$this->extra_car_id);
                break;
            case self::ERROR_LOST:
                $hasError = $this->card && $this->card->is_lost;
                break;
        }

        return $this->status != self::STATUS_FIXED && $hasError;
    }

    /**
     * @return array
     */
    public function errorMessage()
    {
        $errorMessage = [];
        $errorArr = [
            self::ERROR_EXPENSE => '???? ???????????? ????????????',
            self::ERROR_INCOME  => '???? ???????????? ????????????',
            self::ERROR_CHECK   => '?????? ???? ????????????????',
            self::ERROR_CARD    => empty($this->card) ? '???? ???????????????????? ?????????? ?????????? ??????????' : (
                (empty($this->car)) ? false :
                '???? ?????????????????? ?????????? ?????????? ?? ?????????????? ????.<br>
                ?????????? - ' .
                (!empty($this->card) ? $this->card->company->name : '????????????????????') .
                '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
                ' ???? - ' . (!empty($this->car) ? $this->car->company->name : '????????????????????')
            ),
            self::ERROR_CAR     => empty($this->car) ? '???????????????????????? ?????????? ????' : false,
            self::ERROR_TRUCK   => '???????????????? ???????????????????????????? ??????????',
            self::ERROR_LOST    => '??????????????????????',
        ];
        foreach ($errorArr as $key => $err) {
            if ($this->hasError($key)) {
                $errorMessage[] = $err;
            }
        }

        return $errorMessage;
    }

    public function beforeSave($insert)
    {
        //?????????????????? ???? ???????????? ???? ???????????? ?????? ????????????????????
        if(isset($this->time_str)) {
            $dataArrayParam = explode("-", $this->time_str);
            $dataArrayParam = mktime(00, 00, 01, $dataArrayParam['1'], $dataArrayParam['0'], $dataArrayParam['2']);
            $timePeriod = date('n-Y', $dataArrayParam);
            $lockedList = Lock::checkLocked($timePeriod, $this->service_type);
        } else {
            $lockedList = Lock::checkLocked(date('n-Y', $this->served_at), $this->service_type);
        }

        $is_locked = false;

        if(count($lockedList) > 0) {

            $closeAll = false;
            $closeCompany = false;

            for ($c = 0; $c < count($lockedList); $c++) {
                if ($lockedList[$c]["company_id"] == 0) {
                    $closeAll = true;
                }
                if ($lockedList[$c]["company_id"] == $this->partner_id) {
                    $closeCompany = true;
                }
            }

            if (($closeAll == true) && ($closeCompany == false)) {
                $is_locked = true;
            } elseif (($closeAll == false) && ($closeCompany == true)) {
                $is_locked = true;
            }

        }

        if ($insert && $is_locked) {
            if(isset($this->time_str)) {
                $this->addError('period', date('n', $dataArrayParam));
            } else {
                $this->addError('period', date('n', $this->served_at));
            }
            return false;
        }
        $kpd = $this->service_type == Service::TYPE_TIRES ? 1.2 : 1;

        $card = Card::findOne(['number' => $this->card_number]);
        if ($card) {
            $this->card_id = $card->id;
            $this->card_number = $card->number;
        }

        //???????????????????? ?????????????? ???? ??????????
        if ($this->card_id) {
            $this->client_id = $this->card->company_id;
        }

        if (!empty($this->time_str)) {
            $this->served_at = \DateTime::createFromFormat('d-m-Y H:i:s', $this->time_str . ' 12:00:00')->getTimestamp();
        }

        //?????????? ?? ?????????????? ??????????????
        $this->car_number = mb_strtoupper(str_replace(' ', '', $this->car_number), 'UTF-8');
        $this->car_number = strtr($this->car_number, Translit::$rules);
        $this->extra_car_number = mb_strtoupper(str_replace(' ', '', $this->extra_car_number), 'UTF-8');

        //?????????????????????? ?????? ?? ?????????? ???? ????????????, ???????? ?????????? ???? ????????????
        $car = Car::findOne(['number' => $this->car_number]);
        if ($car) {
            $this->car_id = $car->id;
            $this->car_number = $car->number;
            $this->mark_id = $car->mark_id;

            if(Yii::$app->user->isGuest == 0) {
                if (((Yii::$app->user->identity->role == User::ROLE_PARTNER) && ($this->service_type != Service::TYPE_DISINFECT)) || !$this->type_id) {
                    $this->type_id = $car->type_id;
                    $this->type_client = $car->type_id;
                }
            }

            if (empty($this->client_id)) {
                $this->client_id = $car->company_id;
            }
        }

        if (!$this->client_id) {
            $this->addError('client', '???????????? ???? ????????????.');
            return false;
        }

        // ???????????? ???????????????? ?????? ???????? ???? ?????????????? ???? ?????????????????? ???????? ???? ???? ??????????
        if(isset($this->type_client)) {
            if($this->type_client == 0) {
                $this->type_client = $this->type_id;
            }
        } else {
            $this->type_client = $this->type_id;
        }

        if ($insert) {

            // ?????????????????? ???? ?????????????? ??????????????????
            if(Yii::$app->user->identity->role != User::ROLE_ADMIN) {
                $replaceArray = ServiceReplace::find()->where(['client_id' => $this->client_id, 'partner_id' => $this->partner_id, 'type' => $this->service_type])->andWhere(['OR', ['type_client' => 0], ['type_client' => $this->type_client]])->andWhere(['OR', ['mark_partner' => 0], ['mark_partner' => $this->mark_id]])->select('id, type_client, type_partner')->asArray()->all();
                $newCarType = 0;

                $numServReplace = 0;
                $numServiceTrue = 0;

                if (count($replaceArray) > 0) {

                    for ($i = 0; $i < count($replaceArray); $i++) {

                        $replace_id = $replaceArray[$i]['id'];

                        $replaceCont = ServiceReplaceItem::find()->where(['replace_id' => $replace_id])->asArray()->select('service_id, company_id, type, car_type')->all();

                        $numServReplace = 0;
                        $numServiceTrue = 0;

                        if (count($replaceCont) > 0) {

                            for ($j = 0; $j < count($replaceCont); $j++) {

                                // ?????????? ???????????? ??????????????????
                                if ($j == (count($replaceCont) - 1)) {

                                    // ???????????????????? ?????? ???? ???? ??????????????????
                                    if (($replaceCont[$j]['company_id'] == $this->client_id) && ($replaceCont[$j]['car_type'] > 0)) {
                                        $newCarType = $replaceCont[$j]['car_type'];
                                        $this->type_client = $newCarType;
                                    } else if (($replaceCont[$j]['company_id'] == $this->partner_id) && ($replaceCont[$j]['car_type'] > 0)) {
                                        $newCarType = $replaceCont[$j]['car_type'];
                                        $this->type_id = $newCarType;
                                    }

                                }

                            }

                        } else {

                            if ($replaceArray[$i]['type_client'] > 0) {
                                $this->type_client = $replaceArray[$i]['type_client'];
                            }
                            if ($replaceArray[$i]['type_partner'] > 0) {
                                $this->type_id = $replaceArray[$i]['type_partner'];
                            }

                        }

                    }
                }
            }
            // END ?????????????????? ???? ?????????????? ??????????????????

            //?????????????????????? ???????????? ?????????????? ?? ???????????????????? ????????????

            if(isset($this->serviceList[0]['service_id'])) {
                if (!empty($this->serviceList[0]) && count(explode('+', ArrayHelper::getValue($this->serviceList[0], 'service_id', null))) > 1) {
                    $serviceList = [];
                    foreach (explode('+', ArrayHelper::getValue($this->serviceList[0], 'service_id', null)) as $serviceId) {
                        $serviceList[] = [
                            'service_id' => $serviceId,
                            'price' => $this->serviceList[0]['price'],
                            'amount' => $this->serviceList[0]['amount'],
                        ];
                    }
                    $this->serviceList = $serviceList;
                }
            }
            /**
             * ?????????????????? ?????? ?????????????????? ???????????? ?? ?????????????? ??????????, ???????????? ?? ??????????????\
             */
            if (!empty($this->serviceList)) {

                $totalExpense = 0;
                $totalIncome = 0;
                foreach ($this->serviceList as $serviceData) {

                    ArrayHelper::getValue($serviceData, 'service_id', null);

                    $clientService = CompanyService::findOne([
                        'service_id' => ArrayHelper::getValue($serviceData, 'service_id', null),
                        'company_id' => $this->client_id,
                        'type_id'    => $this->type_client,
                    ]);
                    if (!empty($clientService) && $clientService->service->is_fixed) {
                        $totalIncome += $clientService->price * $serviceData['amount'];
                    } else {
                        //???? 20% ?????????????????????? ???????? ?????? ??????????????
                        $totalIncome += $kpd * ArrayHelper::getValue($serviceData, 'price', 0) * $serviceData['amount'];
                    }

                    $partnerService = CompanyService::findOne([
                        'service_id' => ArrayHelper::getValue($serviceData, 'service_id', null),
                        'company_id' => $this->partner_id,
                        'type_id'    => $this->type_id,
                    ]);
                    if (!empty($partnerService) && $partnerService->service->is_fixed) {
                        $totalExpense += $partnerService->price * $serviceData['amount'];
                    } else {
                        $totalExpense += ArrayHelper::getValue($serviceData, 'price', 0) * $serviceData['amount'];
                    }
                }

                $this->income = $totalIncome;
                $this->expense = $totalExpense;
                $this->profit = $this->income - $this->expense;
            } else {
                return false;
            }
        } else {

            // ?????????????????? ???? ?????????????? ??????????????????
            $arrReplaceNeed = [];

            if (Yii::$app->user->identity->role != User::ROLE_ADMIN) {

                $replaceArray = ServiceReplace::find()->where(['client_id' => $this->client_id, 'partner_id' => $this->partner_id, 'type' => $this->service_type])->andWhere(['OR', ['type_client' => 0], ['type_client' => $this->type_client]])->andWhere(['OR', ['mark_partner' => 0], ['mark_partner' => $this->mark_id]])->select('id, type_client, type_partner')->asArray()->all();
                $newCarType = 0;

                $numServReplace = 0;
                $numServiceTrue = 0;
                $numServiceHaveClient = 0;
                $arrnumServiceReplace = [];

                if (count($replaceArray) > 0) {

                    for ($i = 0; $i < count($replaceArray); $i++) {

                        $replace_id = $replaceArray[$i]['id'];

                        $replaceCont = ServiceReplaceItem::find()->where(['replace_id' => $replace_id])->asArray()->select('service_id, company_id, type, car_type')->all();

                        $numServReplace = 0;
                        $numServiceTrue = 0;
                        $numServiceHaveClient = 0;

                        if (count($replaceCont) > 0) {

                            for ($j = 0; $j < count($replaceCont); $j++) {

                                if ($replaceCont[$j]['company_id'] == $this->partner_id) {

                                    $numServReplace++;
                                    $toNextService = false;

                                    foreach ($this->partnerServiceList as $serviceData) {
                                        if ($toNextService == false) {
                                            if ($replaceCont[$j]['service_id'] == $serviceData['service_id']) {
                                                $numServiceTrue++;
                                                $toNextService = true;

                                                $index = $replaceCont[$j]['service_id'];

                                                if (isset($arrnumServiceReplace[$index])) {

                                                    if ($arrnumServiceReplace[$index] >= 1) {
                                                        $arrnumServiceReplace[$index]++;
                                                    } else {
                                                        $arrnumServiceReplace[$index] = 1;
                                                    }

                                                } else {
                                                    $arrnumServiceReplace[$index] = 1;
                                                }

                                            }
                                        }
                                    }

                                    $toNextService = false;
                                    foreach ($this->clientServiceList as $serviceData) {
                                        if ($toNextService == false) {
                                            if ($replaceCont[$j]['service_id'] == $serviceData['service_id']) {
                                                $numServiceHaveClient++;
                                                $toNextService = true;
                                            }
                                        }
                                    }

                                }

                                // ?????????? ???????????? ??????????????????
                                if (($j == (count($replaceCont) - 1)) && ($numServReplace == $numServiceTrue) && ($numServReplace > 0)) {
                                    $arrReplaceNeed = array_merge($arrReplaceNeed, $replaceCont);

                                    // ???????????????????? ?????? ???? ???? ??????????????????
                                    if (($replaceCont[$j]['company_id'] == $this->client_id) && ($replaceCont[$j]['car_type'] > 0)) {
                                        $newCarType = $replaceCont[$j]['car_type'];
                                        $this->type_client = $newCarType;
                                    } else if (($replaceCont[$j]['company_id'] == $this->partner_id) && ($replaceCont[$j]['car_type'] > 0)) {
                                        $newCarType = $replaceCont[$j]['car_type'];
                                        $this->type_id = $newCarType;
                                    }

                                }

                            }

                        } else {

                            if ($replaceArray[$i]['type_client'] > 0) {
                                $this->type_client = $replaceArray[$i]['type_client'];
                            }
                            if ($replaceArray[$i]['type_partner'] > 0) {
                                $this->type_id = $replaceArray[$i]['type_partner'];
                            }

                        }

                    }
                }

            }
            // END ?????????????????? ???? ?????????????? ??????????????????

            $totalExpense = 0;
            $totalIncome = 0;

            if (empty($this->partnerServiceList) && ($this->status == self::STATUS_NEW || $this->byAdmin)) {
                $this->partnerServiceList = $this->getPartnerScopes()->asArray()->all();
            }

            if (!empty($this->partnerServiceList)) {
                ActScope::deleteAll(['act_id' => $this->id, 'company_id' => $this->partner_id]);

                foreach ($this->partnerServiceList as $serviceData) {
                    if (empty($serviceData['service_id']) && empty($serviceData['description'])) {
                        continue;
                    }
                    $scope = new ActScope();
                    $scope->company_id = $this->partner_id;
                    $scope->act_id = $this->id;
                    if (!empty($serviceData['service_id'])) {
                        $scope->service_id = $serviceData['service_id'];
                        $companyService = CompanyService::findOne([
                            'service_id' => $serviceData['service_id'],
                            'company_id' => $this->partner_id,
                            'type_id' => $this->type_id,
                        ]);
                        if (!empty($companyService) && $companyService->service->is_fixed) {

                            if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) && (isset($serviceData['price'])) && ($serviceData['price'] >= 0)) {
                                //$scope->price = ArrayHelper::getValue($serviceData, 'price', 0);
                                $scope->price = $companyService->price;
                            } else {
                                $scope->price = $companyService->price;
                            }

                            $scope->description = $companyService->service->description;
                        } else {
                            $scope->price = ArrayHelper::getValue($serviceData, 'price', 0);
                            $scope->description = Service::findOne(['id' => $serviceData['service_id']])->description;
                        }
                    } else {
                        $scope->price = ArrayHelper::getValue($serviceData, 'price', 0);
                        $scope->description = $serviceData['description'];
                    }
                    $scope->amount = $serviceData['amount'];

                    if (isset($serviceData['parts'])) {
                        $scope->parts = $serviceData['parts'];
                    } else {
                        $scope->parts = 0;
                    }

                    $scope->save();
                    $totalExpense += $scope->price * $scope->amount;
                }

                $this->expense = $totalExpense;
            }

            if (empty($this->clientServiceList) && ($this->status == self::STATUS_NEW || $this->byAdmin)) {
                $this->clientServiceList = $this->getClientScopes()->asArray()->all();
            }
            if (!empty($this->clientServiceList)) {
                ActScope::deleteAll(['act_id' => $this->id, 'company_id' => $this->client_id]);

                // ?????????????????? ?????????????????? ????????????

                $arrClientsType = [];

                if (count($arrReplaceNeed) > 0) {
                    // ?????????????? ???????????? ?????????????????? ?? ??????????????
                    for ($j = 0; $j < count($arrReplaceNeed); $j++) {
                        if ($arrReplaceNeed[$j]['company_id'] != $this->client_id) {

                            foreach ($this->clientServiceList as $key => $serviceData) {
                                if (!empty($serviceData['service_id'])) {
                                    if ($arrReplaceNeed[$j]['service_id'] == $serviceData['service_id']) {

                                        $index = $arrReplaceNeed[$j]['service_id'];

                                        if (isset($arrnumServiceReplace[$index])) {

                                            if ($arrnumServiceReplace[$index] > 0) {
                                                $arrnumServiceReplace[$index]--;
                                                unset($this->clientServiceList[$key]);
                                            }

                                        }

                                    }
                                }
                            }

                        }
                    }
                    // END ?????????????? ???????????? ?????????????????? ?? ??????????????

                }
                // END ?????????????????? ?????????????????? ????????????

                $kpd = $this->service_type == Service::TYPE_TIRES ? 1.2 : 1;

                foreach ($this->clientServiceList as $serviceData) {
                    if (empty($serviceData['service_id']) && empty($serviceData['description'])) {
                        continue;
                    }
                    $scope = new ActScope();
                    $scope->company_id = $this->client_id;
                    $scope->act_id = $this->id;

                    if (!empty($serviceData['service_id'])) {
                        $scope->service_id = $serviceData['service_id'];
                        $companyService = CompanyService::findOne([
                            'service_id' => $serviceData['service_id'],
                            'company_id' => $this->client_id,
                            'type_id' => $newCarType > 0 ? $newCarType : ($this->type_client > 0 ? $this->type_client : $this->type_id),
                        ]);

                        if (!empty($companyService) && $companyService->service->is_fixed) {

                            if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) && (isset($serviceData['price'])) && ($serviceData['price'] > 0)) {
                                $scope->price = $serviceData['price'];
                            } else {
                                $scope->price = $companyService->price;
                            }

                            $scope->description = $companyService->service->description;
                        } else {
                            $scope->price = $kpd == 1 ? 0 : ArrayHelper::getValue($serviceData, 'price', 0); // ???????? ???????? ?????? ???? ???????????? 0
                            $scope->description = Service::findOne(['id' => $serviceData['service_id']])->description;
                        }
                    } else {
                        $scope->price = ArrayHelper::getValue($serviceData, 'price', 0);
                        $scope->description = $serviceData['description'];
                    }
                    $scope->amount = $serviceData['amount'];

                    if (isset($serviceData['parts'])) {
                        $scope->parts = $serviceData['parts'];
                    } else {
                        $scope->parts = 0;
                    }

                    $scope->save();
                    $totalIncome += $scope->price * $scope->amount;
                }

                // ?????????????????? ???????????????????? ????????????
                for ($j = 0; $j < count($arrReplaceNeed); $j++) {
                    if ($arrReplaceNeed[$j]['company_id'] == $this->client_id) {

                        $haveServiceRepair = false;

                        foreach ($this->clientServiceList as $serviceData) {
                            if ($haveServiceRepair == false) {
                                if ($arrReplaceNeed[$j]['service_id'] == $serviceData['service_id']) {
                                    $haveServiceRepair = true;
                                }
                            }
                        }

                        if ($haveServiceRepair == false) {

                            $scope = new ActScope();
                            $scope->company_id = $this->client_id;
                            $scope->act_id = $this->id;
                            $scope->service_id = $arrReplaceNeed[$j]['service_id'];
                            $clientService = CompanyService::findOne([
                                'service_id' => $arrReplaceNeed[$j]['service_id'],
                                'company_id' => $this->client_id,
                                'type_id' => $newCarType > 0 ? $newCarType : ($this->type_client > 0 ? $this->type_client : $this->type_id),
                            ]);

                            if (!empty($clientService) && $clientService->service->is_fixed) {
                                $scope->price = $clientService->price;
                                $scope->description = $clientService->service->description;
                            } else {
                                $scope->price = 0;
                                $scope->description =
                                    Service::findOne(['id' => $arrReplaceNeed[$j]['service_id']])->description;
                            }
                            $scope->amount = 1;

                            if (isset($serviceData['parts'])) {
                                $scope->parts = $serviceData['parts'];
                            } else {
                                $scope->parts = 0;
                            }

                            $scope->save();
                            $totalIncome += $scope->price * $scope->amount;

                        }

                    }
                }
                // END ?????????????????? ???????????????????? ????????????

                $this->income = $totalIncome;
            }

            $this->profit = $this->income - $this->expense;
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $kpd = $this->service_type == Service::TYPE_TIRES ? 1.2 : 1;
        //?????????????????? ???????????????? ????????
        $this->uploadImage();
        /**
         * ?????????????????? ?????? ?????????????????? ???????????? ?? ?????????????????? ?????? ???????????????? ?? ?????????????? ???? ???????????? ??????
         */

        // ?????? ?????????????????? ?? ???????????????? ???? ??????????????????????
        $arrReplaceNeed = [];
        $numePartnerService = 0;
        $numeClientService = 0;
        $numReplacePartner = 0;
        $numReplaceClient = 0;

        if ($insert) {

            if (!empty($this->serviceList)) {

                // ?????????????????? ???? ?????????????? ??????????????????
                if (Yii::$app->user->identity->role != User::ROLE_ADMIN) {

                    $replaceArray = ServiceReplace::find()->where(['client_id' => $this->client_id, 'partner_id' => $this->partner_id, 'type' => $this->service_type])->andWhere(['OR', ['type_client' => 0], ['type_client' => $this->type_client]])->andWhere(['OR', ['mark_partner' => 0], ['mark_partner' => $this->mark_id]])->select('id, type_client, type_partner')->asArray()->all();
                    $newCarType = 0;

                    $numServReplace = 0;
                    $numServiceTrue = 0;
                    $arrnumServiceReplace = [];

                    if (count($replaceArray) > 0) {

                        for ($i = 0; $i < count($replaceArray); $i++) {

                            $replace_id = $replaceArray[$i]['id'];

                            $replaceCont = ServiceReplaceItem::find()->where(['replace_id' => $replace_id])->asArray()->select('service_id, company_id, type, car_type')->all();

                            $numServReplace = 0;
                            $numServiceTrue = 0;

                            if (count($replaceCont) > 0) {

                                for ($j = 0; $j < count($replaceCont); $j++) {

                                    if ($replaceCont[$j]['company_id'] == $this->partner_id) {

                                        $numServReplace++;
                                        $toNextService = false;

                                        foreach ($this->serviceList as $serviceData) {
                                            if ($toNextService == false) {
                                                if (!empty($serviceData['service_id'])) {
                                                    if ($replaceCont[$j]['service_id'] == $serviceData['service_id']) {
                                                        $numServiceTrue++;
                                                        $toNextService = true;

                                                        $index = $replaceCont[$j]['service_id'];

                                                        if (isset($arrnumServiceReplace[$index])) {

                                                            if ($arrnumServiceReplace[$index] >= 1) {
                                                                $arrnumServiceReplace[$index]++;
                                                            } else {
                                                                $arrnumServiceReplace[$index] = 1;
                                                            }

                                                        } else {
                                                            $arrnumServiceReplace[$index] = 1;
                                                        }

                                                    }
                                                }
                                            }
                                        }

                                    }

                                    // ?????????? ???????????? ??????????????????
                                    if (($j == (count($replaceCont) - 1)) && ($numServReplace == $numServiceTrue) && ($numServReplace > 0)) {
                                        $arrReplaceNeed = array_merge($arrReplaceNeed, $replaceCont);
                                        $numReplacePartner = $numServiceTrue;
                                        $numReplaceClient = count($replaceCont) - $numReplacePartner;

                                        // ???????????????????? ?????? ???? ???? ??????????????????
                                        if (($replaceCont[$j]['company_id'] == $this->client_id) && ($replaceCont[$j]['car_type'] > 0)) {
                                            $newCarType = $replaceCont[$j]['car_type'];
                                            $this->type_client = $newCarType;
                                        } else if (($replaceCont[$j]['company_id'] == $this->partner_id) && ($replaceCont[$j]['car_type'] > 0)) {
                                            $newCarType = $replaceCont[$j]['car_type'];
                                            $this->type_id = $newCarType;
                                        }

                                    }

                                }

                            } else {

                                if ($replaceArray[$i]['type_client'] > 0) {
                                    $this->type_client = $replaceArray[$i]['type_client'];
                                }
                                if ($replaceArray[$i]['type_partner'] > 0) {
                                    $this->type_id = $replaceArray[$i]['type_partner'];
                                }

                            }

                        }
                    }

                }
                // END ?????????????????? ???? ?????????????? ??????????????????

                // ???????? ?? ?????????????????? ?????????? ?????? ???? ?????? ??????????????, ???? ???????????? ???????????????? ?????? ?? ????
                if ($newCarType > 0) {
                    Yii::$app->db->createCommand()->update('{{%act}}', ['type_client' => $newCarType], ['id' => $this->id])->execute();
                }
                // ???????? ?? ?????????????????? ?????????? ?????? ???? ?????? ??????????????, ???? ???????????? ???????????????? ?????? ?? ????

                $numRepairServiceClient = 0;

                $totalExpense = 0;
                $totalIncome = 0;

                foreach ($this->serviceList as $serviceData) {

                    $removeServiceClient = false;

                    // ?????????????????? ?????????????????? ????????????
                    if (count($arrReplaceNeed) > 0) {
                        for ($j = 0; $j < count($arrReplaceNeed); $j++) {
                            if ($arrReplaceNeed[$j]['company_id'] != $this->client_id) {

                                if ($arrReplaceNeed[$j]['service_id'] == $serviceData['service_id']) {
                                    // ?????????????? ???????????? ?????????????????? ?? ??????????????
                                    $removeServiceClient = true;

                                    $index = $arrReplaceNeed[$j]['service_id'];

                                    if (isset($arrnumServiceReplace[$index])) {

                                        if ($arrnumServiceReplace[$index] > 0) {
                                            $arrnumServiceReplace[$index]--;
                                        } else {
                                            $removeServiceClient = false;
                                        }

                                    }

                                }

                            } else {
                                $numRepairServiceClient++;
                            }
                        }
                    }
                    // END ?????????????????? ?????????????????? ????????????

                    // ?????????????? ???????????? ?????????????????? ?? ??????????????
                    if ($removeServiceClient == false) {
                        $clientScope = new ActScope();
                        $clientScope->company_id = $this->client_id;
                        $clientScope->act_id = $this->id;
                        if (!empty($serviceData['service_id'])) {
                            $clientScope->service_id = $serviceData['service_id'];
                            $clientService = CompanyService::findOne([
                                'service_id' => $serviceData['service_id'],
                                'company_id' => $this->client_id,
                                'type_id' => $newCarType > 0 ? $newCarType : ($this->type_client > 0 ? $this->type_client : $this->type_id),
                            ]);

                            if (!empty($clientService) && $clientService->service->is_fixed) {
                                $clientScope->price = $clientService->price;
                                $clientScope->description = $clientService->service->description;
                            } else {
                                $clientScope->price = $kpd == 1 ? 0 : ($kpd * ArrayHelper::getValue($serviceData, 'price', 0));  // ???????? ???????? ?????? ???? ???????????? 0
                                $clientScope->description =
                                    Service::findOne(['id' => $serviceData['service_id']])->description;
                            }
                        } else {
                            //???? 20% ?????????????????????? ???????? ?????? ??????????????
                            $clientScope->price = $kpd * ArrayHelper::getValue($serviceData, 'price', 0);
                            $clientScope->description = ArrayHelper::getValue($serviceData, 'description', '?????? ????????????????');
                        }
                        $clientScope->amount = $serviceData['amount'];

                        if (isset($serviceData['parts'])) {
                            $clientScope->parts = $serviceData['parts'];
                        } else {
                            $clientScope->parts = 0;
                        }

                        $totalIncome += $clientScope->price * $serviceData['amount'];

                        $clientScope->save();
                        $numeClientService++;
                    }

                    $partnerScope = new ActScope();
                    $partnerScope->company_id = $this->partner_id;
                    $partnerScope->act_id = $this->id;
                    if (!empty($serviceData['service_id'])) {
                        $partnerScope->service_id = $serviceData['service_id'];
                        $partnerService = CompanyService::findOne([
                            'service_id' => $serviceData['service_id'],
                            'company_id' => $this->partner_id,
                            'type_id' => $this->type_id,
                        ]);
                        if (!empty($partnerService) && $partnerService->service->is_fixed) {
                            $partnerScope->price = $partnerService->price;
                            $partnerScope->description = $partnerService->service->description;
                        } else {
                            $partnerScope->price = ArrayHelper::getValue($serviceData, 'price', 0);
                            $partnerScope->description =
                                Service::findOne(['id' => $serviceData['service_id']])->description;
                        }
                    } else {
                        $partnerScope->price = ArrayHelper::getValue($serviceData, 'price', 0);
                        $partnerScope->description = $serviceData['description'];
                    }
                    $partnerScope->amount = $serviceData['amount'];

                    if (isset($serviceData['parts'])) {
                        $partnerScope->parts = $serviceData['parts'];
                    } else {
                        $partnerScope->parts = 0;
                    }

                    $totalExpense += $partnerScope->price * $serviceData['amount'];

                    $partnerScope->save();
                    $numePartnerService++;
                }

                // ?????????????????? ???????????? ??????????????????
                if ((count($arrReplaceNeed) > 0) && ($numRepairServiceClient > 0)) {
                    for ($j = 0; $j < count($arrReplaceNeed); $j++) {
                        if ($arrReplaceNeed[$j]['company_id'] == $this->client_id) {

                            $haveServiceRepair = false;

                            foreach ($this->serviceList as $serviceData) {
                                if ($haveServiceRepair == false) {
                                    if ($arrReplaceNeed[$j]['service_id'] == $serviceData['service_id']) {
                                        $haveServiceRepair = true;
                                    }
                                }
                            }

                            if ($haveServiceRepair == false) {

                                $clientScope = new ActScope();
                                $clientScope->company_id = $this->client_id;
                                $clientScope->act_id = $this->id;
                                $clientScope->service_id = $arrReplaceNeed[$j]['service_id'];
                                $clientService = CompanyService::findOne([
                                    'service_id' => $arrReplaceNeed[$j]['service_id'],
                                    'company_id' => $this->client_id,
                                    'type_id' => $newCarType > 0 ? $newCarType : ($this->type_client > 0 ? $this->type_client : $this->type_id),
                                ]);

                                if (!empty($clientService) && $clientService->service->is_fixed) {
                                    $clientScope->price = $clientService->price;
                                    $clientScope->description = $clientService->service->description;
                                } else {
                                    $clientScope->price = 0;
                                    $clientScope->description =
                                        Service::findOne(['id' => $arrReplaceNeed[$j]['service_id']])->description;
                                }
                                $clientScope->amount = 1;
                                $clientScope->parts = 0;

                                $totalIncome += $clientScope->price;

                                $clientScope->save();
                                $numeClientService++;

                            }

                        }

                    }
                }

                // ???????????? ?????????????????? ??????????, ???????????? ?? ?????????????? ?????????? ???????????????????? ??????????????????
                if (($this->expense != $totalExpense) || ($this->income != $totalIncome)) {

                    $profit = $totalIncome - $totalExpense;
                    Yii::$app->db->createCommand()->update('{{%act}}', ['expense' => $totalExpense, 'income' => $totalIncome, 'profit' => $profit], ['id' => $this->id])->execute();

                }
                // END ?????????????????? ??????????, ???????????? ?? ?????????????? ?????????? ???????????????????? ??????????????????

            }
            // END ?????????????????? ???????????? ??????????????????

        } else {
            // ?????? ?????????????????????? ?????????? ?????? ????????????????????????????

            // ?????????????????? ???? ?????????????? ??????????????????
            if(Yii::$app->user->identity->role != User::ROLE_ADMIN) {

                $replaceArray = ServiceReplace::find()->where(['client_id' => $this->client_id, 'partner_id' => $this->partner_id, 'type' => $this->service_type])->andWhere(['OR', ['type_client' => 0], ['type_client' => $this->type_client]])->andWhere(['OR', ['mark_partner' => 0], ['mark_partner' => $this->mark_id]])->select('id, type_client, type_partner')->asArray()->all();
                $newCarType = 0;

                $numServReplace = 0;
                $numServiceTrue = 0;

                if (count($replaceArray) > 0) {

                    for ($i = 0; $i < count($replaceArray); $i++) {

                        $replace_id = $replaceArray[$i]['id'];

                        $replaceCont = ServiceReplaceItem::find()->where(['replace_id' => $replace_id])->asArray()->select('service_id, company_id, type, car_type')->all();

                        $numServReplace = 0;
                        $numServiceTrue = 0;

                        if (count($replaceCont) > 0) {

                            for ($j = 0; $j < count($replaceCont); $j++) {

                                if ($replaceCont[$j]['company_id'] == $this->partner_id) {

                                    $numServReplace++;
                                    $toNextService = false;

                                    foreach ($this->partnerServiceList as $serviceData) {
                                        if ($toNextService == false) {
                                            if ($replaceCont[$j]['service_id'] == $serviceData['service_id']) {
                                                $numServiceTrue++;
                                                $toNextService = true;
                                            }
                                        }
                                    }

                                }

                                // ?????????? ???????????? ??????????????????
                                if (($j == (count($replaceCont) - 1)) && ($numServReplace == $numServiceTrue) && ($numServReplace > 0)) {
                                    $arrReplaceNeed = array_merge($arrReplaceNeed, $replaceCont);
                                    $numReplacePartner = $numServiceTrue;
                                    $numReplaceClient = count($replaceCont) - $numReplacePartner;

                                    // ???????????????????? ?????? ???? ???? ??????????????????
                                    if (($replaceCont[$j]['company_id'] == $this->client_id) && ($replaceCont[$j]['car_type'] > 0)) {
                                        $newCarType = $replaceCont[$j]['car_type'];
                                        $this->type_client = $newCarType;
                                    }

                                }

                            }

                        } else {

                            if ($replaceArray[$i]['type_client'] > 0) {
                                $this->type_client = $replaceArray[$i]['type_client'];
                            }
                            if ($replaceArray[$i]['type_partner'] > 0) {
                                $this->type_id = $replaceArray[$i]['type_partner'];
                            }

                        }

                    }
                }

            }
            // END ?????????????????? ???? ?????????????? ??????????????????

            $numePartnerService = count($this->partnerServiceList);
            $numeClientService = count($this->clientServiceList);

        }

        //?????????????????????????? ???????????????? ??????
        MonthlyAct::getRealObject($this->service_type)->saveFromAct($this);

        // ?????????????????? ???????? ???????????? ???? ???????????????????? ?????????? ?? ????????????
        ActError::deleteAll(['act_id' => $this->id]);
        $dateLastMonth = date('Y-m-01 00:00:00', strtotime("-1 month"));

        if($this->served_at >= strtotime($dateLastMonth)) {

            //?????????????????? ???? ????????????
            $listErrors = $this->getListError();

            if ($this->card_id) {
                Card::markFoundedById($this->card_id);
            }
            foreach ($listErrors as $errorType) {
                $modelActError = new ActError();
                $modelActError->act_id = $this->id;
                $modelActError->error_type = $errorType;
                $modelActError->save();
            }

            // ???????????????? ???? ?????????????????? ??????
            if ($this->profit < 0) {
                $modelActError = new ActError();
                $modelActError->act_id = $this->id;
                $modelActError->error_type = 19;
                $modelActError->save();
            }

            // ?????????????????????? ????????
            if ((($numeClientService != $numePartnerService) && ((count($arrReplaceNeed) == 0))) || ((count($arrReplaceNeed) > 0) && ((($numReplacePartner > $numReplaceClient) && ($numeClientService < $numePartnerService) && ($numePartnerService != ($numeClientService + ($numReplacePartner - $numReplaceClient)))) || (($numReplaceClient > $numReplacePartner) && ($numePartnerService < $numeClientService) && ($numeClientService != ($numePartnerService + ($numReplaceClient - $numReplacePartner)))) || (($numReplaceClient != $numReplacePartner) && ($numePartnerService == $numeClientService))))) {
                $modelActError = new ActError();
                $modelActError->act_id = $this->id;
                $modelActError->error_type = 20;
                $modelActError->save();
            }

            // ???????????????????? ????????
            $dateSecondSearch = date('Y-m-d', strtotime("-2 day"));
            $dateFirtsSearch = date('Y-m-d', strtotime("-1 day"));
            $dateNowSearch = date('Y-m-d');
            $arrSearchActs = Act::find()->where(['AND', ['car_number' => $this->car_number], ['service_type' => $this->service_type]])->andWhere(['OR', ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%Y-%m-%d")' => $dateSecondSearch], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%Y-%m-%d")' => $dateFirtsSearch], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%Y-%m-%d")' => $dateNowSearch]])->count();

            if($arrSearchActs > 1) {
                $modelActError = new ActError();
                $modelActError->act_id = $this->id;
                $modelActError->error_type = 21;
                $modelActError->save();
            }

        }
        // ?????????????????? ???????? ???????????? ???? ???????????????????? ?????????? ?? ????????????

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * ?????????? ??????????????????
     *
     * @return bool
     */
    public function beforeDelete()
    {

        // ???????????????? ??????????????
        if($this->service_type == Service::TYPE_PENALTY) {
            PenaltyInfo::deleteAll(['act_id' => $this->id]);
        }

        return true;
    }

    public function getListError()
    {
        $res = [];
        foreach (self::$listErrors as $errorType => $errorName) {
            if ($this->hasError($errorName)) {
                $res[] = $errorType;
            }
        }

        return $res;
    }

    /**
     * @throws \yii\base\ErrorException
     */
    private function uploadImage()
    {
        if ($this->image) {
            $image = \Yii::$app->image->load($this->image->tempName);
            /**
             * @var $image \yii\image\drivers\Image
             */
            $imagePath = \Yii::getAlias('@webroot/files/checks/' . $this->id . '.' . $this->image->extension);

            if (!file_exists(\Yii::getAlias('@webroot/files/'))) {
                mkdir(\Yii::getAlias('@webroot/files/'), 0775);
            }

            if (!file_exists(\Yii::getAlias('@webroot/files/checks/'))) {
                mkdir(\Yii::getAlias('@webroot/files/checks/'), 0775);
            }

            $fileHaveName = '';

            foreach (glob("files/checks/" . $this->id . ".*") as $filename) {
                $fileHaveName = $filename;
            }

            if($fileHaveName != '') {
                if (file_exists(\Yii::getAlias('@webroot/' . $fileHaveName))) {
                    chmod(\Yii::getAlias('@webroot/' . $fileHaveName), 0775);
                    unlink(\Yii::getAlias('@webroot/' . $fileHaveName));
                }
            }

            $return = $image->resize(self::ACT_WIDTH, self::ACT_HEIGHT)->save($imagePath);
            chmod($imagePath, 0775);

            return $return;

//            $imagePath = \Yii::getAlias('@webroot/files/checks/' . $this->id . '.' . $this->image->extension);
//            $this->image->saveAs($imagePath);
        }

        return false;
    }
}