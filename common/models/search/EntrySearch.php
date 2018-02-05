<?php

namespace common\models\search;

use common\models\Entry;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * EntrySearch represents the model behind the search form about `common\models\Entry`.
 */
class EntrySearch extends Entry
{

    public $dateFrom;
    public $dateTo;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'id',
                    'company_id',
                    'type_id',
                    'card_id',
                    'mark_id',
                    'service_type',
                    'status',
                    'created_at',
                    'updated_at',
                    'start_at',
                    'end_at',
                    'user_id'
                ],
                'integer'
            ],
            [['dateFrom', 'dateTo', 'period', 'number', 'extra_number'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
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
        $query = Entry::find();

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

        $query->orderBy('start_at');

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'company_id' => $this->company_id,
            'type_id' => $this->type_id,
            'card_id' => $this->card_id,
            'mark_id' => $this->mark_id,
            'service_type' => $this->service_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            //'DATE_FORMAT(FROM_UNIXTIME(start_at), "%d-%m-%Y")' => $this->day, старый поиск по дню
        ]);

        // Если период не задан то задаем сегодняшний день
        if ((!isset($this->dateFrom)) && (!isset($this->dateTo))) {
            $this->dateFrom = date('Y-m-d') . 'T00:00:00.000Z';
            $this->dateTo = date('Y-m-d') . 'T23:59:59.000Z';
            $query->andWhere(['between', "DATE(FROM_UNIXTIME(created_at))", $this->dateFrom, $this->dateTo]);
        } else {
            $query->andWhere(['between', "DATE(FROM_UNIXTIME(created_at))", $this->dateFrom, $this->dateTo]);
        }
        // Если период не задан то задаем сегодняшний день

        $query->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 'extra_number', $this->extra_number]);

        return $dataProvider;
    }
}
