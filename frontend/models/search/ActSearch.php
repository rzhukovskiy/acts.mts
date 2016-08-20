<?php

namespace frontend\models\search;

use frontend\models\Act;
use Yii;
use yii\data\ActiveDataProvider;


/**
 * ActSearch represents the model behind the search form about `common\models\Act`.
 */
class ActSearch extends Act
{
    public $dateFrom;
    public $dateTo;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['dateFrom', 'dateTo'], 'safe'],
        ];

        return array_merge(parent::rules(), $rules);
    }

    public function scenarios()
    {
        $scenarios = [
            'search_by_date' => ['dateFrom', 'dateTo'],
        ];

        return array_merge(parent::scenarios(), $scenarios);
    }

    /**
     * Test:
     * SELECT count(a.id), count(a.expense), count(a.profit), c.name
     * FROM `act` a
     * LEFT JOIN company c ON a.partner_id = c.id
     * WHERE DATE(FROM_UNIXTIME(`served_at`))
     * BETWEEN '2016-01-01' AND '2016-01-20'
     * GROUP BY type_id;
     *
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchByDate($params)
    {
        $query = static::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);

        if (!$this->validate())
            return $dataProvider;

        $query->andFilterWhere(['between', "DATE(FROM_UNIXTIME(`served_at`))", $this->dateFrom, $this->dateTo]);

        return $dataProvider;
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
        $query = static::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'partner_id' => $this->partner_id,
            'client_id' => $this->client_id,
            'type_id' => $this->type_id,
            'card_id' => $this->card_id,
            'mark_id' => $this->mark_id,
            'expense' => $this->expense,
            'income' => $this->income,
            'profit' => $this->profit,
            'service_type' => $this->service_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'served_at' => $this->served_at,
        ]);

        $query->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 'extra_number', $this->extra_number])
            ->andFilterWhere(['like', 'check', $this->check]);

        return $dataProvider;
    }
}
