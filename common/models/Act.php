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
 * @property integer $status
 * @property integer $expense
 * @property integer $income
 * @property integer $profit
 * @property integer $service_type
 * @property integer $served_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $check
 * @property string $number
 * @property string $extra_number
 *
 * @property array $serviceList
 * @property array $clientServiceList
 * @property array $partnerServiceList
 * @property string $card_number
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

    private $card_number;

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
            [['partner_id', 'number'], 'required'],
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
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
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
            'client_id'    => 'Клиент',
            'card_id'      => 'Карта',
            'card_number'  => 'Карта',
            'number'       => 'Номер',
            'extra_number' => 'п/п',
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
        return $this->hasOne(Car::className(), ['number' => 'number']);
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
        $this->client_id = $car->company_id;
        $this->number = $car->number;
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
                    $hasError = $hasError || !$scope->price;
                }
                break;
            case self::ERROR_INCOME:
                $hasError = !$this->income;
                foreach ($this->clientScopes as $scope) {
                    $hasError = $hasError || !$scope->price;
                }
                break;
            case self::ERROR_CHECK:
                $hasError = $this->service_type == Service::TYPE_WASH && (!$this->check || !$this->getImageLink());
                break;
            case self::ERROR_CARD:
                $hasError =
                    ($this->service_type != Service::TYPE_DISINFECT) &&
                    (empty($this->card->company_id) || 
                        (!empty($this->car->company_id) && $this->card->company_id != $this->car->company_id));
                break;
            case self::ERROR_CAR:
                $hasError =
                    !isset($this->car->company_id) ||
                    ($this->service_type != Service::TYPE_DISINFECT && $this->car->company_id != $this->client_id);
                break;
            case self::ERROR_TRUCK:
                $hasError =
                    (isset($this->client) && $this->client->is_split && !$this->extra_number) ||
                    (isset($this->client) &&
                        $this->client->is_split &&
                        $this->extra_number &&
                        !Car::find()->byNumber($this->extra_number)->exists());
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
            self::ERROR_CARD    => (empty($this->card->company_id)) ? 'Не существует такой номер карты' : (
            (empty($this->car->company_id)) ? false :
                'Не совпадает номер карты с номером ТС.<br>
                Карта - ' .
                (isset($this->card->company) ? $this->card->company->name : 'Неизвестна') .
                '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
                ' ТС - ' . (isset($this->car->company) ? $this->car->company->name : 'Неизвестна')
            ),
            self::ERROR_CAR     => empty($this->car->company_id) ? 'Некорректный номер ТС' : false,
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

    public function setCard_number($value)
    {
        $card = Card::findOne(['number' => $value]);
        $this->card_id = $card ? $card->id : $value;

        $this->card_number = $value;
    }

    public function getCard_number()
    {
        $card = Card::findOne($this->card_id);
        return $card ? $card->number : $this->card_id;
    }

    public function beforeSave($insert)
    {
        $kpd = $this->service_type == Service::TYPE_TIRES ? 1.2 : 1;
        
        if (!empty($this->card_number)) {
            $card = Card::findOne(['number' => $this->card_number]);
            $this->card_id = $card ? $card->id : $this->card_number;
        }

        if (!empty($this->time_str)) {
            $this->served_at =
                \DateTime::createFromFormat('d-m-Y H:i:s', $this->time_str . ' 12:00:00')->getTimestamp();
        }

        $LockedLisk = Lock::CheckLocked(date('n-Y', $this->served_at), $this->service_type);
        $is_locked = false;

        if(count($LockedLisk) > 0) {

            $CloseAll = false;
            $CloseCompany = false;

            for ($c = 0; $c < count($LockedLisk); $c++) {
                if ($LockedLisk[$c]["company_id"] == 0) {
                    $CloseAll = true;
                }
                if ($LockedLisk[$c]["company_id"] == $this->partner_id) {
                    $CloseCompany = true;
                }
            }

            if (($CloseAll == true) && ($CloseCompany == false)) {
                $is_locked = true;
            } elseif (($CloseAll == false) && ($CloseCompany == true)) {
                $is_locked = true;
            }

        }

        if ($insert && $is_locked) {
            $this->addError('period', 'This period is locked');
            return false;
        }

        //определяем клиента по карте
        if (!empty($this->card)) {
            $this->client_id = $this->card->company_id;
        }

        //номер в верхний регистр
        $this->number = mb_strtoupper(str_replace(' ', '', $this->number), 'UTF-8');
        $this->extra_number = mb_strtoupper(str_replace(' ', '', $this->extra_number), 'UTF-8');

        //подставляем тип и марку из машины, если нашли по номеру
        $car = Car::findOne(['number' => $this->number]);
        if ($car) {
            $this->mark_id = $car->mark_id;
            if (Yii::$app->user->identity->role != User::ROLE_ADMIN || !$this->type_id) {
                $this->type_id = $car->type_id;
            }

            if (empty($this->client_id)) {
                $this->client_id = $car->company_id;
            }
        }

        if (empty($this->client_id)) {
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
                        $totalIncome += $kpd * $serviceData['price'] * $serviceData['amount'];
                    }

                    $partnerService = CompanyService::findOne([
                        'service_id' => ArrayHelper::getValue($serviceData, 'service_id', null),
                        'company_id' => $this->partner_id,
                        'type_id'    => $this->type_id,
                    ]);
                    if (!empty($partnerService) && $partnerService->service->is_fixed) {
                        $totalExpense += $partnerService->price * $serviceData['amount'];
                    } else {
                        $totalExpense += $serviceData['price'] * $serviceData['amount'];
                    }
                }

                $this->income = $totalIncome;
                $this->expense = $totalExpense;
                $this->profit = $this->income - $this->expense;
            } else {
                return false;
            }
        } else {
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
                            $scope->price = $companyService->price;
                            $scope->description = $companyService->service->description;
                        } else {
                            $scope->price = $serviceData['price'];
                            $scope->description = Service::findOne(['id' => $serviceData['service_id']])->description;
                        }
                    } else {
                        $scope->price = $serviceData['price'];
                        $scope->description = $serviceData['description'];
                    }
                    $scope->amount = $serviceData['amount'];
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
                            $scope->price = $companyService->price;
                            $scope->description = $companyService->service->description;
                        } else {
                            $scope->price = $serviceData['price'];
                            $scope->description = Service::findOne(['id' => $serviceData['service_id']])->description;
                        }
                    } else {
                        $scope->price = $serviceData['price'];
                        $scope->description = $serviceData['description'];
                    }
                    $scope->amount = $serviceData['amount'];
                    $scope->save();
                    $totalIncome += $scope->price * $scope->amount;
                }

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
        if ($insert) {

            if (!empty($this->serviceList)) {
                foreach ($this->serviceList as $serviceData) {
                    $clientScope = new ActScope();
                    $clientScope->company_id = $this->client_id;
                    $clientScope->act_id = $this->id;
                    if (!empty($serviceData['service_id'])) {
                        $clientScope->service_id = $serviceData['service_id'];
                        $clientService = CompanyService::findOne([
                            'service_id' => $serviceData['service_id'],
                            'company_id' => $this->client_id,
                            'type_id'    => $this->type_id,
                        ]);

                        if (!empty($clientService) && $clientService->service->is_fixed) {
                            $clientScope->price = $clientService->price;
                            $clientScope->description = $clientService->service->description;
                        } else {
                            $clientScope->price = $kpd * $serviceData['price'];
                            $clientScope->description =
                                Service::findOne(['id' => $serviceData['service_id']])->description;
                        }
                    } else {
                        //на 20% увеличиваем цену для клиента
                        $clientScope->price = $kpd * $serviceData['price'];
                        $clientScope->description = ArrayHelper::getValue($serviceData, 'description', 'Нет описания');
                    }
                    $clientScope->amount = $serviceData['amount'];
                    $clientScope->save();

                    $partnerScope = new ActScope();
                    $partnerScope->company_id = $this->partner_id;
                    $partnerScope->act_id = $this->id;
                    if (!empty($serviceData['service_id'])) {
                        $partnerScope->service_id = $serviceData['service_id'];
                        $partnerService = CompanyService::findOne([
                            'service_id' => $serviceData['service_id'],
                            'company_id' => $this->partner_id,
                            'type_id'    => $this->type_id,
                        ]);
                        if (!empty($partnerService) && $partnerService->service->is_fixed) {
                            $partnerScope->price = $partnerService->price;
                            $partnerScope->description = $partnerService->service->description;
                        } else {
                            $partnerScope->price = $serviceData['price'];
                            $partnerScope->description =
                                Service::findOne(['id' => $serviceData['service_id']])->description;
                        }
                    } else {
                        $partnerScope->price = $serviceData['price'];
                        $partnerScope->description = $serviceData['description'];
                    }
                    $partnerScope->amount = $serviceData['amount'];
                    $partnerScope->save();
                }
            }
        }
        //Пересчитываем месячный акт
        MonthlyAct::getRealObject($this->service_type)->saveFromAct($this);

        //Проверяем на ошибки
        $listErrors = $this->getListError();
        ActError::deleteAll(['act_id' => $this->id]);
        Card::markFounded($this->card_number);
        foreach ($listErrors as $errorType) {
            $modelActError = new ActError();
            $modelActError->act_id = $this->id;
            $modelActError->error_type = $errorType;
            $modelActError->save();
        }

        parent::afterSave($insert, $changedAttributes);
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

            return $image->resize(self::ACT_WIDTH, self::ACT_HEIGHT)->save($imagePath);

//            $imagePath = \Yii::getAlias('@webroot/files/checks/' . $this->id . '.' . $this->image->extension);
//            $this->image->saveAs($imagePath);
        }

        return false;
    }
}