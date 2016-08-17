<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Act;

/**
 * ActSearch represents the model behind the search form about `common\models\Act`.
 */
class ActSearch extends Act
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'partner_id', 'client_id', 'type_id', 'card_id', 'mark_id', 'expense', 'income', 'profit', 'service_type', 'status', 'created_at', 'updated_at', 'served_at'], 'integer'],
            [['number', 'extra_number', 'check'], 'safe'],
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
        $query = Act::find();

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
