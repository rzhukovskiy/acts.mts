<?php

namespace common\models\search;

use common\models\CarHistory;
use yii;
use yii\data\ActiveDataProvider;

class CarHistorySearch extends CarHistory
{
    public $dateFrom;
    public $dateTo;
    public $period;
    public $type;
    public $from;
    public $car_number;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['dateFrom', 'dateTo', 'period', 'car_number', 'from'], 'safe'],
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
        $query = CarHistory::find()->with('fromCompany')->with('toCompany');

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

        $query->where(['between', "DATE(FROM_UNIXTIME(`date`))", $this->dateFrom, $this->dateTo]);

        if(isset($this->type)) {
            if(($this->type >= 0) && (mb_strlen($this->type) > 0)) {
                $query->andWhere(['type' => $this->type]);
            }
        }

        if($this->from) {
            $query->innerJoin('company', '`company`.`id` = `car_history`.`from`');
            $query->andFilterWhere(['like', '`company`.`name`', $this->from]);
        }

        $query->andFilterWhere(['like', 'car_number', $this->car_number]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'date'    => SORT_ASC,
            ]
        ];

        return $dataProvider;
    }

}
