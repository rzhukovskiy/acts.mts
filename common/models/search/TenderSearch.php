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
    public $work_user_id;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date_request_end', 'time_bidding_end', 'customer', 'city'], 'string'],
            [['purchase_status', 'method_purchase', 'work_user_id'], 'integer'],
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
        return [
            'tender' => ['date_request_end', 'time_bidding_end', 'customer', 'city', 'purchase_status', 'method_purchase', 'user_id', 'service_type', 'price_nds', 'company_id', 'dateFrom', 'dateTo', 'period'],
            'tenderlist' => ['date_request_end', 'time_bidding_end', 'customer', 'city', 'purchase_status', 'method_purchase', 'user_id', 'service_type', 'price_nds', 'company_id', 'dateFrom', 'dateTo', 'period'],
            'statplace' => ['date_request_end', 'time_bidding_end', 'customer', 'city', 'purchase_status', 'method_purchase', 'user_id', 'service_type', 'price_nds', 'company_id', 'dateFrom', 'dateTo', 'period'],
            'statwintender' => ['date_request_end', 'time_bidding_end', 'customer', 'city', 'purchase_status', 'method_purchase', 'user_id', 'service_type', 'price_nds', 'company_id', 'dateFrom', 'dateTo', 'period'],
            'activity' => ['dateFrom', 'dateTo', 'service_type', 'period', 'work_user_id'],
            'default' => ['date_request_end', 'time_bidding_end', 'customer', 'city', 'purchase_status', 'method_purchase', 'user_id', 'service_type', 'price_nds', 'company_id', 'dateFrom', 'dateTo', 'period'],
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

        switch ($this->scenario) {
            case 'tender':

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

                // ???????? ???????????? ???? ?????????? ???? ???????????? 10 ??????. ?????????????? ???????? ???? ?????????? ?????????????? ?? ?????????????? ????????????, ?? ???????? ?????????? ???? ?????????????? ???????????????? ?????? ????????????
                if (((!isset($this->dateFrom)) && (!isset($this->dateTo))) || ((strtotime($this->dateTo) - strtotime($this->dateFrom)) > 157680000)) {
                    $this->dateFrom = (((int) date('Y', time())) - 10) . '-12-31T21:00:00.000Z';
                    $this->dateTo = (((int) date('Y', time())) + 1) . '-12-31T21:00:00.000Z';
                    $query->andWhere(['OR', ['between', "DATE(FROM_UNIXTIME(date_request_end))", $this->dateFrom, $this->dateTo], ['is', 'date_request_end', null], ['date_request_end' => '']]);

                } else {
                    $query->andWhere(['between', "DATE(FROM_UNIXTIME(date_request_end))", $this->dateFrom, $this->dateTo]);
                }

                $query->orderby('company.id');
                break;

            case 'tenderlist':

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

                // ???????? ???????????? ???? ?????????? ???? ???????????? 10 ??????. ?????????????? ???????? ???? ?????????? ?????????????? ?? ?????????????? ????????????, ?? ???????? ?????????? ???? ?????????????? ???????????????? ?????? ????????????
                if (((!isset($this->dateFrom)) && (!isset($this->dateTo))) || ((strtotime($this->dateTo) - strtotime($this->dateFrom)) > 157680000)) {
                    $this->dateFrom = (((int) date('Y', time())) - 10) . '-12-31T21:00:00.000Z';
                    $this->dateTo = (((int) date('Y', time())) + 1) . '-12-31T21:00:00.000Z';
                    $query->andWhere(['OR', ['between', "DATE(FROM_UNIXTIME(date_request_end))", $this->dateFrom, $this->dateTo], ['is', 'date_request_end', null], ['date_request_end' => '']]);

                } else {
                    $query->andWhere(['between', "DATE(FROM_UNIXTIME(date_request_end))", $this->dateFrom, $this->dateTo]);
                }

                // ???????????????? ???????????????? ?? ?????????????????? ???????????????? ????????????????????????
                if (isset($params['TenderSearch']['user_id'])) {
                    if ($params['TenderSearch']['user_id'] > 0) {
                        $this->user_id = $params['TenderSearch']['user_id'];
                        $query->andWhere(['user_id' => $params['TenderSearch']['user_id']]);
                    }
                } else {
                    $exists = Tender::find()->where(['user_id' => Yii::$app->user->identity->id])->andWhere(['OR', ['purchase_status' => 15], ['purchase_status' => 18], ['purchase_status' => 19], ['purchase_status' => 57], ['purchase_status' => 58], ['purchase_status' => 85]])->exists();
                    if (Yii::$app->user->identity->role != User::ROLE_ADMIN && ($exists)) {
                        $this->user_id = Yii::$app->user->identity->id;
                        $query->andWhere(['user_id' => Yii::$app->user->identity->id]);
                    }
                }

                $query->orderby('company.id');
                break;

            case 'activity':

                $query->innerJoin('department_user', '`department_user`.`user_id` = `tender`.`work_user_id`')->andWhere(['department_id' => $this->service_type]);
                $query->andWhere(['between', "DATE(FROM_UNIXTIME(work_user_time))", $this->dateFrom, $this->dateTo]);

                if($this->work_user_id) {
                    $query->andWhere(['`tender`.`work_user_id`' => $this->work_user_id]);
                    $query->groupBy('`tender`.`id`');
                    $query->orderBy('`tender`.`work_user_time`');
                } else {
                    $query->groupBy('`tender`.`work_user_id`');
                    $query->orderBy('COUNT(Distinct `tender`.`id`) DESC');
                }

                $query->select('`tender`.`id` as `id`, `tender`.`customer` as `customer`, `tender`.`work_user_time` as `work_user_time`, `tender`.`work_user_id`, COUNT(Distinct `tender`.`id`) as `created_at`');

                break;

            case 'statplace':

                // ???????? ???????????? ???? ?????????? ???? ???????????? 10 ??????.
                if ((!isset($this->dateFrom)) && (!isset($this->dateTo))) {
                    $this->dateFrom = (((int) date('Y', time())) - 10) . '-12-31T21:00:00.000Z';
                    $this->dateTo = (((int) date('Y', time())) + 1) . '-12-31T21:00:00.000Z';
                    $query->andWhere(['OR', ['between', "DATE(FROM_UNIXTIME(date_request_end))", $this->dateFrom, $this->dateTo], ['is', 'date_request_end', null], ['date_request_end' => '']]);

                } else {
                    $query->andWhere(['between', "DATE(FROM_UNIXTIME(date_request_end))", $this->dateFrom, $this->dateTo]);
                }

                break;

            case 'statwintender':

                // ???????? ???????????? ???? ?????????? ???? ???????????? 10 ??????.
                if ((!isset($this->dateFrom)) && (!isset($this->dateTo))) {
                    $this->dateFrom = (((int) date('Y', time())) - 10) . '-12-31T21:00:00.000Z';
                    $this->dateTo = (((int) date('Y', time())) + 1) . '-12-31T21:00:00.000Z';
                    $query->andWhere(['OR', ['between', "DATE(FROM_UNIXTIME(date_request_end))", $this->dateFrom, $this->dateTo], ['is', 'date_request_end', null], ['date_request_end' => '']]);

                } else {
                    $query->andWhere(['between', "DATE(FROM_UNIXTIME(date_request_end))", $this->dateFrom, $this->dateTo]);
                }

                break;

        }

        return $dataProvider;
    }
}
