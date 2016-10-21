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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['partner_id', 'card_id', 'mark_id', 'type_id', 'day'], 'integer'],
            [['number', 'extra_number', 'period'], 'string'],
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
            self::SCENARIO_CAR => ['card_id', 'number', 'dateFrom', 'dateTo'],
            self::SCENARIO_CLIENT => ['check', 'client_id', 'card_id', 'mark_id', 'type_id', 'day', 'number', 'extra_number', 'period'],
            self::SCENARIO_PARTNER => ['check', 'partner_id', 'card_id', 'mark_id', 'type_id', 'day', 'number', 'extra_number', 'period'],
            self::SCENARIO_ERROR => ['check', 'service_type', 'client_id', 'partner_id', 'card_id', 'mark_id', 'type_id', 'number', 'extra_number'],
            self::SCENARIO_HISTORY => ['client_id', 'number', 'dateFrom', 'dateTo'],
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
                ]);

                $query->orWhere(['income' => 0]);
                $query->orWhere(['expense' => 0]);
                $query->orWhere(['client_id' => 0]);
                $query->orWhere(['partner_id' => 0]);
                if ($this->service_type == Service::TYPE_WASH) {
                    $query->orWhere(['check' => null]);
                    $query->orWhere(['check' => '']);
                }
                if ($this->service_type != Service::TYPE_DISINFECT) {
                    $query->orWhere('car.company_id != card.company_id');
                    $query->orWhere(['card.company_id' => null]);
                }
                $query->orWhere(['car.company_id' => null]);
                $query->andFilterWhere(['client_id' => $this->client_id,]);
                $query->andFilterWhere(['partner_id' => $this->partner_id,]);
                $query->andFilterWhere(['!=', 'act.status', Act::STATUS_FIXED]);

                $query->orderBy('partner_id, served_at');
                break;

            case self::SCENARIO_CLIENT:
                $query->joinWith([
                    'type',
                    'mark',
                    'card',
                    'car',
                    'partner partner',
                    'client client',
                    'clientScopes',
                ]);
                if (!empty($this->client->children)) {
                    $query->andFilterWhere(['client.parent_id' => $this->client_id])->orFilterWhere(['client_id' => $this->client_id]);
                } else {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }
                $query->orderBy('client.parent_id, act.client_id, served_at');
                break;

            case self::SCENARIO_PARTNER:
                $query->joinWith([
                    'type',
                    'mark',
                    'card',
                    'car',
                    'partner partner',
                    'client client',
                    'partnerScopes',
                ]);
                if (!empty($this->client->children)) {
                    $query->andFilterWhere(['partner.parent_id' => $this->client_id])->orFilterWhere(['partner_id' => $this->partner_id]);
                } else {
                    $query->andFilterWhere(['partner_id' => $this->partner_id]);
                }
                $query->orderBy('partner.parent_id, act.partner_id, served_at');
                break;

            case self::SCENARIO_HISTORY:
                $query->joinWith([
                    'type',
                    'mark',
                    'client client',
                    'car car',
                ]);
                if ($this->dateFrom) {
                    $query->andFilterWhere(['between', 'served_at', strtotime($this->dateFrom), strtotime($this->dateTo)]);
                }
                if (!empty($this->client->children)) {
                    $query->andFilterWhere(['client.parent_id' => $this->client_id])->orFilterWhere(['client_id' => $this->client_id]);
                } else {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }
                $query->orderBy('parent_id, client_id, actsCount DESC');
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
                    $query->andFilterWhere(['client.parent_id' => $this->client_id])->orFilterWhere(['client_id' => $this->client_id]);
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
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'service_type' => $this->service_type,
            'DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period,
            'DAY(FROM_UNIXTIME(`served_at`))' => $this->day,
            'DATE_FORMAT(FROM_UNIXTIME(`act`.`created_at`), "%Y-%m-%d")' => $this->createDay,
        ]);

        $query->andFilterWhere(['like', 'act.number', $this->number])
            ->andFilterWhere(['like', 'act.extra_number', $this->extra_number])
            ->andFilterWhere(['like', 'check', $this->check]);


        return $dataProvider;
    }
}
