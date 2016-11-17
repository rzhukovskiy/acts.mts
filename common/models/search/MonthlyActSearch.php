<?php

namespace common\models\search;

use common\components\ArrayHelper;
use common\models\Company;
use common\models\MonthlyAct;
use yii;
use yii\data\ActiveDataProvider;

/**
 * ActSearch represents the model behind the search form about `common\models\MonthlyAct`.
 * @property string $period
 * @property integer $day
 */
class MonthlyActSearch extends MonthlyAct
{
    public $dateFrom;
    public $dateTo;
    public $period;
    public $createDay;
    public $dateMonth;
    public $day;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'type_id'], 'integer'],
            [['act_date'], 'string'],
            ['act_date', 'default', 'value' => date('n-Y', strtotime('-1 month'))],
            [['dateFrom', 'dateTo', 'dateMonth'], 'safe'],
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
        $query = MonthlyAct::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => false,
            'sort'       => [
                'defaultOrder' => ['client_id' => SORT_DESC],
            ],
        ]);

        $this->load($params);
        $company = ArrayHelper::getValue($params, 'company', 0);
        $this->is_partner = ($company == 0) ? 1 : 0;

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        // grid filtering conditions
        $query->andFilterWhere([
            'id'                               => $this->id,
            'client_id'                        => $this->client_id,
            'type_id'                          => $this->type_id,
            'DATE_FORMAT(`act_date`, "%c-%Y")' => $this->act_date,
            'is_partner'                       => $this->is_partner,
            'created_at'                       => $this->created_at,
            'updated_at'                       => $this->updated_at,
        ]);

        return $dataProvider;
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchArchive($params)
    {
        $query = static::find();
        $query->addSelect([
            'DATE_FORMAT(`act_date`, "%c-%Y") as dateMonth',
            'act_date',
            'type_id',
            'client_id',
            'service_id',
            'profit',
            'number'
        ])->with('client');


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if (!$this->client_id) {
            $query->groupBy(['client_id']);
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'client_id'  => $this->client_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
        if ($this->type_id == Company::TYPE_OWNER) {
            $query->andFilterWhere(['is_partner' => MonthlyAct::NOT_PARTNER]);
        } else {
            $query->andFilterWhere(['is_partner' => MonthlyAct::PARTNER, 'type_id' => $this->type_id,]);
        }

        $query->andFilterWhere(['between', "act_date", $this->dateFrom, $this->dateTo]);
        $query->orderBy(['type_id' => SORT_ASC, 'act_date' => SORT_DESC]);

        return $dataProvider;
    }
}
