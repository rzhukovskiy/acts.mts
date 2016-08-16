<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.08.2016
 * Time: 0:27
 */

namespace common\models;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

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
 * @property array $time_str
 *
 * @property Company $client
 * @property Company $partner
 * @property Type $type
 * @property Mark $mark
 * @property Card $card
 * @property Car $car
 * @property ActScope[] $scopes
 */
class Act extends ActiveRecord
{
    const STATUS_NEW = 0;
    const STATUS_CLOSED = 1;
    const STATUS_FIXED = 2;
    
    public $serviceList;
    public $time_str;
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
            [['check', 'expense', 'income', 'profit', 'service_type', 'serviceList', 'time_str'], 'safe'],
            ['service_type', 'default', 'value' => Service::TYPE_WASH],
        ];
    }

    /**
     * @return Company
     */
    public function getClient()
    {
        return $this->hasOne(Company::className(), ['id' => 'client_id']);
    }

    /**
     * @return Company
     */
    public function getPartner()
    {
        return $this->hasOne(Company::className(), ['id' => 'partner_id']);
    }

    /**
     * @return Card
     */
    public function getCard()
    {
        return $this->hasOne(Card::className(), ['id' => 'card_id']);
    }

    /**
     * @return Mark
     */
    public function getMark()
    {
        return $this->hasOne(Mark::className(), ['id' => 'mark_id']);
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'type_id']);
    }

    /**
     * @return ActScope[]
     */
    public function getScopes()
    {
        return $this->hasMany(ActScope::className(), ['act_id' => 'id']);
    }

    public function beforeSave($insert)
    {
        if (!empty($this->time_str)) {
            $this->served_at = \DateTime::createFromFormat('d-m-Y', $this->time_str)->getTimestamp();
        }
        if ($insert) {
            //определяем клиента по карте
            if (empty($this->client_id)) {
                $card = Card::findOne(['number' => $this->card_id]);

                if (empty($card)) {
                    return false;
                }

                $this->client_id = $card->company->id;
            }

            //номер в верхний регистр
            $this->number = mb_strtoupper(str_replace(' ', '', $this->number), 'UTF-8');
            $this->extra_number = mb_strtoupper(str_replace(' ', '', $this->extra_number), 'UTF-8');

            //подставляем тип и марку из машины, если нашли по номеру
            $car = Car::findOne(['number' => $this->number]);
            if ($car) {
                $this->mark_id = $car->mark_id;
                $this->type_id = $car->type_id;
            }

            /**
             * суммируем все указанные услуги и считаем доход, расход и прибыль\
             */
            if (!empty($this->serviceList)) {
                foreach ($this->serviceList as $serviceData) {
                    $totalExpense = 0;
                    $totalIncome = 0;

                    $clientService = CompanyService::findOne(['service_id' => $serviceData['service_id'], 'company_id' => $this->client_id]);
                    if (!empty($clientService) && $clientService->service->is_fixed) {
                        $totalIncome += $clientService->price * $serviceData['amount'];
                    } else {
                        //на 20% увеличиваем цену для клиента
                        $totalIncome += 1.2 * $serviceData['price'] * $serviceData['amount'];
                    }

                    $partnerService = CompanyService::findOne(['service_id' => $serviceData['service_id'], 'company_id' => $this->partner_id]);
                    if (!empty($clientService) && $clientService->service->is_fixed) {
                        $totalExpense += $partnerService->price * $serviceData['amount'];
                    } else {
                        $totalExpense += $serviceData['price'] * $serviceData['amount'];
                    }
                }

                $this->income = $totalIncome;
                $this->expense = $totalExpense;
                $this->profit = $this->income - $this->expense;
            }
        }

        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        /**
         * сохраняем все указанные услуги и дублируем для компании и клиента на первый раз
         */
        if ($insert) {
            if (!empty($this->serviceList)) {
                print_r($this->serviceList);die;
                foreach ($this->serviceList as $serviceData) {
                    $clientScope = new ActScope();
                    $clientScope->company_id = $this->client_id;
                    $clientScope->act_id = $this->id;
                    $clientService = CompanyService::findOne(['service_id' => $serviceData['service_id'], 'company_id' => $this->client_id]);

                    if (!empty($clientService) && $clientService->service->is_fixed) {
                        $clientScope->company_service_id = $clientService->id;
                        $clientScope->price = $clientService->price;
                        $clientScope->description = $clientService->service->description;
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
                    $partnerService = CompanyService::findOne(['service_id' => $serviceData['service_id'], 'company_id' => $this->partner_id]);
                    if (!empty($partnerService) && $partnerService->service->is_fixed) {
                        $partnerScope->company_service_id = $partnerService->id;
                        $partnerScope->price = $partnerService->price;
                        $partnerScope->description = $partnerService->service->description;
                    } else {
                        $clientScope->price = $serviceData['price'];
                        $partnerScope->description = $serviceData['description'];
                    }
                    $partnerScope->amount = $serviceData['amount'];
                    $partnerScope->save();
                }
            }            
        } else {
            //TODO: act editing for admin and partner
        }

        parent::afterSave($insert, $changedAttributes);
    }
}