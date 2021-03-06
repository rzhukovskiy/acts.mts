<?php

namespace common\models\search;

use common\models\Act;
use common\models\Service;
use Yii;
use yii\data\ActiveDataProvider;
use common\models\Car;

/**
 * CarSearch represents the model behind the search form about `common\models\Car`.
 * @property string $period
 * @property string $periodex
 * @property string $periodel
 * @property string $company_del
 */
class CarSearch extends Car
{
    public $period;
    public $periodex;
    public $periodel;
    public $company_del;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'company_del', 'mark_id', 'type_id', 'is_infected'], 'integer'],
            [['number', 'period', 'periodex', 'periodel'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['company_id', 'number','mark_id', 'type_id'],
            self::SCENARIO_INFECTED => ['company_id', 'company_del', 'period', 'periodex', 'periodel'],
            self::SCENARIO_OWNER => ['company_id', 'number','mark_id', 'type_id'],
            'default' => ['company_id', 'number','mark_id', 'type_id'],
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
        $query->alias('car');
        $query->andFilterWhere([
            'car.id' => $this->id,
            'car.mark_id' => $this->mark_id,
            'car.type_id' => $this->type_id,
            'car.is_infected' => $this->is_infected,
        ]);

        if ($this->scenario != self::SCENARIO_OWNER) {
            $query->andFilterWhere(['or',['company_id'=>$this->company_id],['company.parent_id' => $this->company_id]]);
        } else {
            $query->andFilterWhere(['company_id'=>$this->company_id]);
        }

        $query->andFilterWhere(['like', 'number', $this->number]);

        return $dataProvider;
    }
}
