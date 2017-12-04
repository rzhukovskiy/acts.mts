<?php

namespace common\models\search;

use common\models\Company;
use common\models\Service;
use yii;
use yii\data\ActiveDataProvider;
use common\models\Act;

/**
 * ActSearch represents the model behind the search form about `common\models\Act`.
 * @property string $period
 * @property integer $day
 */
class ActSearch extends Act
{
    public $dateFrom;
    public $dateTo;
    public $period;
    public $createDay;
    public $day;
    public $address;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_number', 'partner_id', 'card_id', 'mark_id', 'type_id', 'day', 'service_type'], 'integer'],
            [['car_number', 'extra_car_number', 'period', 'address'], 'string'],
            ['period', 'default', 'value' => date('n') . '-' . date('Y'), 'on' => self::SCENARIO_CLIENT],
            ['period', 'default', 'value' => date('n') . '-' . date('Y'), 'on' => self::SCENARIO_PARTNER],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return [
            self::SCENARIO_CAR => ['card_number', 'card_id', 'car_number', 'dateFrom', 'dateTo'],
            self::SCENARIO_CLIENT => ['card_number', 'check', 'client_id', 'card_id', 'mark_id', 'type_id', 'day', 'car_number', 'extra_car_number', 'period', 'service_type', 'address'],
            self::SCENARIO_PARTNER => ['card_number', 'check', 'partner_id', 'card_id', 'mark_id', 'type_id', 'day', 'car_number', 'extra_car_number', 'period', 'service_type', 'address'],
            self::SCENARIO_ERROR => ['card_number', 'check', 'service_type', 'client_id', 'partner_id', 'card_id', 'mark_id', 'type_id', 'car_number', 'extra_car_number'],
            self::SCENARIO_LOSSES => ['card_number', 'check', 'service_type', 'client_id', 'partner_id', 'card_id', 'mark_id', 'type_id', 'car_number', 'extra_car_number'],
            self::SCENARIO_HISTORY => ['card_number', 'client_id', 'car_number', 'dateFrom', 'dateTo', 'service_type', 'address', 'mark_id', 'type_id'],
            'default' => [],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Act::find();

        // Если не выбран период то показываем только прошлый месяц
        if((!isset($params['ActSearch']['dateFrom'])) && (!isset($params['ActSearch']['dateTo']))) {

            $params['ActSearch']['dateFrom'] = date("Y-m-t", strtotime("-2 month")) . 'T21:00:00.000Z';
            $params['ActSearch']['dateTo'] = date("Y-m-t", strtotime("-1 month")) . 'T21:00:00.000Z';
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $this->load($params);

        //для не админа жестко задаем company_id
        if (!empty(Yii::$app->user->identity->company_id) && !$this->client_id && Yii::$app->user->identity->company->type == Company::TYPE_OWNER) {
            $this->client_id = Yii::$app->user->identity->company->id;
        }
        if (!empty(Yii::$app->user->identity->company_id) && !$this->partner_id && Yii::$app->user->identity->company->type != Company::TYPE_OWNER) {
            $this->partner_id = Yii::$app->user->identity->company->id;
        }


        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        switch ($this->scenario) {
            case self::SCENARIO_ERROR:
                $query->joinWith([
                    'type',
                    'mark',
                    'car car',
                    'card as card',
                    'client as client',
                    'partner as partner',
                    'car as car',
                    'actErrors as actErrors',
                ]);
                $query->andWhere(['is NOT', 'actErrors.act_id', null]);
                $query->andWhere(['!=', 'actErrors.error_type', 19]);
                $query->orderBy('partner_id, served_at');
                break;
            case self::SCENARIO_LOSSES:
                $query->joinWith([
                    'type',
                    'mark',
                    'car car',
                    'card as card',
                    'client as client',
                    'partner as partner',
                    'car as car',
                    'actErrors as actErrors',
                ]);
                $query->andWhere(['is NOT', 'actErrors.act_id', null]);
                $query->andWhere(['actErrors.error_type' => 19]);
                $query->orderBy('partner_id, served_at');
                break;
            case self::SCENARIO_CLIENT:

                if($this->service_type == Company::TYPE_PENALTY) {
                    $query->joinWith([
                        'type',
                        'mark',
                        'card',
                        'car',
                        'penaltyinfo',
                        'partner partner',
                        'client client',
                    ]);
                } else {
                    $query->joinWith([
                        'type',
                        'mark',
                        'card',
                        'car',
                        'partner partner',
                        'client client',
                    ]);
                }

                if (!empty($this->client->children)) {

                    if (!isset(Yii::$app->request->queryParams['ActSearch']['client_id'])) {

                        if ($this->client_id == 59) {
                            $queryPar = Company::find()->where(['parent_id' => $this->client_id])->all();

                            $arrParParIds = [];

                            for ($i = 0; $i < count($queryPar); $i++) {
                                $arrParParIds[] = $queryPar[$i]['id'];

                                $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                                for ($j = 0; $j < count($queryParPar); $j++) {
                                    $arrParParIds[] = $queryParPar[$j]['id'];
                                }

                            }

                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id], ['client_id' => $arrParParIds]]);

                        } else {
                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                        }

                    } else {

                        if ((Yii::$app->request->queryParams['ActSearch']['client_id'] == '') || (Yii::$app->request->queryParams['ActSearch']['client_id'] <= 0)) {

                            if ($this->client_id == 59) {
                                $queryPar = Company::find()->where(['parent_id' => $this->client_id])->all();

                                $arrParParIds = [];

                                for ($i = 0; $i < count($queryPar); $i++) {
                                    $arrParParIds[] = $queryPar[$i]['id'];

                                    $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                                    for ($j = 0; $j < count($queryParPar); $j++) {
                                        $arrParParIds[] = $queryParPar[$j]['id'];
                                    }

                                }

                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id], ['client_id' => $arrParParIds]]);

                            } else {
                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                            }

                        } else {
                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                        }

                    }

                } else {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }
                $query->orderBy('client.parent_id, act.client_id, served_at');
                break;

            case self::SCENARIO_PARTNER:

                if($this->service_type == Company::TYPE_PENALTY) {
                    $query->joinWith([
                        'type',
                        'mark',
                        'card',
                        'car',
                        'penaltyinfo',
                        'partner partner',
                        'client client',
                    ]);
                } else {
                    $query->joinWith([
                        'type',
                        'mark',
                        'card',
                        'car',
                        'partner partner',
                        'client client',
                    ]);
                }

                $query->andFilterWhere(['partner_id' => $this->partner_id]);
                $query->orderBy('partner.parent_id, act.partner_id, served_at');
                break;

            case self::SCENARIO_HISTORY:
                $query->joinWith([
                    'type',
                    'mark',
                    'client client',
                    'partner partner',
                    'car car',
                ]);

                if ($this->dateFrom) {
                    $query->andFilterWhere(['between', 'served_at', strtotime($this->dateFrom), strtotime($this->dateTo)]);
                }
                if (!empty($this->client->children)) {

                    if (!isset(Yii::$app->request->queryParams['ActSearch']['client_id'])) {

                        if ($this->client_id == 59) {
                            $queryPar = Company::find()->where(['parent_id' => $this->client_id])->all();

                            $arrParParIds = [];

                            for ($i = 0; $i < count($queryPar); $i++) {
                                $arrParParIds[] = $queryPar[$i]['id'];

                                $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                                for ($j = 0; $j < count($queryParPar); $j++) {
                                    $arrParParIds[] = $queryParPar[$j]['id'];
                                }

                            }

                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id], ['client_id' => $arrParParIds]]);

                        } else {
                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                        }

                    } else {

                        if ((Yii::$app->request->queryParams['ActSearch']['client_id'] == '') || (Yii::$app->request->queryParams['ActSearch']['client_id'] <= 0)) {

                            if ($this->client_id == 59) {
                                $queryPar = Company::find()->where(['parent_id' => $this->client_id])->all();

                                $arrParParIds = [];

                                for ($i = 0; $i < count($queryPar); $i++) {
                                    $arrParParIds[] = $queryPar[$i]['id'];

                                    $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                                    for ($j = 0; $j < count($queryParPar); $j++) {
                                        $arrParParIds[] = $queryParPar[$j]['id'];
                                    }

                                }

                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id], ['client_id' => $arrParParIds]]);

                            } else {
                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                            }

                        } else {

                            // МФП выдает неверную детальную среднюю статистику обслуживаний на 1тс
                            if(isset(Yii::$app->request->queryParams['group'])) {
                                if (Yii::$app->request->queryParams['group'] == 'average') {

                                    if ($this->client_id == 154) {
                                        $query->andFilterWhere(['client_id' => $this->client_id]);
                                    } else {
                                        $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                                    }

                                } else {
                                    $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                                }
                            } else {
                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                            }

                        }

                    }

                } else {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }
                $query->orderBy('client.parent_id, client_id');
                break;

            case self::SCENARIO_CAR:
                $query->joinWith([
                    'type',
                    'mark',
                    'client client',
                ]);
                if ($this->dateFrom) {
                    $query->andFilterWhere(['between', 'served_at', strtotime($this->dateFrom), strtotime($this->dateTo)]);
                }
                if (!empty($this->client->children)) {

                    if (!isset(Yii::$app->request->queryParams['ActSearch']['client_id'])) {

                        if ($this->client_id == 59) {
                            $queryPar = Company::find()->where(['parent_id' => $this->client_id])->all();

                            $arrParParIds = [];

                            for ($i = 0; $i < count($queryPar); $i++) {
                                $arrParParIds[] = $queryPar[$i]['id'];

                                $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                                for ($j = 0; $j < count($queryParPar); $j++) {
                                    $arrParParIds[] = $queryParPar[$j]['id'];
                                }

                            }

                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id], ['client_id' => $arrParParIds]]);

                        } else {
                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                        }

                    } else {

                        if ((Yii::$app->request->queryParams['ActSearch']['client_id'] == '') || (Yii::$app->request->queryParams['ActSearch']['client_id'] <= 0)) {

                            if ($this->client_id == 59) {
                                $queryPar = Company::find()->where(['parent_id' => $this->client_id])->all();

                                $arrParParIds = [];

                                for ($i = 0; $i < count($queryPar); $i++) {
                                    $arrParParIds[] = $queryPar[$i]['id'];

                                    $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                                    for ($j = 0; $j < count($queryParPar); $j++) {
                                        $arrParParIds[] = $queryParPar[$j]['id'];
                                    }

                                }

                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id], ['client_id' => $arrParParIds]]);

                            } else {
                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                            }

                        } else {
                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                        }

                    }

                } else {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }
                $query->orderBy('parent_id, client_id, served_at DESC');
                break;

            default:
                $query->joinWith([
                    'type',
                    'mark',
                    'card',
                    'partner',
                ]);
                $query->andFilterWhere([
                    'client_id' => $this->client_id,
                    'partner_id' => $this->partner_id,
                ]);

                $query->orderBy('parent_id, partner_id, served_at');
        }

        // grid filtering conditions
        $query->alias('act');
        $query->andFilterWhere([
            'id' => $this->id,
            'card_id' => $this->card_id,
            'act.type_id' => $this->type_id,
            'act.mark_id' => $this->mark_id,
            'partner.address' => $this->address,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'service_type' => $this->service_type,
            'DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period,
            'DAY(FROM_UNIXTIME(`served_at`))' => $this->day,
            'DATE_FORMAT(FROM_UNIXTIME(`act`.`created_at`), "%Y-%m-%d")' => $this->createDay,
        ]);

        $query->andFilterWhere(['like', 'act.car_number', $this->car_number])
            ->andFilterWhere(['like', 'act.extra_car_number', $this->extra_car_number])
            ->andFilterWhere(['like', 'check', $this->check]);

        $dataProvider->setSort([
            'attributes' => [
                'card_number' => [
                    'label' => 'Карта',
                    'default' => SORT_ASC
                ],
            ]
        ]);

        return $dataProvider;
    }

    /**
     * @return ActiveDataProvider
     */
    public function searchClient()
    {
        $query = Act::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $query->alias('act');
        $query->andFilterWhere([
            'id' => $this->id,
            'card_id' => $this->card_id,
            'act.type_id' => $this->type_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'service_type' => $this->service_type,
            'DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period,
            'DAY(FROM_UNIXTIME(`served_at`))' => $this->day,
            'DATE_FORMAT(FROM_UNIXTIME(`act`.`created_at`), "%Y-%m-%d")' => $this->createDay,
        ]);
        $query->joinWith('client');
        $query->groupBy('client_id');

        return $dataProvider;
    }

    /**
     * @return ActiveDataProvider
     */
    public function searchPartner()
    {
        $query = Act::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);


        $query->alias('act');
        $query->andFilterWhere([
            'id' => $this->id,
            'card_id' => $this->card_id,
            'act.type_id' => $this->type_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'service_type' => $this->service_type,
            'DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period,
            'DATE_FORMAT(FROM_UNIXTIME(`act`.`created_at`), "%Y-%m-%d")' => $this->createDay,
        ]);
        $query->joinWith('partner');
        $query->groupBy('partner_id');

        return $dataProvider;
    }
}
