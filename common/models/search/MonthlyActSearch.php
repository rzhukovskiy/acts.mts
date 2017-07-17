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
    public $payment_status;
    public $act_status;
    public $period;
    public $createDay;
    public $dateMonth;
    public $day;
    public $client_name;
    public $client_city;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'type_id'], 'integer'],
            [['act_date'], 'string'],
            ['act_date', 'default', 'value' => date('n-Y', strtotime('-1 month'))],
            [['dateFrom', 'dateTo', 'dateMonth', 'payment_status', 'client_name', 'client_city'], 'safe'],
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
            'sort'=>[
                'defaultOrder'=>[
                    'parent_key' => SORT_ASC
                ]
            ]
        ]);

        $sort = $dataProvider->getSort();
        $sort->attributes = array_merge($sort->attributes,
            [
                'parent_key' => [
                    'asc'  => ['parent_key' => SORT_ASC],
                    'desc' => ['parent_key' => SORT_DESC]
                ]
            ]);
        $dataProvider->setSort($sort);

        $this->load($params);
        $company = ArrayHelper::getValue($params, 'company', 0);
        $this->is_partner = ($company == 0) ? 1 : 0;

        $query->alias('company');
        $query->joinWith('client client')->addSelect([
                    'company.*',
                    new yii\db\Expression('IF(IFNULL(client.parent_id,0)=0, client.id*1000, client.parent_id*1000+client.id) as parent_key')
                ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        // grid filtering conditions
        $query->andFilterWhere([
            'company.id'                               => $this->id,
            'company.client_id'                        => $this->client_id,
            'company.type_id'                          => $this->type_id,
            'DATE_FORMAT(`act_date`, "%c-%Y")' => $this->act_date,
            'company.is_partner'                       => $this->is_partner,
            'company.created_at'                       => $this->created_at,
            'company.updated_at'                       => $this->updated_at,
        ]);

        $query->andFilterWhere(['company.act_status' => $this->act_status]);

        // фильтр оплачено
        if($this->payment_status == 15) {
            $query->andFilterWhere(['>', 'company.payment_status', 0]);
        } else {
            $query->andFilterWhere(['company.payment_status' => $this->payment_status]);
        }

        //
        $query->innerJoin('{{%act}}');
        $dataFilter = explode('-', $this->act_date);

        if($dataFilter[0] > 9) {
            $dataFilter = $dataFilter[1] . '-' . $dataFilter[0] . '-00';
        } else {
            $dataFilter = $dataFilter[1] . '-0' . $dataFilter[0] . '-00';
        }

        if ($this->is_partner == self::PARTNER) {
            $query->andWhere('company.client_id = act.partner_id AND (act.expense > 0) AND (act.service_type=' .$this->type_id . ') AND (date_format(FROM_UNIXTIME(act.served_at), \'%Y-%m-00\') = \'' . $dataFilter . '\')');
        } else {
            $query->andWhere('company.client_id = act.client_id AND (act.income > 0) AND (act.service_type=' .$this->type_id . ') AND (date_format(FROM_UNIXTIME(act.served_at), \'%Y-%m-00\') = \'' . $dataFilter . '\')');
        }
        //

        $query->andWhere('client.name LIKE "%' . $this->client_name . '%" ');
        $query->andWhere('client.address LIKE "%' . $this->client_city . '%" ');

        return $dataProvider;
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchArchive($params)
    {

        $query = MonthlyAct::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => false,
            'sort'=>[
                'defaultOrder'=>[
                    'parent_key' => SORT_ASC
                ]
            ]
        ]);

        $sort = $dataProvider->getSort();
        $sort->attributes = array_merge($sort->attributes,
            [
                'parent_key' => [
                    'asc'  => ['parent_key' => SORT_ASC],
                    'desc' => ['parent_key' => SORT_DESC]
                ]
            ]);
        $dataProvider->setSort($sort);

        $this->load($params);
        $company = ArrayHelper::getValue($params, 'company', 0);
        $this->is_partner = ($company == 0) ? 1 : 0;

        $query->alias('company');
        $query->joinWith('client client')->addSelect([
            'company.*',
            new yii\db\Expression('IF(IFNULL(client.parent_id,0)=0, client.id*1000, client.parent_id*1000+client.id) as parent_key')
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }


        // grid filtering conditions
        if($this->type_id) {
            $query->andFilterWhere([
                'company.id' => $this->id,
                'company.client_id' => $this->client_id,
                'company.type_id' => $this->type_id,
                'company.is_partner' => $this->is_partner,
                'company.created_at' => $this->created_at,
                'company.updated_at' => $this->updated_at,
            ]);
        } else {
            $query->andFilterWhere([
                'company.id' => $this->id,
                'company.client_id' => $this->client_id,
                'company.is_partner' => $this->is_partner,
                'company.created_at' => $this->created_at,
                'company.updated_at' => $this->updated_at,
            ]);
        }

        $query->andFilterWhere(['between', "act_date", $this->dateFrom, $this->dateTo]);

        $query->andFilterWhere(['company.act_status' => $this->act_status]);

        $query->innerJoin('{{%act}}');

        if ($this->is_partner == self::PARTNER) {
            $query->andWhere('company.client_id = act.partner_id AND (act.expense > 0) AND (act.service_type=' . $this->type_id . ') AND (act.served_at BETWEEN \'' . strtotime($this->dateFrom) . '\' AND \'' . strtotime($this->dateTo) . '\')');
        } else {
            if ($this->type_id) {
                $query->andWhere('company.client_id = act.client_id AND (act.income > 0) AND (act.service_type=' . $this->type_id . ') AND (act.served_at BETWEEN \'' . strtotime($this->dateFrom) . '\' AND \'' . strtotime($this->dateTo) . '\')');
            } else {
                $query->andWhere('(company.client_id = act.client_id) AND (company.payment_status=0) AND (act.income > 0) AND (act.service_type=company.type_id) AND (date_format(FROM_UNIXTIME(`act`.`served_at`), "%Y-%m-00")= company.act_date)');
            }
        }
        //

        $query->andWhere('client.name LIKE "%' . $this->client_name . '%" ');
        $query->andWhere('client.address LIKE "%' . $this->client_city . '%" ');

        if(!$this->client_id) {
            if ($this->type_id) {
                $query->groupBy('company.client_id');
            } else {
                $query->groupBy('company.client_id');
                $query->orderBy('company.type_id');
            }
        } else {
            if (!$this->type_id) {
                $query->groupBy('company.id');
                $query->orderBy('company.type_id, company.act_date');
            }
        }

        return $dataProvider;
    }
}
