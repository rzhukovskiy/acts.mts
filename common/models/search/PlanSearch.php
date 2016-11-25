<?php

namespace common\models\search;

use common\models\MonthlyAct;
use common\models\Plan;
use yii;
use yii\data\ActiveDataProvider;

/**
 * PlanSearch represents the model behind the search form about `common\models\Plan`.
 * @property string $period
 * @property integer $day
 */
class PlanSearch extends Plan
{

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Plan::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => false,
            'sort'       => [
                'defaultOrder' => ['status' => SORT_ASC],
            ],
        ]);

        $this->load($params);

        // grid filtering conditions
        $query->andFilterWhere([
            'user_id' => $this->user_id
        ]);

        return $dataProvider;
    }

}
