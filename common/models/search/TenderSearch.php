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
    public $dateFrom;
    public $dateTo;
    public $period;
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
            [['dateFrom', 'dateTo', 'period'], 'safe'],
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
            'pagination' => false,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->joinWith(['company']);

        // grid filtering conditions
        $query->andFilterWhere([
            'company_id' => $this->company_id,
            'purchase_status' => $this->purchase_status,
            'user_id' => $this->user_id,
            'method_purchase' => $this->method_purchase,
            'service_type' => $this->service_type,

        ]);
        $query->andFilterWhere(['like', 'city', $this->city])
              ->andFilterWhere(['like', 'price_nds', $this->price_nds]);

        // Если период не задан то задаем 10 лет. Выводим если не задан тендеры с пустыми датами, а если задан то которые попадают под фильтр
        if (((!isset($this->dateFrom)) && (!isset($this->dateTo))) || ((strtotime($this->dateTo) - strtotime($this->dateFrom)) > 157680000)) {
            $this->dateFrom = (((int) date('Y', time())) - 10) . '-12-31T21:00:00.000Z';
            $this->dateTo = (((int) date('Y', time())) + 1) . '-12-31T21:00:00.000Z';
            $query->andWhere(['OR', ['between', "DATE(FROM_UNIXTIME(date_request_end))", $this->dateFrom, $this->dateTo], ['is', 'date_request_end', null], ['date_request_end' => '']]);

        } else {
            $query->andWhere(['between', "DATE(FROM_UNIXTIME(date_request_end))", $this->dateFrom, $this->dateTo]);
        }

        $query->orderby('company.id');

        return $dataProvider;
    }
}
