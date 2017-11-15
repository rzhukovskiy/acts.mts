<?php

namespace common\models\search;

use common\models\Card;
use common\models\Company;
use common\models\Tender;
use common\models\User;
use yii;
use yii\data\ActiveDataProvider;
use yii\base\Model;

/**
 * CardSearch represents the model behind the search form about `common\models\Card`.
 */
class TenderSearch extends Company
{
    public $purchase_status;
    public $user_id;
    public $date_request_end;
    public $time_bidding_end;
    public $customer;
    public $method_purchase;
    public $city;
    public $service_type;
    public $price_nds;
    public $company_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date_request_end', 'time_bidding_end', 'customer', 'city'], 'string'],
            [['purchase_status', 'method_purchase'], 'integer'],
            [['user_id', 'service_type', 'price_nds'], 'safe'],
            [['company_id'], 'integer'],
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
        $query = Tender::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            $query->joinWith(['company']);
            return $dataProvider;
        }


        // grid filtering conditions
        $query->andFilterWhere([
            'company_id' => $this->company_id,
            'purchase_status' => $this->purchase_status,
            'user_id' => $this->user_id,
            'date_request_end' => $this->date_request_end,
            'time_bidding_end' => $this->time_bidding_end,
            'customer' => $this->customer,
            'method_purchase' => $this->method_purchase,
            'city' => $this->city,
            'service_type' => $this->service_type,
            'price_nds' => $this->price_nds,

        ]);

        return $dataProvider;
    }
}
