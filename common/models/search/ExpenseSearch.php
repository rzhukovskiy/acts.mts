<?php
namespace common\models\search;

use common\models\Expense;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form about `common\models\User`.
 */
class ExpenseSearch extends Expense
{
    public $dateFrom;
    public $dateTo;
    public $period;
    public $name;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['expense_company', 'type'], 'integer'],
            [['sum'], 'number'],
            [['description', 'name'], 'string'],
            [['date', 'dateFrom', 'dateTo', 'period'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return [
            self::SCENARIO_ADD => ['expense_company', 'type', 'sum', 'description', 'date'],
            self::SCENARIO_STAT => ['expense_company', 'type', 'sum', 'description', 'date', 'dateFrom', 'dateTo', 'period', 'name'],
            self::SCENARIO_TOTAL => ['expense_company', 'type', 'sum', 'description', 'date', 'dateFrom', 'dateTo', 'period'],
            'default' => [],
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
        $query = Expense::find();

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
            case self::SCENARIO_ADD:


                break;
            case self::SCENARIO_STAT:

                $query->andWhere(['expense.type' => $this->type]);
                $query->joinWith(['expensecompany']);
                $query->andFilterWhere(['expense_company' => $this->expense_company]);
                // Если период не задан то задаем текущий месяц.
                if ((!isset($this->dateFrom)) && (!isset($this->dateTo))) {
                    $this->dateFrom = date('Y-m', strtotime("-1 month")) . '-31T21:00:00.000Z';
                    $this->dateTo = date('Y-m', time()) . '-31T21:00:00.000Z';
                    $query->andWhere(['between', "DATE(FROM_UNIXTIME(date))", $this->dateFrom, $this->dateTo]);
                } else {
                    $query->andWhere(['between', "DATE(FROM_UNIXTIME(date))", $this->dateFrom, $this->dateTo]);
                }
                break;

            case self::SCENARIO_TOTAL:
                $query->groupBy('type');
                $query->addSelect(['type', 'SUM(sum) as sum']);
                // Если период не задан то задаем текущий месяц.
                if ((!isset($this->dateFrom)) && (!isset($this->dateTo))) {
                    $this->dateFrom = date('Y-m', strtotime("-1 month")) . '-31T21:00:00.000Z';
                    $this->dateTo = date('Y-m', time()) . '-31T21:00:00.000Z';
                    $query->andWhere(['between', "DATE(FROM_UNIXTIME(date))", $this->dateFrom, $this->dateTo]);
                } else {
                    $query->andWhere(['between', "DATE(FROM_UNIXTIME(date))", $this->dateFrom, $this->dateTo]);
                }
                break;
            default:

        }
        return $dataProvider;
    }
}