<?php

namespace common\models\search;

use common\models\Service;
use yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Act;

/**
 * CardSearch represents the model behind the search form about `common\models\Card`.
 * @property string $period
 * @property integer $day
 */
class ActSearch extends Act
{
    public $dateFrom;
    public $dateTo;
    public $period;
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
            self::SCENARIO_CLIENT => ['client_id', 'card_id', 'mark_id', 'type_id', 'day', 'number', 'extra_number', 'period'],
            self::SCENARIO_PARTNER => ['partner_id', 'card_id', 'mark_id', 'type_id', 'day', 'number', 'extra_number', 'period'],
            self::SCENARIO_ERROR => ['service_type', 'client_id', 'partner_id', 'card_id', 'mark_id', 'type_id', 'number', 'extra_number'],
            self::SCENARIO_HISTORY => ['number', 'dateFrom', 'dateTo'],
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
                    'card as card',
                    'client as client',
                    'partner as partner',
                    'car as car',
                ]);

                $query->orFilterWhere(['income' => 0]);
                $query->orFilterWhere(['expense' => 0]);
                $query->orFilterWhere(['client_id' => 0]);
                $query->orFilterWhere(['partner_id' => 0]);
                if ($this->service_type == Service::TYPE_WASH) {
                    $query->orFilterWhere(['check' => null]);
                }
                if ($this->service_type != Service::TYPE_DISINFECT) {
                    $query->orWhere('car.company_id != card.company_id');
                }
                $query->orFilterWhere(['car.company_id' => null]);
                $query->andFilterWhere(['!=', 'act.status', Act::STATUS_FIXED]);
                
                $query->orderBy('partner_id, served_at');
                break;

            case self::SCENARIO_CLIENT:
                $query->joinWith([
                    'type',
                    'mark',
                    'card',
                    'client',
                    'clientScopes',
                ]);
                $query->orderBy('parent_id, act.client_id, served_at');
                break;

            case self::SCENARIO_PARTNER:
                $query->joinWith([
                    'type',
                    'mark',
                    'card',
                    'partner',
                    'partnerScopes',
                ]);
                $query->orderBy('parent_id, act.partner_id, served_at');
                break;

            case self::SCENARIO_HISTORY:
                $query->joinWith([
                    'type',
                    'mark',
                    'client',
                ]);
                if ($this->dateFrom) {
                    $query->andFilterWhere(['between', 'served_at', strtotime($this->dateFrom), strtotime($this->dateTo)]);
                }
                $query->orderBy('parent_id, client_id, actsCount DESC');
                break;

            case self::SCENARIO_CAR:
                $query->joinWith([
                    'type',
                    'mark',
                    'client',
                ]);
                if ($this->dateFrom) {
                    $query->andFilterWhere(['between', 'served_at', strtotime($this->dateFrom), strtotime($this->dateTo)]);
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

                $query->orderBy('parent_id, partner_id, served_at');
        }
        
        // grid filtering conditions
        $query->alias('act');
        $query->andFilterWhere([
            'id' => $this->id,
            'card_id' => $this->card_id,
            'client_id' => $this->client_id,
            'partner_id' => $this->partner_id,
            'act.number' => $this->number,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'service_type' => $this->service_type,
            'DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period,
            'DAY(FROM_UNIXTIME(`served_at`))' => $this->day,
        ]);

        return $dataProvider;
    }
}
