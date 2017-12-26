<?php

namespace common\models;

use common\models\query\CarQuery;
use frontend\models\Penalty;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%car}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $number
 * @property string $cert
 * @property integer $mark_id
 * @property integer $type_id
 * @property integer $is_penalty
 * @property integer $is_infected
 *
 * @property Company $company
 * @property Mark $mark
 * @property Type $type
 * @property Act $acts
 */
class Car extends ActiveRecord
{
    const SCENARIO_INFECTED = 'infected';
    const SCENARIO_OWNER = 'owner';
    
    public $carsCountByType;
    public $listService;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%car}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'number'], 'required'],
            [['company_id', 'mark_id', 'type_id', 'is_infected', 'is_penalty'], 'integer'],
            [['cert'], 'string'],
            ['is_infected', 'default', 'value' => 0],
            [['number'], 'unique'],
            [['number'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Компания',
            'number' => 'Номер',
            'mark_id' => 'Марка',
            'type_id' => 'Тип',
            'is_infected' => 'Дизенфицировать',
            'period' => 'Период',
            'is_penalty' => 'Штрафы',
            'cert' => 'Свидетельства о регистрации ТС',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CarQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CarQuery(get_called_class());
    }

    public function getMark(  )
    {
        return $this->hasOne(Mark::className(), ['id' => 'mark_id']);
    }

    public function getType()
    {
        return $this->hasOne(Type::className(), ['id' => 'type_id']);
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getActs()
    {
        return $this->hasMany(Act::className(), ['number' => 'number']);
    }

    public function beforeSave($insert)
    {

        if($this->isNewRecord) {
            $this->is_penalty = 1;
        }

        // Контроль штрафов
        if($this->is_penalty == 1) {

            // Проверка включен ли контроль штрафов в компании
            $companyModel = Company::findOne(['id' => $this->company_id]);

            if($companyModel->use_penalty == 1) {

            $modelInfo = CompanyInfo::findOne(['company_id' => $this->company_id]);

            if (isset($modelInfo)) {

                if ((mb_strlen($modelInfo->inn) > 3) && (mb_strlen($this->cert) > 3)) {
                } else {
                    $this->is_penalty = 0;
                }

            } else {
                $this->is_penalty = 0;
            }

            } else {
                $this->is_penalty = 0;
            }

        }
        // Контроль штрафов

        //номер в верхний регистр
        $this->number = mb_strtoupper(str_replace(' ', '', $this->number), 'UTF-8');
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {

        // Контроль штрафов
        if($this->is_penalty == 1) {

            $modelInfo = CompanyInfo::findOne(['company_id' => $this->company_id]);

            if (isset($modelInfo)) {

                if ((mb_strlen($modelInfo->inn) > 3) && (mb_strlen($this->cert) > 3)) {

                    $modelPenalty = new Penalty();
                    $modelPenalty->createToken();

                    // Получаем токен
                    $token = $modelPenalty->createToken();
                    $resToken = json_decode($token[1], true);

                    // Сохраняем полученный токен
                    $modelPenalty->setParams(['token' => $resToken['token']]);

                    // Добавление ТС
                    $addCars = $modelPenalty->createClientCar($this->company_id . '@mtransservice.ru', ['name' => '', 'cert' => $this->cert, 'reg' => $this->number]);
                    $resAddCars = json_decode($addCars[1], true);

                    if(isset($resAddCars['errors'])) {
                        // Ошибка
                        Car::updateAll(['is_penalty' => 0], 'company_id = ' . $this->company_id . ' AND number="' . $this->number . '"');
                    } else {
                    }
                    // Добавление ТС

                }

            }

        } else {

            if (!$insert) {

                $modelPenalty = new Penalty();
                $modelPenalty->createToken();

                // Получаем токен
                $token = $modelPenalty->createToken();
                $resToken = json_decode($token[1], true);

                // Сохраняем полученный токен
                $modelPenalty->setParams(['token' => $resToken['token']]);

                $carList = $modelPenalty->getClientCars($this->company_id . '@mtransservice.ru');
                $resCarList = json_decode($carList[1], true);

                if (isset($resCarList['cars'])) {
                    $arrCarsList = $resCarList['cars'];

                    for ($i = 0; $i < count($arrCarsList); $i++) {
                        if (($arrCarsList[$i]['reg'] == $this->number) || (mb_strtoupper(str_replace(' ', '', $arrCarsList[$i]['reg']), 'UTF-8') == $this->number)) {

                            $delCar = $modelPenalty->deleteClientCar($this->company_id . '@mtransservice.ru', $arrCarsList[$i]['id']);
                            $resDel = json_decode($delCar[1], true);

                            if (isset($resDel['errors'])) {
                                // Ошибка
                                Car::updateAll(['is_penalty' => 1], 'company_id = ' . $this->company_id . ' AND number="' . $this->number . '"');
                            }

                        }
                    }

                }

            }

        }
        // Контроль штрафов

        if ($insert) {
            //если эта машина уже упоминалась в актах - пересохраняем ее
            $listAct = Act::findAll(['status' => Act::STATUS_NEW, 'car_number' => $this->number]);
            foreach ($listAct as $act) {
                $act->save();
            }
        }

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
        if($this->is_penalty == 1) {
            $modelPenalty = new Penalty();
            $modelPenalty->createToken();

            // Получаем токен
            $token = $modelPenalty->createToken();
            $resToken = json_decode($token[1], true);

            // Сохраняем полученный токен
            $modelPenalty->setParams(['token' => $resToken['token']]);

            $carList = $modelPenalty->getClientCars($this->company_id . '@mtransservice.ru');
            $resCarList = json_decode($carList[1], true);

            if(isset($resCarList['cars'])) {
                $arrCarsList = $resCarList['cars'];

                for ($i = 0; $i < count($arrCarsList); $i++) {
                    if (($arrCarsList[$i]['reg'] == $this->number) || (mb_strtoupper(str_replace(' ', '', $arrCarsList[$i]['reg']), 'UTF-8') == $this->number)) {

                        $delCar = $modelPenalty->deleteClientCar($this->company_id . '@mtransservice.ru', $arrCarsList[$i]['id']);
                        $resDel = json_decode($delCar[1], true);

                        if (isset($resDel['errors'])) {
                            // Ошибка
                            Car::updateAll(['is_penalty' => 1], 'company_id = ' . $this->company_id . ' AND number="' . $this->number . '"');
                        }

                    }
                }

            }

        }

        return true;
    }

}
