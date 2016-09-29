<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Entry;

/**
 * EntrySearch represents the model behind the search form about `common\models\Entry`.
 */
class EntrySearch extends Entry
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'type_id', 'card_id', 'mark_id', 'service_type', 'status', 'created_at', 'updated_at', 'start_at', 'end_at'], 'integer'],
            [['day', 'number', 'extra_number'], 'safe'],
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
            'DAY(FROM_UNIXTIME(`start_at`))' => $this->day,
        ]);

        $query->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 'extra_number', $this->extra_number]);

        return $dataProvider;
    }
}
