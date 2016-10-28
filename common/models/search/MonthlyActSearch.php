<?php

namespace common\models\search;

use common\components\ArrayHelper;
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
}
