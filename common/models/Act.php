<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.08.2016
 * Time: 0:27
 */

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

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
 *
 * @property Company $client
 * @property Company $partner
 * @property Type $type
 * @property Mark $mark
 * @property Card $card
 * @property Car $car
 * @property ActScope $scopes
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

    public $serviceList;
    public $clientServiceList;
    public $partnerServiceList;
    public $time_str;
    public $actsCount;
    /**
     * @var UploadedFile
     */
    public $image;

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
            [['partner_id', 'card_id', 'mark_id', 'type_id', 'number'], 'required'],
            [['check', 'expense', 'income', 'profit', 'service_type', 'serviceList', 'time_str', 'partnerServiceList', 'clientServiceList'], 'safe'],
            [['image'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
            ['service_type', 'default', 'value' => Service::TYPE_WASH],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'served_at' => 'Дата',
            'partner_id' => 'Партнер',
            'client_id' => 'Клиент',
            'card_id' => 'Карта',
            'number' => 'Номер',
            'extra_number' => 'п/п',
            'mark_id' => 'Марка',
            'type_id' => 'Тип',
            'income' => 'Сумма',
            'expense' => 'Сумма',
            'check' => 'Чек',
            'period' => 'Период',
            'day' => 'День',
            'time_str' => 'Дата',
            'image' => 'Загрузка чека',
            'actsCount' => 'Обслуживания',
        ];
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
     * @return bool|string
     */
    public function getImageLink()
    {
        if (file_exists('files/checks/' . $this->id . '.jpg')) {
            return '/files/checks/' . $this->id . '.jpg';
        }
        if (file_exists('files/checks/' . $this->id . '.png')) {
            return '/files/checks/' . $this->id . '.png';
        }
        return false;
    }

    public function hasError($error)
    {
        $hasError = false;
        switch ($error) {
            case 'expense':
                $hasError = !$this->expense;
                break;
            case 'income':
                $hasError = !$this->income;
                break;
            case 'check':
                $hasError = $this->service_type == Service::TYPE_WASH && !$this->getImageLink();
                break;
            case 'card':
                $hasError = $this->service_type != Service::TYPE_DISINFECT && $this->card->company_id != $this->car->company_id;
                break;
            case 'car':
                $hasError = !isset($this->car->company_id);
                break;
            case 'truck':
                $hasError = (isset($this->client) && $this->client->is_split && !$this->extra_number) ||
                    (isset($this->client) && $this->client->is_split && $this->extra_number && !Car::model()->find('number = :number', [':number' => $this->extra_number]));
                break;
        }

        return !$this->status != self::STATUS_FIXED && $hasError;
    }

    public function beforeSave($insert)
    {
        if (!empty($this->time_str)) {
            $this->served_at = \DateTime::createFromFormat('d-m-Y', $this->time_str)->getTimestamp();
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
            $this->type_id = $car->type_id;

            if (empty($this->client_id)) {
                $this->client_id = $car->company_id;
            }
        }

        if (empty($this->client_id)) {
            return false;
        }

        if ($insert) {
            //преобразуем Герину поебень в нормальный массив
            if (!empty($this->serviceList[0]) && count(explode('+', $this->serviceList[0]['service_id'])) > 1) {
                $serviceList = [];
                foreach (explode('+', $this->serviceList[0]['service_id']) as $serviceId) {
                    $serviceList[] = [
                        'service_id' => $serviceId,
                        'price' => $this->serviceList[0]['price'],
                        'amount' => $this->serviceList[0]['amount'],
                    ];
                }
                $this->serviceList = $serviceList;
            }
            /**
             * суммируем все указанные услуги и считаем доход, расход и прибыль\
             */
            if (!empty($this->serviceList)) {
                $totalExpense = 0;
                $totalIncome = 0;
                foreach ($this->serviceList as $serviceData) {
                    $clientService = CompanyService::findOne(['service_id' => $serviceData['service_id'], 'company_id' => $this->client_id]);
                    if (!empty($clientService) && $clientService->service->is_fixed) {
                        $totalIncome += $clientService->price * $serviceData['amount'];
                    } else {
                        //на 20% увеличиваем цену для клиента
                        $totalIncome += 1.2 * $serviceData['price'] * $serviceData['amount'];
                    }

                    $partnerService = CompanyService::findOne(['service_id' => $serviceData['service_id'], 'company_id' => $this->partner_id]);
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
            ActScope::deleteAll(['act_id' => $this->id]);
            if (!empty($this->partnerServiceList)) {
                foreach ($this->partnerServiceList as $serviceData) {
                    if (empty($serviceData['service_id']) && empty($serviceData['description'])) {
                        continue;
                    }
                    $scope = new ActScope();
                    $scope->company_id = $this->partner_id;
                    $scope->act_id = $this->id;
                    if (!empty($serviceData['service_id'])) {
                        $scope->service_id = $serviceData['service_id'];
                        $companyService = CompanyService::findOne(['service_id' => $serviceData['service_id'], 'company_id' => $this->partner_id]);

                        if (!empty($companyService) && $companyService->service->is_fixed) {
                            $scope->price = $serviceData['price'] ? $serviceData['price'] : $companyService->price;
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
            }

            if (!empty($this->clientServiceList)) {
                foreach ($this->clientServiceList as $serviceData) {
                    if (empty($serviceData['service_id']) && empty($serviceData['description'])) {
                        continue;
                    }
                    $scope = new ActScope();
                    $scope->company_id = $this->client_id;
                    $scope->act_id = $this->id;
                    if (!empty($serviceData['service_id'])) {
                        $scope->service_id = $serviceData['service_id'];
                        $companyService = CompanyService::findOne(['service_id' => $serviceData['service_id'], 'company_id' => $this->client_id]);

                        if (!empty($companyService) && $companyService->service->is_fixed) {
                            $scope->price = $serviceData['price'] ? $serviceData['price'] : $companyService->price;
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
            }

            $this->income = $totalIncome;
            $this->expense = $totalExpense;
            $this->profit = $this->income - $this->expense;
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        /**
         * сохраняем все указанные услуги и дублируем для компании и клиента на первый раз
         */
        if ($insert) {
            //сохраняем картинку чека
            if ($this->image) {
                $this->image->saveAs('files/checks/' . $this->id . '.' . $this->image->extension);
            }

            if (!empty($this->serviceList)) {
                foreach ($this->serviceList as $serviceData) {
                    $clientScope = new ActScope();
                    $clientScope->company_id = $this->client_id;
                    $clientScope->act_id = $this->id;
                    if (!empty($serviceData['service_id'])) {
                        $clientScope->service_id = $serviceData['service_id'];
                        $clientService = CompanyService::findOne(['service_id' => $serviceData['service_id'], 'company_id' => $this->client_id]);

                        if (!empty($clientService) && $clientService->service->is_fixed) {
                            $clientScope->price = $clientService->price;
                            $clientScope->description = $clientService->service->description;
                        } else {
                            $clientScope->price = 1.2 * $serviceData['price'];
                            $clientScope->description = Service::findOne(['id' => $serviceData['service_id']])->description;
                        }
                    } else {
                        //на 20% увеличиваем цену для клиента
                        $clientScope->price = 1.2 * $serviceData['price'];
                        $clientScope->description = $serviceData['description'];
                    }
                    $clientScope->amount = $serviceData['amount'];
                    $clientScope->save();

                    $partnerScope = new ActScope();
                    $partnerScope->company_id = $this->partner_id;
                    $partnerScope->act_id = $this->id;
                    if (!empty($serviceData['service_id'])) {
                        $partnerScope->service_id = $serviceData['service_id'];
                        $partnerService = CompanyService::findOne(['service_id' => $serviceData['service_id'], 'company_id' => $this->partner_id]);
                        if (!empty($partnerService) && $partnerService->service->is_fixed) {
                            $partnerScope->price = $partnerService->price;
                            $partnerScope->description = $partnerService->service->description;
                        } else {
                            $partnerScope->price = $serviceData['price'];
                            $partnerScope->description = Service::findOne(['id' => $serviceData['service_id']])->description;
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

        parent::afterSave($insert, $changedAttributes);
    }
}