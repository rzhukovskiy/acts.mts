<?php

namespace common\models\search;

use common\models\TenderControl;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 *  TenderControlSearch model
 * @package common\models
 * @property integer $user_id
 * @property integer $site_address
 * @property integer $type_payment
 * @property integer $is_archive
 * @property float $send
 * @property float $tender_return
 * @property float $balance_work
 * @property string $date_send
 * @property string $date_enlistment
 * @property string $money_unblocking
 * @property string $date_return
 * @property string $customer
 * @property string $purchase
 */
class TenderControlSearch extends TenderControl
{
    public $dateFrom;
    public $dateTo;
    public $period;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'site_address', 'type_payment', 'is_archive'], 'integer'],
            [['send', 'tender_return'], 'safe'],
            [['date_send', 'date_enlistment', 'money_unblocking', 'date_return', 'customer', 'purchase', 'site_address'], 'string'],
            [['dateFrom', 'dateTo', 'period'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return [
            'all' => ['user_id', 'site_address', 'type_payment', 'is_archive', 'send', 'tender_return', 'date_send', 'date_enlistment', 'money_unblocking', 'date_return', 'customer', 'purchase', 'site_address', 'dateFrom', 'dateTo', 'period'],
            'statprice' => ['user_id', 'site_address', 'type_payment', 'is_archive', 'send', 'tender_return', 'date_send', 'date_enlistment', 'money_unblocking', 'date_return', 'customer', 'purchase', 'dateFrom', 'dateTo', 'period'],
            'default' => ['user_id', 'site_address', 'type_payment', 'is_archive', 'send', 'tender_return', 'date_send', 'date_enlistment', 'money_unblocking', 'date_return', 'customer', 'purchase', 'dateFrom', 'dateTo', 'period'],
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
        $query = TenderControl::find();

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

        switch ($this->scenario) {
            case 'all':

                if (isset($this->is_archive)) {
                    if ($this->is_archive == 1) {

                    } else {
                        $this->is_archive = 0;
                    }
                } else {
                    $this->is_archive = 0;
                }

                // grid filtering conditions
                $query->andFilterWhere([
                    'user_id' => $this->user_id,
                    'type_payment' => $this->type_payment,
                    'is_archive' => $this->is_archive,
                    'site_address' => $this->site_address,
                ]);

                // Если период не задан то задаем 10 лет.
                if ((!isset($this->dateFrom)) && (!isset($this->dateTo))) {
                    $this->dateFrom = (((int) date('Y', time())) - 10) . '-12-31T21:00:00.000Z';
                    $this->dateTo = (((int) date('Y', time())) + 1) . '-12-31T21:00:00.000Z';
                    $query->andWhere(['between', "DATE(FROM_UNIXTIME(date_send))", $this->dateFrom, $this->dateTo]);

                } else {
                    $query->andWhere(['between', "DATE(FROM_UNIXTIME(date_send))", $this->dateFrom, $this->dateTo]);
                }

                $query->andFilterWhere(['like', 'send', $this->send])
                    ->andFilterWhere(['like', 'customer', $this->customer])
                    ->andFilterWhere(['like', 'purchase', $this->purchase])
                    ->andFilterWhere(['like', 'tender_return', $this->tender_return]);

                break;

            case 'statprice':

                // Если период не задан то задаем 10 лет.
                if ((!isset($this->dateFrom)) && (!isset($this->dateTo))) {
                    $this->dateFrom = (((int) date('Y', time())) - 10) . '-12-31T21:00:00.000Z';
                    $this->dateTo = (((int) date('Y', time())) + 1) . '-12-31T21:00:00.000Z';
                    $query->andWhere(['between', "DATE(FROM_UNIXTIME(date_send))", $this->dateFrom, $this->dateTo]);

                } else {
                    $query->andWhere(['between', "DATE(FROM_UNIXTIME(date_send))", $this->dateFrom, $this->dateTo]);
                }

                break;

        }

        return $dataProvider;
    }
}
