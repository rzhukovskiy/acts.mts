<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CompanyInfo;

/**
 * CompanyInfoSearch represents the model behind the search form about `common\models\CompanyInfo`.
 */
class CompanyInfoSearch extends CompanyInfo
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id'], 'integer'],
            [['phone', 'address', 'address_mail', 'email'], 'safe'],
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
        $query = CompanyInfo::find();

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
            'company_id' => $this->company_id,
        ]);

        $query->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'address_mail', $this->address_mail])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
}
