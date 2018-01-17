<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.08.2016
 * Time: 0:27
 */

namespace common\models;

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
            'ru' => 'Новый',
            'en' => 'new',
        ],
        self::STATUS_CLOSED => [
            'ru' => 'Закрыт',
            'en' => 'closed',
        ],
        self::STATUS_FIXED  => [
            'ru' => 'Исправлен',
            'en' => 'fixed',
        ]
    ];
    /**
     * @var array
     */
    public static $periodList = ['все время', 'месяц', 'квартал', 'полгода', 'год'];
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
            'served_at'    => 'Дата',
            'partner_id'   => 'Партнер',
            'city'   => 'Город',
            'client_id'    => 'Клиент',
            'card_id'      => 'Карта',
            'car_id'       => 'Номер',
            'extra_car_id' => 'п/п',
            'card_number'  => 'Карта',
            'car_number'   => 'Номер',
            'extra_car_number' => 'п/п',
            'mark_id'      => 'Марка',
            'type_id'      => 'Тип',
            'income'       => 'Сумма',
            'expense'      => 'Сумма',
            'check'        => 'Чек',
            'period'       => 'Период',
            'day'          => 'День',
            'time_str'     => 'Дата',
            'image'        => 'Загрузка чека',
            'actsCount'    => 'Обслуживания',
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
            self::ERROR_EXPENSE => 'Не указан расход',
            self::ERROR_INCOME  => 'Не указан приход',
            self::ERROR_CHECK   => 'Чек не загружен',
            self::ERROR_CARD    => empty($this->card) ? 'Не существует такой номер карты' : (
                (empty($this->car)) ? false :
                'Не совпадает номер карты с номером ТС.<br>
                Карта - ' .
                (!empty($this->card) ? $this->card->company->name : 'Неизвестна') .
                '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
                ' ТС - ' . (!empty($this->car) ? $this->car->company->name : 'Неизвестна')
            ),
            self::ERROR_CAR     => empty($this->car) ? 'Некорректный номер ТС' : false,
            self::ERROR_TRUCK   => 'Неверный дополнительный номер',
            self::ERROR_LOST    => 'Потеряшечка',
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
        //проверяем не закрыт ли период для добавления
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

        //определяем клиента по карте
        if ($this->card_id) {
            $this->client_id = $this->card->company_id;
        }

        if (!empty($this->time_str)) {
            $this->served_at = \DateTime::createFromFormat('d-m-Y H:i:s', $this->time_str . ' 12:00:00')->getTimestamp();
        }

        //номер в верхний регистр
        $this->car_number = mb_strtoupper(str_replace(' ', '', $this->car_number), 'UTF-8');
        $this->extra_car_number = mb_strtoupper(str_replace(' ', '', $this->extra_car_number), 'UTF-8');

        //подставляем тип и марку из машины, если нашли по номеру
        $car = Car::findOne(['number' => $this->car_number]);
        if ($car) {
            $this->car_id = $car->id;
            $this->car_number = $car->number;
            $this->mark_id = $car->mark_id;

            if(Yii::$app->user->isGuest == 0) {
                if (Yii::$app->user->identity->role != User::ROLE_ADMIN || !$this->type_id) {
                    $this->type_id = $car->type_id;
                }
            }

            if (empty($this->client_id)) {
                $this->client_id = $car->company_id;
            }
        }

        if (!$this->client_id) {
            $this->addError('client', 'Клиент не выбран.');
            return false;
        }

        if ($insert) {
            //преобразуем Герину поебень в нормальный массив

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
             * суммируем все указанные услуги и считаем доход, расход и прибыль\
             */
            if (!empty($this->serviceList)) {

                $totalExpense = 0;
                $totalIncome = 0;
                foreach ($this->serviceList as $serviceData) {

                    ArrayHelper::getValue($serviceData, 'service_id', null);

                    $clientService = CompanyService::findOne([
                        'service_id' => ArrayHelper::getValue($serviceData, 'service_id', null),
                        'company_id' => $this->client_id,
                        'type_id'    => $this->type_id,
                    ]);
                    if (!empty($clientService) && $clientService->service->is_fixed) {
                        $totalIncome += $clientService->price * $serviceData['amount'];
                    } else {
                        //на 20% увеличиваем цену для клиента
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

            // Проверяем на наличие замещений
            $arrReplaceNeed = [];
            if((Yii::$app->user->identity->id != 1) && (Yii::$app->user->identity->id != 176) && (Yii::$app->user->identity->id != 238)) {

                $replaceArray = ServiceReplace::find()->where(['client_id' => $this->client_id, 'partner_id' => $this->partner_id, 'type' => $this->service_type])->andWhere(['OR', ['type_partner' => 0], ['type_partner' => $this->type_id]])->select('id')->asArray()->column();

                $numServReplace = 0;
                $numServiceTrue = 0;
                $numServiceHaveClient = 0;
                $arrnumServiceReplace = [];

                if (count($replaceArray) > 0) {

                    for ($i = 0; $i < count($replaceArray); $i++) {

                        $replace_id = $replaceArray[$i];

                        $replaceCont = ServiceReplaceItem::find()->where(['replace_id' => $replace_id])->asArray()->select('service_id, company_id, type, car_type')->all();

                        $numServReplace = 0;
                        $numServiceTrue = 0;
                        $numServiceHaveClient = 0;

                        for ($j = 0; $j < count($replaceCont); $j++) {

                            if ($replaceCont[$j]['company_id'] == $this->partner_id) {

                                $numServReplace++;
                                $toNextService = false;

                                foreach ($this->partnerServiceList as $serviceData) {
                                    if($toNextService == false) {
                                        if ($replaceCont[$j]['service_id'] == $serviceData['service_id']) {
                                            $numServiceTrue++;
                                            $toNextService = true;

                                            $index = $replaceCont[$j]['service_id'];

                                            if(isset($arrnumServiceReplace[$index])) {

                                                if($arrnumServiceReplace[$index] >= 1) {
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
                                    if($toNextService == false) {
                                        if ($replaceCont[$j]['service_id'] == $serviceData['service_id']) {
                                            $numServiceHaveClient++;
                                            $toNextService = true;
                                        }
                                    }
                                }


                            }

                            // Нашли нужное замещение
                            if (($j == (count($replaceCont) - 1)) && ($numServReplace == $numServiceTrue) && ($numServReplace > 0)) {
                                $arrReplaceNeed = $replaceCont;
                            }

                        }

                    }
                }

                if($numServiceHaveClient < $numServiceTrue) {
                    $arrReplaceNeed = [];
                }

            }
            // END Проверяем на наличие замещений

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
                            'type_id'    => $this->type_id,
                        ]);
                        if (!empty($companyService) && $companyService->service->is_fixed) {

                            if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) && (isset($serviceData['price'])) && ($serviceData['price'] >= 0)) {
                                $scope->price = ArrayHelper::getValue($serviceData, 'price', 0);
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

                    if(isset($serviceData['parts'])) {
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

                // Выполняем замещение клиент

                $arrClientsType = [];

                if((Yii::$app->user->identity->id != 1) && (Yii::$app->user->identity->id != 176) && (Yii::$app->user->identity->id != 238)) {
                    if (count($arrReplaceNeed) > 0) {
                        // Удаляем услуги выбранные у клиента
                        for ($j = 0; $j < count($arrReplaceNeed); $j++) {
                            if ($arrReplaceNeed[$j]['company_id'] != $this->client_id) {

                                foreach ($this->clientServiceList as $key => $serviceData) {
                                    if (!empty($serviceData['service_id'])) {
                                        if ($arrReplaceNeed[$j]['service_id'] == $serviceData['service_id']) {

                                            $index = $arrReplaceNeed[$j]['service_id'];

                                            if(isset($arrnumServiceReplace[$index])) {

                                                if($arrnumServiceReplace[$index] > 0) {
                                                    $arrnumServiceReplace[$index]--;
                                                    unset($this->clientServiceList[$key]);
                                                }

                                            }

                                        }
                                    }
                                }

                            }
                        }
                        // END Удаляем услуги выбранные у клиента

                    }
                }
                // END Выполняем замещение клиент

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
                            'type_id'    => $this->type_id,
                        ]);

                        if (!empty($companyService) && $companyService->service->is_fixed) {

                            if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) && (isset($serviceData['price'])) && ($serviceData['price'] > 0)) {
                                $scope->price = $serviceData['price'];
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

                    if(isset($serviceData['parts'])) {
                        $scope->parts = $serviceData['parts'];
                    } else {
                        $scope->parts = 0;
                    }

                    $scope->save();
                    $totalIncome += $scope->price * $scope->amount;
                }

                // добавляем замещенные услуги
                for ($j = 0; $j < count($arrReplaceNeed); $j++) {
                    if ($arrReplaceNeed[$j]['company_id'] == $this->client_id) {

                        $haveServiceRepair = false;

                        foreach ($this->clientServiceList as $serviceData) {
                            if($haveServiceRepair == false) {
                                if ($arrReplaceNeed[$j]['service_id'] == $serviceData['service_id']) {
                                    $haveServiceRepair = true;
                                }
                            }
                        }

                        if($haveServiceRepair == false) {

                            $scope = new ActScope();
                            $scope->company_id = $this->client_id;
                            $scope->act_id = $this->id;
                            $scope->service_id = $arrReplaceNeed[$j]['service_id'];
                            $clientService = CompanyService::findOne([
                                'service_id' => $arrReplaceNeed[$j]['service_id'],
                                'company_id' => $this->client_id,
                                'type_id' => ($arrReplaceNeed[$j]['car_type'] > 0) ? $arrReplaceNeed[$j]['car_type'] : $this->type_id,
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
                // END добавляем замещенные услуги

                $this->income = $totalIncome;
            }

            $this->profit = $this->income - $this->expense;
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $kpd = $this->service_type == Service::TYPE_TIRES ? 1.2 : 1;
        //сохраняем картинку чека
        $this->uploadImage();
        /**
         * сохраняем все указанные услуги и дублируем для компании и клиента на первый раз
         */

        // Для замещений и проверку на асинхронные
        $arrReplaceNeed = [];
        $numePartnerService = 0;
        $numeClientService = 0;
        $numReplacePartner = 0;
        $numReplaceClient = 0;

        if ($insert) {

            if (!empty($this->serviceList)) {

                // Проверяем на наличие замещений
                $replaceArray = ServiceReplace::find()->where(['client_id' => $this->client_id, 'partner_id' => $this->partner_id, 'type' => $this->service_type])->andWhere(['OR', ['type_partner' => 0], ['type_partner' => $this->type_id]])->select('id')->asArray()->column();

                $numServReplace = 0;
                $numServiceTrue = 0;
                $arrnumServiceReplace = [];

                if (count($replaceArray) > 0) {

                    for ($i = 0; $i < count($replaceArray); $i++) {

                        $replace_id = $replaceArray[$i];

                        $replaceCont = ServiceReplaceItem::find()->where(['replace_id' => $replace_id])->asArray()->select('service_id, company_id, type, car_type')->all();

                        $numServReplace = 0;
                        $numServiceTrue = 0;

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

                                                if(isset($arrnumServiceReplace[$index])) {

                                                    if($arrnumServiceReplace[$index] >= 1) {
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

                            // Нашли нужное замещение
                            if (($j == (count($replaceCont) - 1)) && ($numServReplace == $numServiceTrue) && ($numServReplace > 0)) {
                                $arrReplaceNeed = $replaceCont;
                                $numReplacePartner = $numServiceTrue;
                                $numReplaceClient = count($replaceCont) - $numReplacePartner;
                            }

                        }

                    }
                }
                // END Проверяем на наличие замещений

                $numRepairServiceClient = 0;

                foreach ($this->serviceList as $serviceData) {

                    $removeServiceClient = false;

                    // Выполняем замещение клиент
                    if (count($arrReplaceNeed) > 0) {
                        for ($j = 0; $j < count($arrReplaceNeed); $j++) {
                            if ($arrReplaceNeed[$j]['company_id'] != $this->client_id) {

                                if ($arrReplaceNeed[$j]['service_id'] == $serviceData['service_id']) {
                                    // Удаляем услуги выбранные у клиента
                                    $removeServiceClient = true;

                                    $index = $arrReplaceNeed[$j]['service_id'];

                                    if(isset($arrnumServiceReplace[$index])) {

                                        if($arrnumServiceReplace[$index] > 0) {
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
                    // END Выполняем замещение клиент

                    // Удаляем услуги выбранные у клиента
                    if ($removeServiceClient == false) {
                        $clientScope = new ActScope();
                        $clientScope->company_id = $this->client_id;
                        $clientScope->act_id = $this->id;
                        if (!empty($serviceData['service_id'])) {
                            $clientScope->service_id = $serviceData['service_id'];
                            $clientService = CompanyService::findOne([
                                'service_id' => $serviceData['service_id'],
                                'company_id' => $this->client_id,
                                'type_id' => $this->type_id,
                            ]);

                            if (!empty($clientService) && $clientService->service->is_fixed) {
                                $clientScope->price = $clientService->price;
                                $clientScope->description = $clientService->service->description;
                            } else {
                                $clientScope->price = $kpd * ArrayHelper::getValue($serviceData, 'price', 0);
                                $clientScope->description =
                                    Service::findOne(['id' => $serviceData['service_id']])->description;
                            }
                        } else {
                            //на 20% увеличиваем цену для клиента
                            $clientScope->price = $kpd * ArrayHelper::getValue($serviceData, 'price', 0);
                            $clientScope->description = ArrayHelper::getValue($serviceData, 'description', 'Нет описания');
                        }
                        $clientScope->amount = $serviceData['amount'];

                        if (isset($serviceData['parts'])) {
                            $clientScope->parts = $serviceData['parts'];
                        } else {
                            $clientScope->parts = 0;
                        }

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

                    $partnerScope->save();
                    $numePartnerService++;
                }

                // Добавляем услуги замещения
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
                                    'type_id' => ($arrReplaceNeed[$j]['car_type'] > 0) ? $arrReplaceNeed[$j]['car_type'] : $this->type_id,
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

                                $clientScope->save();
                                $numeClientService++;

                            }

                        }

                    }
                }
            }
            // END Добавляем услуги замещения

        } else {
            // Для асинхронных актов при редактировании
            // Проверяем на наличие замещений
            $replaceArray = ServiceReplace::find()->where(['client_id' => $this->client_id, 'partner_id' => $this->partner_id, 'type' => $this->service_type])->andWhere(['OR', ['type_partner' => 0], ['type_partner' => $this->type_id]])->select('id')->asArray()->column();

            $numServReplace = 0;
            $numServiceTrue = 0;

            if(count($replaceArray) > 0) {

                for ($i = 0; $i < count($replaceArray); $i++) {

                    $replace_id = $replaceArray[$i];

                    $replaceCont = ServiceReplaceItem::find()->where(['replace_id' => $replace_id])->asArray()->select('service_id, company_id, type, car_type')->all();

                    $numServReplace = 0;
                    $numServiceTrue = 0;

                    for ($j = 0; $j < count($replaceCont); $j++) {

                        if ($replaceCont[$j]['company_id'] == $this->partner_id) {

                            $numServReplace++;
                            $toNextService = false;

                            foreach ($this->partnerServiceList as $serviceData) {
                                if($toNextService == false) {
                                    if ($replaceCont[$j]['service_id'] == $serviceData['service_id']) {
                                        $numServiceTrue++;
                                        $toNextService = true;
                                    }
                                }
                            }

                        }

                        // Нашли нужное замещение
                        if (($j == (count($replaceCont) - 1)) && ($numServReplace == $numServiceTrue) && ($numServReplace > 0)) {
                            $arrReplaceNeed = $replaceCont;
                            $numReplacePartner = $numServiceTrue;
                            $numReplaceClient = count($replaceCont) - $numReplacePartner;
                        }

                    }

                }
            }
            // END Проверяем на наличие замещений

            $numePartnerService = count($this->partnerServiceList);
            $numeClientService = count($this->clientServiceList);

        }

        //Пересчитываем месячный акт
        MonthlyAct::getRealObject($this->service_type)->saveFromAct($this);

        // Ошибочные акты только за предыдущий месяц и свежее
        $dateLastMonth = date('Y-m-01 00:00:00', strtotime("-1 month"));

        if($this->served_at >= strtotime($dateLastMonth)) {

            //Проверяем на ошибки
            $listErrors = $this->getListError();
            ActError::deleteAll(['act_id' => $this->id]);
            if ($this->card_id) {
                Card::markFoundedById($this->card_id);
            }
            foreach ($listErrors as $errorType) {
                $modelActError = new ActError();
                $modelActError->act_id = $this->id;
                $modelActError->error_type = $errorType;
                $modelActError->save();
            }

            // Проверка на убыточный акт
            if ($this->profit < 0) {
                $modelActError = new ActError();
                $modelActError->act_id = $this->id;
                $modelActError->error_type = 19;
                $modelActError->save();
            }

            // Асинхронные акты
            if ((($numeClientService != $numePartnerService) && ((count($arrReplaceNeed) == 0))) || ((count($arrReplaceNeed) > 0) && ((($numReplacePartner > $numReplaceClient) && ($numeClientService < $numePartnerService) && ($numePartnerService != ($numeClientService + ($numReplacePartner - $numReplaceClient)))) || (($numReplaceClient > $numReplacePartner) && ($numePartnerService < $numeClientService) && ($numeClientService != ($numePartnerService + ($numReplaceClient - $numReplacePartner)))) || (($numReplaceClient != $numReplacePartner) && ($numePartnerService == $numeClientService))))) {
                $modelActError = new ActError();
                $modelActError->act_id = $this->id;
                $modelActError->error_type = 20;
                $modelActError->save();
            }

        }
        // Ошибочные акты только за предыдущий месяц и свежее

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Перед удалением
     *
     * @return bool
     */
    public function beforeDelete()
    {

        // Контроль штрафов
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