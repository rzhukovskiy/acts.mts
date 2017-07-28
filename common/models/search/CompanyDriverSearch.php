<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CompanyDriver;
use common\models\Company;

/**
 * CompanyDriverSearch represents the model behind the search form about `common\models\CompanyDriver`.
 */
class CompanyDriverSearch extends CompanyDriver
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id'], 'integer'],
            [['phone', 'name', 'number'], 'safe'],
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
        $query = CompanyDriver::find()->with('car');

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

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'company_id' => $this->company_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'phone', $this->phone]);

        $query->orderBy('car_id ASC');

        return $dataProvider;
    }

    public function searchClient($params)
    {
        $query = CompanyDriver::find()->with('car');

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

        // grid filtering conditions

        $queryPar = Company::find()->where(['parent_id' => $this->company_id])->all();

        $arrParParIds = [];

        for ($i = 0; $i < count($queryPar); $i++) {
            $arrParParIds[] = $queryPar[$i]['id'];

            $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

            for ($j = 0; $j < count($queryParPar); $j++) {
                $arrParParIds[] = $queryParPar[$j]['id'];
            }

        }

        if (count($arrParParIds) > 0) {
            $query->andFilterWhere(['OR', ['company_id' => $this->company_id], ['company_id' => $arrParParIds]]);
        } else {
            $query->andFilterWhere([
                'id' => $this->id,
                'company_id' => $this->company_id,
            ]);
        }

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'phone', $this->phone]);

        $query->orderBy('company_id ASC, car_id ASC');

        return $dataProvider;
    }

}
