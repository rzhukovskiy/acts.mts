<?php

namespace common\models\search;

use common\models\Company;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\CompanyMember;

/**
 * CompanyMemberSearch represents the model behind the search form about `common\models\CompanyMember`.
 */
class CompanyMemberSearch extends CompanyMember
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id'], 'integer'],
            [['position', 'phone', 'email', 'name'], 'safe'],
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
        $query = CompanyMember::find();

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

        $query->andFilterWhere(['like', 'position', $this->position])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }

    //Вывод дочерних сотрудников
    public function searchMemberlist($params)
    {
        $query = CompanyMember::find();

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

        $queryPar = Company::find()->where(['parent_id' => $this->company_id])->all();

        $arrParParIds = [];

        for ($i = 0; $i < count($queryPar); $i++) {
            $arrParParIds[] = $queryPar[$i]['id'];

            $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

            for ($j = 0; $j < count($queryParPar); $j++) {
                $arrParParIds[] = $queryParPar[$j]['id'];
            }

        }

        $query->where(['OR', ['company_id' => $this->company_id], ['company_id' => $arrParParIds]]);

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id]);

        $query->andFilterWhere(['like', 'position', $this->position])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['show_member' => 1]);


        return $dataProvider;
    }

}
