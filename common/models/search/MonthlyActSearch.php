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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'type_id'], 'integer'],
            [['act_date'], 'string'],
            ['act_date', 'default', 'value' => date('n-Y', strtotime('-1 month'))],
            [['dateFrom', 'dateTo', 'dateMonth', 'payment_status'], 'safe'],
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
            'company.payment_status'                   => $this->payment_status,
            'company.created_at'                       => $this->created_at,
            'company.updated_at'                       => $this->updated_at,
        ]);

        // фильтр не подписанные
        if($this->act_status == 15) {
            $query->andFilterWhere(['!=', 'company.act_status', 5]);
            $query->andFilterWhere(['!=', 'company.act_status', 4]);
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
            'act_id',
            'is_partner',
            'number',
            'client.name'
        ])->joinWith('client client');


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
                    'asc'  => ['is_nested' => SORT_DESC, 'parent_key' => SORT_ASC],
                    'desc' => ['is_nested' => SORT_ASC, 'parent_key' => SORT_DESC]
                ]
            ]);
        $dataProvider->setSort($sort);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->alias('company');
        $query->joinWith('client client')->addSelect([
            new yii\db\Expression('IF(IFNULL(client.parent_id,0)=0, client.id*1000, client.parent_id*1000+client.id) as parent_key')
        ]);

        if (!$this->client_id) {
            $query->groupBy(['client_id']);
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'company.client_id'  => $this->client_id,
            'company.created_at' => $this->created_at,
            'company.updated_at' => $this->updated_at,
        ]);
        if ($this->type_id == Company::TYPE_OWNER) {
            $query->andFilterWhere(['is_partner' => MonthlyAct::NOT_PARTNER]);
        } else {
            $query->andFilterWhere(['is_partner' => MonthlyAct::PARTNER, 'type_id' => $this->type_id,]);
        }

        $query->andFilterWhere(['between', "act_date", $this->dateFrom, $this->dateTo]);
        $query->orderBy(['client.name' => SORT_ASC, 'type_id' => SORT_ASC, 'act_date' => SORT_DESC]);

        return $dataProvider;
    }
}
