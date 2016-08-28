<?php

namespace frontend\models\search;

use common\models\Act;
use Yii;
use yii\data\ActiveDataProvider;
use common\models\Car;
use common\models\search\CarSearch as BaseCarSearch;
use yii\data\ArrayDataProvider;

/**
 * CarSearch represents the model behind the search form about `common\models\Car`.
 */
class CarSearch extends BaseCarSearch
{

    // Date range
    public $dateFrom;
    public $dateTo;

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['company_id', 'number'],
        ];
    }


    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchDirty($params)
    {
        $query = Car::find();

//    SELECT t.id, t.company_id, c.name, t.number, c.name, a.id
//    FROM car t
//
//    LEFT JOIN company c
//    ON t.company_id = c.id
//
//    LEFT OUTER JOIN act a
//    ON a.number = t.number
//
//    WHERE
//    a.id IS NULL
//    AND  c.is_infected='1'
//
//    GROUP BY t.id
//    ORDER BY t.company_id, t.number DESC;
        $query
            ->addSelect(['company_id', 'mark_id', 'type_id', 'number'])
            ->join('LEFT OUTER JOIN', Act::tableName(), Act::tableName() . '.number = ' . static::tableName() . '.number')
            ->andWhere([Act::tableName() . 'id' => null])
            ->groupBy(static::tableName() . '.id')
            ->orderBy([static::tableName() . '.company_id', static::tableName() . '.number']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $this->load($params);

        if (!$this->validate())
            return $dataProvider;

        return $dataProvider;
    }
}
