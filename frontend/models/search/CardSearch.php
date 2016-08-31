<?php

namespace frontend\models\search;

use common\models\Company;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Card;
use common\models\search\CardSearch as CommonCardSearch;

/**
 * CardSearch represents the model behind the search form about `common\models\Card`.
 */
class CardSearch extends CommonCardSearch
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'number', 'status', 'created_at', 'updated_at'], 'integer'],
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
        $query = Card::find()->orderBy('company_id');

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

        $query->joinWith(['company company']);
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
        $query->andFilterWhere(['company.status' => Company::STATUS_ACTIVE]);
        $query->andFilterWhere(['parent_id' => $this->company_id])->orFilterWhere(['company_id' => $this->company_id]);

        return $dataProvider;
    }
}
