<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Car;

/**
 * CarSearch represents the model behind the search form about `common\models\Car`.
 */
class CarSearch extends Car
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'mark_id', 'type_id', 'is_infected'], 'integer'],
            [['number'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['company_id', 'number'],
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
        $query = Car::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $this->load($params);

        //для не админа жестко задаем company_id
        if (!empty(Yii::$app->user->identity->company_id) && !$this->company_id) {
            $this->company_id = Yii::$app->user->identity->company->id;
        }

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->joinWith([
            'company company',
        ]);
        $query->andFilterWhere([
            'id' => $this->id,
            'mark_id' => $this->mark_id,
            'type_id' => $this->type_id,
            'is_infected' => $this->is_infected,
        ]);
        $query->andFilterWhere(['company.parent_id' => $this->company_id])->orFilterWhere(['company_id' => $this->company_id]);

        $query->andFilterWhere(['like', 'number', $this->number]);

        return $dataProvider;
    }
}
