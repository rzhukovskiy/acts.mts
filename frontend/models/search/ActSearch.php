<?php

namespace frontend\models\search;

use frontend\widgets\datePeriod\DatePeriodWidget;
use frontend\models\Act;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\Expression;


/**
 * ActSearch represents the model behind the search form about `common\models\Act`.
 */
class ActSearch extends Act
{
    public $dateMonth; // display year and month on /statistic/view

    public $dateFrom;
    public $dateTo;
    public $createDay;
    public $period;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['dateFrom', 'dateTo', 'service_type', 'client_id'], 'safe'],
        ];

        return array_merge(parent::rules(), $rules);
    }

    public function scenarios()
    {
        $scenarios = [
            'statistic_partner_filter' => ['dateFrom', 'dateTo', 'service_type'],
            'statistic_client_filter' => ['dateFrom', 'dateTo', 'service_type'],
            'statistic_filter' => ['dateFrom', 'dateTo', 'service_type','client_id'],
        ];

        return array_merge(parent::scenarios(), $scenarios);
    }

    /**
     * Поиск по типу услуги
     *
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchByType($params)
    {
        $query = static::find();

        $query->addSelect([
            'served_at',
            'COUNT(' . Act::tableName() . '.id) AS countServe',
            'service_type',
            'SUM(expense) as expense',
            'SUM(profit) as profit',
            'SUM(income) as income',
            'partner_id',
            'client_id',
        ])
            ->orderBy('profit DESC')
            ->with(['partner', 'client']);

        return $this->createProvider($params, $query);
    }

    /**
     * Поиск по для выбранного типа по месяцам
     *
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchTypeByMonth($params)
    {
        $query = static::find();

        $query->addSelect([
            "DATE(FROM_UNIXTIME(served_at)) as dateMonth",
            'COUNT(' . Act::tableName() . '.id) AS countServe',
            'service_type',
            'SUM(expense) as expense',
            'SUM(income) as income',
            'SUM(profit) as profit',
            'partner_id',
            'client_id',
        ])
            ->groupBy(['DATE_FORMAT(dateMonth, "%Y-%m")'])
            ->orderBy('dateMonth ASC');

        return $this->createProvider($params, $query);
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchByDays($params)
    {
        $query = static::find();

        $query->addSelect([
            "DATE(FROM_UNIXTIME(served_at)) as dateMonth",
            'COUNT({{%act}}.id) AS countServe',
            'service_type',
            'SUM(expense) as expense',
            'SUM(income) as income',
            'SUM(profit) as profit',
        ])
            ->groupBy(['DATE_FORMAT(FROM_UNIXTIME(served_at), "%Y-%m-%d")'])
            ->orderBy('dateMonth ASC');

        return $this->createProvider($params, $query);
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchDayCars($params)
    {
        $query = static::find();

        $query->addSelect([
            "DATE(FROM_UNIXTIME(served_at)) as dateMonth",
            'act.id',
            'check',
            'expense',
            'income',
            'profit',
            'type_id',
            'mark_id',
            'card_id',
            'service_type',
            'number'
        ])
            ->with(['type', 'mark', 'card'])
            ->orderBy('dateMonth ASC')
            ->alias('act');

        return $this->createProvider($params, $query);
    }

    /**
     * Поиск для общей статистики
     *
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchTotal($params)
    {
        $query = static::find();

        $query->addSelect([
            'COUNT(' . Act::tableName() . '.id) AS countServe',
            'SUM(expense) as expense',
            'SUM(profit) as profit',
            'SUM(income) as income',
            'service_type',
        ])
            ->with(['type'])
            ->groupBy('service_type')
            ->orderBy('profit DESC');

        return $this->createProvider($params, $query);
    }

    /**
     * Общий провайдер для статистики
     *
     * @param $params
     * @param $query
     * @return ActiveDataProvider
     */
    private function createProvider($params, ActiveQuery $query)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);

        if (!$this->validate())
            return $dataProvider;

        $query->andFilterWhere(['between', "DATE(FROM_UNIXTIME(`served_at`))", $this->dateFrom, $this->dateTo]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = static::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'partner_id' => $this->partner_id,
            'client_id' => $this->client_id,
            'type_id' => $this->type_id,
            'card_id' => $this->card_id,
            'mark_id' => $this->mark_id,
            'expense' => $this->expense,
            'income' => $this->income,
            'profit' => $this->profit,
            'service_type' => $this->service_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'served_at' => $this->served_at,
        ]);

        $query->andFilterWhere(['like', 'number', $this->number])
            ->andFilterWhere(['like', 'extra_number', $this->extra_number])
            ->andFilterWhere(['like', 'check', $this->check]);

        return $dataProvider;
    }

    /**
     * Поиск по типу услуги
     *
     * @param $params
     * @return ActiveDataProvider
     */
    public function searchStatistic($params)
    {
        $query = static::find();

        $query->addSelect([
            'SUM(expense) as expense',
            'SUM(profit) as profit',
            'SUM(income) as income',
        ]);
        if (in_array($this->period, [DatePeriodWidget::PERIOD_ALL, DatePeriodWidget::PERIOD_YEAR])) {
            $query->addSelect(new Expression('date_format(FROM_UNIXTIME(served_at), "%Y-%m-00") as date'))
                ->groupBy('date');
        } else {
            $query->addSelect(new Expression('date_format(FROM_UNIXTIME(served_at), "%Y-%m-%d") as date'))
                ->groupBy('date');
        }
        //Чтобы получить client_id надо присвоить
        $this->load($params);
        if ($this->client_id) {
            $query->andWhere(['or', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]])
                ->joinWith('client client');
        }
        //Ограничение на текущий месяц
        $query->andWhere([
            "<",
            "date_format(FROM_UNIXTIME(served_at), '%Y%m')",
            date('Ym', time())
        ]);

        return $this->createProvider($params, $query);
    }


}
